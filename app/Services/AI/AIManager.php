<?php

namespace App\Services\AI;

use App\Models\AiUsage;
use App\Services\Rps\RpsDraftValidator;
use App\Services\AI\Contracts\AIProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\Providers\OpenAIProvider;
use InvalidArgumentException;

class AIManager
{
    /** @var array<string, AIProvider> */
    protected array $drivers = [];

    public function __construct(
        protected AIResponseValidator $validator,
        protected RpsDraftValidator $rpsDraftValidator,
    ) {}

    public function driver(?string $name = null): AIProvider
    {
        $name = $name ?: (string) config('ai.provider', 'null');
        if ($name === '') {
            $name = 'null';
        }

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->resolve($name);
        }

        return $this->drivers[$name];
    }

    public function validator(): AIResponseValidator
    {
        return $this->validator;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context  Optional usage tracking context (user_id, assessment_id, submission_id)
     * @return array<string, mixed>
     */
    public function assessDocument(array $payload, array $context = []): array
    {
        return $this->execute('assessDocument', $payload, $context, validate: true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function assessAnswer(array $payload, array $context = []): array
    {
        return $this->execute('assessAnswer', $payload, $context, validate: true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function analyzeQuestion(array $payload, array $context = []): array
    {
        return $this->execute('analyzeQuestion', $payload, $context, validate: false);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generateFeedback(array $payload, array $context = []): array
    {
        return $this->execute('generateFeedback', $payload, $context, validate: false);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generateRpsDraft(array $payload, array $context = []): array
    {
        $providerName = (string) ($payload['provider'] ?? config('ai.provider', 'null'));
        $provider = $this->driver($providerName);

        /** @var array<string, mixed> $result */
        $result = $provider->generateRpsDraft($payload);

        $validated = $this->rpsDraftValidator->validate($result);

        $this->trackUsage($result, array_merge($context, ['method' => 'generateRpsDraft']));

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function extractStudentIdentity(array $payload, array $context = []): array
    {
        return $this->execute('extractStudentIdentity', $payload, $context, validate: false);
    }

    /**
     * Estimated spend for the current calendar month.
     */
    public function monthlySpend(?int $userId = null): float
    {
        $query = AiUsage::query()
            ->where('created_at', '>=', now()->startOfMonth());

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return (float) $query->sum('estimated_cost');
    }

    public function withinBudget(?int $userId = null): bool
    {
        $budget = (float) config('ai.budget_monthly', 50);

        return $this->monthlySpend($userId) < $budget;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function execute(string $method, array $payload, array $context, bool $validate): array
    {
        $providerName = (string) ($payload['provider'] ?? config('ai.provider', 'null'));
        $provider = $this->driver($providerName);

        /** @var array<string, mixed> $result */
        $result = $provider->{$method}($payload);

        if ($validate) {
            $validated = $this->validator->validate($result);
            $result = array_merge($result, $validated);
        }

        $this->trackUsage($result, $context);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $context
     */
    protected function trackUsage(array $result, array $context): void
    {
        $provider = (string) ($result['_provider'] ?? config('ai.provider', 'null'));
        $model = (string) ($result['_model'] ?? config("ai.providers.{$provider}.model") ?? config('ai.model') ?? 'unknown');
        $tokensIn = (int) ($result['_tokens_input'] ?? 0);
        $tokensOut = (int) ($result['_tokens_output'] ?? 0);

        AiUsage::query()->create([
            'user_id' => $context['user_id'] ?? null,
            'provider' => $provider,
            'model' => $model,
            'tokens_input' => $tokensIn,
            'tokens_output' => $tokensOut,
            'requests' => 1,
            'estimated_cost' => $this->estimateCost($provider, $tokensIn, $tokensOut),
            'assessment_id' => $context['assessment_id'] ?? null,
            'submission_id' => $context['submission_id'] ?? null,
            'meta' => [
                'method' => $context['method'] ?? null,
                'mock' => $provider === 'null',
            ],
        ]);
    }

    protected function estimateCost(string $provider, int $tokensIn, int $tokensOut): float
    {
        // Rough USD estimates per 1K tokens; null/ollama are treated as free.
        $rates = match ($provider) {
            'openai' => ['in' => 0.00015, 'out' => 0.0006],
            'gemini' => ['in' => 0.0001, 'out' => 0.0004],
            default => ['in' => 0.0, 'out' => 0.0],
        };

        return round((($tokensIn / 1000) * $rates['in']) + (($tokensOut / 1000) * $rates['out']), 6);
    }

    protected function resolve(string $name): AIProvider
    {
        $temperature = (float) config('ai.temperature', 0.2);
        $maxTokens = (int) config('ai.max_tokens', 4096);

        return match ($name) {
            'openai' => new OpenAIProvider(config('ai.providers.openai', []), $temperature, $maxTokens),
            'gemini' => new GeminiProvider(config('ai.providers.gemini', []), $temperature, $maxTokens),
            'ollama' => new OllamaProvider(config('ai.providers.ollama', []), $temperature, $maxTokens),
            'null' => new NullAIProvider,
            default => throw new InvalidArgumentException("Unsupported AI provider [{$name}]."),
        };
    }
}
