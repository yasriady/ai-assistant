<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Concerns\BuildsAssessmentPrompts;
use App\Services\Rps\Concerns\BuildsRpsPrompts;
use App\Services\AI\Contracts\AIProvider;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIProvider implements AIProvider
{
    use BuildsAssessmentPrompts;
    use BuildsRpsPrompts;

    public function __construct(
        protected array $config = [],
        protected float $temperature = 0.2,
        protected int $maxTokens = 4096,
    ) {
        $this->config = $config ?: config('ai.providers.openai', []);
        $this->temperature = $temperature ?: (float) config('ai.temperature', 0.2);
        $this->maxTokens = $maxTokens ?: (int) config('ai.max_tokens', 4096);
    }

    public function assessDocument(array $payload): array
    {
        return $this->chatAssessment(
            $this->antiHallucinationSystemPrompt(),
            $this->documentAssessmentUserPrompt($payload),
        );
    }

    public function assessAnswer(array $payload): array
    {
        return $this->chatAssessment(
            $this->antiHallucinationSystemPrompt(),
            $this->answerAssessmentUserPrompt($payload),
        );
    }

    public function analyzeQuestion(array $payload): array
    {
        $result = $this->chatJson(
            $this->antiHallucinationSystemPrompt(),
            $this->analyzeQuestionUserPrompt($payload),
        );

        return array_merge($result, $this->usageMeta($result));
    }

    public function generateFeedback(array $payload): array
    {
        $result = $this->chatJson(
            $this->antiHallucinationSystemPrompt(),
            $this->feedbackUserPrompt($payload),
        );

        return array_merge($result, $this->usageMeta($result));
    }

    public function generateRpsDraft(array $payload): array
    {
        $result = $this->chatJson(
            $this->rpsSystemPrompt(),
            $this->rpsUserPrompt($payload),
        );

        return array_merge($result, $this->usageMeta($result));
    }

    /**
     * @return array<string, mixed>
     */
    protected function chatAssessment(string $system, string $user): array
    {
        $result = $this->chatJson($system, $user);

        return $this->normalizeAssessmentResult($result, $this->usageMeta($result));
    }

    /**
     * @return array<string, mixed>
     */
    protected function chatJson(string $system, string $user): array
    {
        $apiKey = $this->config['api_key'] ?? null;
        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $baseUrl = rtrim((string) ($this->config['base_url'] ?? 'https://api.openai.com/v1'), '/');
        $model = (string) ($this->config['model'] ?? config('ai.model', 'gpt-4o-mini'));
        $timeout = (int) ($this->config['timeout'] ?? 120);

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->acceptJson()
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ])
                ->throw()
                ->json();
        } catch (RequestException $e) {
            throw new RuntimeException('OpenAI request failed: '.$e->getMessage(), previous: $e);
        }

        $content = data_get($response, 'choices.0.message.content', '');
        if (! is_string($content) || $content === '') {
            throw new RuntimeException('OpenAI returned an empty response.');
        }

        $decoded = $this->decodeJsonResponse($content);
        $decoded['_provider'] = 'openai';
        $decoded['_model'] = $model;
        $decoded['_raw'] = $response;
        $decoded['_tokens_input'] = (int) data_get($response, 'usage.prompt_tokens', 0);
        $decoded['_tokens_output'] = (int) data_get($response, 'usage.completion_tokens', 0);

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function usageMeta(array $result): array
    {
        return [
            '_provider' => $result['_provider'] ?? 'openai',
            '_model' => $result['_model'] ?? ($this->config['model'] ?? null),
            '_raw' => $result['_raw'] ?? null,
            '_tokens_input' => (int) ($result['_tokens_input'] ?? 0),
            '_tokens_output' => (int) ($result['_tokens_output'] ?? 0),
        ];
    }
}
