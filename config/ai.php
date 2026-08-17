<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "openai", "gemini", "ollama", "null"
    | Use "null" for deterministic mock responses (tests / demo without keys).
    |
    */

    'provider' => env('AI_PROVIDER', 'null'),

    'model' => env('AI_MODEL'),

    'temperature' => (float) env('AI_TEMPERATURE', 0.2),

    'max_tokens' => (int) env('AI_MAX_TOKENS', 4096),

    'budget_monthly' => (float) env('AI_BUDGET_MONTHLY', 50),

    'prompt_version' => env('AI_PROMPT_VERSION', '1.0'),

    /*
    |--------------------------------------------------------------------------
    | Assessment output language
    |--------------------------------------------------------------------------
    |
    | Language for AI-generated review text (feedback, reasoning, etc.).
    | Independent from the dashboard UI locale (session locale).
    |
    */

    'assessment_locale' => env('AI_ASSESSMENT_LOCALE', 'id'),

    'providers' => [

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', env('AI_MODEL', 'gpt-4o-mini')),
            'timeout' => (int) env('OPENAI_TIMEOUT', 120),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'model' => env('GEMINI_MODEL', env('AI_MODEL', 'gemini-3.1-flash-lite')),
            'timeout' => (int) env('GEMINI_TIMEOUT', 120),
        ],

        'ollama' => [
            'api_key' => env('OLLAMA_API_KEY'),
            'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
            'model' => env('OLLAMA_MODEL', env('AI_MODEL', 'llama3.2')),
            'timeout' => (int) env('OLLAMA_TIMEOUT', 180),
        ],

        'null' => [
            'model' => env('AI_NULL_MODEL', 'null-mock'),
        ],

    ],

];
