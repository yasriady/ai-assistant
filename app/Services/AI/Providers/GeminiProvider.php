<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Concerns\BuildsAssessmentPrompts;
use App\Services\Rps\Concerns\BuildsRpsPrompts;
use App\Services\AI\Contracts\AIProvider;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AIProvider
{
    use BuildsAssessmentPrompts;
    use BuildsRpsPrompts;

    public function __construct(
        protected array $config = [],
        protected float $temperature = 0.2,
        protected int $maxTokens = 4096,
    ) {
        $this->config = $config ?: config('ai.providers.gemini', []);
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
        $result = $this->generateJson(
            $this->antiHallucinationSystemPrompt(),
            $this->analyzeQuestionUserPrompt($payload),
        );

        return array_merge($result, $this->usageMeta($result));
    }

    public function generateFeedback(array $payload): array
    {
        $result = $this->generateJson(
            $this->antiHallucinationSystemPrompt(),
            $this->feedbackUserPrompt($payload),
        );

        return array_merge($result, $this->usageMeta($result));
    }

    public function generateRpsDraft(array $payload): array
    {
        $result = $this->generateJson(
            $this->rpsSystemPrompt(),
            $this->rpsUserPrompt($payload),
        );

        return array_merge($result, $this->usageMeta($result));
    }

    public function extractStudentIdentity(array $payload): array
    {
        $result = $this->generateJson(
            $this->antiHallucinationSystemPrompt(),
            $this->studentIdentityUserPrompt($payload),
        );

        return $this->normalizeStudentIdentityResult($result, $this->usageMeta($result));
    }

    /**
     * @return array<string, mixed>
     */
    protected function chatAssessment(string $system, string $user): array
    {
        $result = $this->generateJson($system, $user);

        return $this->normalizeAssessmentResult($result, $this->usageMeta($result));
    }

    /**
     * @return array<string, mixed>
     */
    protected function generateJson(string $system, string $user): array
    {
        $apiKey = $this->config['api_key'] ?? null;
        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $baseUrl = rtrim((string) ($this->config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = (string) ($this->config['model'] ?? config('ai.model', 'gemini-2.0-flash'));
        $timeout = (int) ($this->config['timeout'] ?? 120);

        $url = "{$baseUrl}/models/{$model}:generateContent";

        try {
            $response = Http::withQueryParameters(['key' => $apiKey])
                ->timeout($timeout)
                ->acceptJson()
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $system]],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $user]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => $this->temperature,
                        'maxOutputTokens' => $this->maxTokens,
                        'responseMimeType' => 'application/json',
                    ],
                ])
                ->throw()
                ->json();
        } catch (RequestException $e) {
            throw new RuntimeException('Gemini request failed: '.$e->getMessage(), previous: $e);
        }

        $parts = data_get($response, 'candidates.0.content.parts', []);
        $content = '';
        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (isset($part['text']) && is_string($part['text'])) {
                    $content .= $part['text'];
                }
            }
        }

        if ($content === '') {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        $decoded = $this->decodeJsonResponse($content);
        $decoded['_provider'] = 'gemini';
        $decoded['_model'] = $model;
        $decoded['_raw'] = $response;
        $decoded['_tokens_input'] = (int) data_get($response, 'usageMetadata.promptTokenCount', 0);
        $decoded['_tokens_output'] = (int) data_get($response, 'usageMetadata.candidatesTokenCount', 0);

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function usageMeta(array $result): array
    {
        return [
            '_provider' => $result['_provider'] ?? 'gemini',
            '_model' => $result['_model'] ?? ($this->config['model'] ?? null),
            '_raw' => $result['_raw'] ?? null,
            '_tokens_input' => (int) ($result['_tokens_input'] ?? 0),
            '_tokens_output' => (int) ($result['_tokens_output'] ?? 0),
        ];
    }
}
