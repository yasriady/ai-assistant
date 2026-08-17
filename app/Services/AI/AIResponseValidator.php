<?php

namespace App\Services\AI;

use InvalidArgumentException;

class AIResponseValidator
{
    /**
     * Validate a structured assessment JSON payload.
     *
     * @param  array<string, mixed>  $data
     * @return array{
     *     score: float,
     *     max_score: float,
     *     criteria: list<array<string, mixed>>,
     *     overall_feedback: string,
     *     confidence: float
     * }
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $data): array
    {
        foreach (['score', 'max_score', 'criteria', 'overall_feedback', 'confidence'] as $key) {
            if (! array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Missing required assessment field: {$key}");
            }
        }

        if (! is_numeric($data['score'])) {
            throw new InvalidArgumentException('Field score must be numeric.');
        }

        if (! is_numeric($data['max_score'])) {
            throw new InvalidArgumentException('Field max_score must be numeric.');
        }

        if ((float) $data['max_score'] <= 0) {
            throw new InvalidArgumentException('Field max_score must be greater than zero.');
        }

        if ((float) $data['score'] < 0) {
            throw new InvalidArgumentException('Field score must be >= 0.');
        }

        if ((float) $data['score'] > (float) $data['max_score'] + 0.001) {
            throw new InvalidArgumentException('Field score cannot exceed max_score.');
        }

        if (! is_array($data['criteria'])) {
            throw new InvalidArgumentException('Field criteria must be an array.');
        }

        if ($data['criteria'] === []) {
            throw new InvalidArgumentException('Field criteria must not be empty.');
        }

        if (! is_string($data['overall_feedback']) || trim($data['overall_feedback']) === '') {
            throw new InvalidArgumentException('Field overall_feedback must be a non-empty string.');
        }

        if (! is_numeric($data['confidence'])) {
            throw new InvalidArgumentException('Field confidence must be numeric.');
        }

        $confidence = (float) $data['confidence'];
        if ($confidence < 0 || $confidence > 1) {
            throw new InvalidArgumentException('Field confidence must be between 0 and 1.');
        }

        $criteria = [];
        foreach (array_values($data['criteria']) as $index => $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException("Criteria[{$index}] must be an object.");
            }

            foreach (['name', 'score', 'max_score'] as $required) {
                if (! array_key_exists($required, $item)) {
                    throw new InvalidArgumentException("Criteria[{$index}] missing field: {$required}");
                }
            }

            if (! is_string($item['name']) || trim($item['name']) === '') {
                throw new InvalidArgumentException("Criteria[{$index}].name must be a non-empty string.");
            }

            if (! is_numeric($item['score']) || ! is_numeric($item['max_score'])) {
                throw new InvalidArgumentException("Criteria[{$index}] score/max_score must be numeric.");
            }

            if ((float) $item['max_score'] < 0 || (float) $item['score'] < 0) {
                throw new InvalidArgumentException("Criteria[{$index}] scores must be >= 0.");
            }

            if ((float) $item['score'] > (float) $item['max_score'] + 0.001) {
                throw new InvalidArgumentException("Criteria[{$index}] score cannot exceed max_score.");
            }

            $criteria[] = [
                'name' => trim($item['name']),
                'score' => round((float) $item['score'], 2),
                'max_score' => round((float) $item['max_score'], 2),
                'evidence' => isset($item['evidence']) ? (string) $item['evidence'] : '',
                'reasoning' => isset($item['reasoning']) ? (string) $item['reasoning'] : '',
                'feedback' => isset($item['feedback']) ? (string) $item['feedback'] : '',
                'insufficient_evidence' => (bool) ($item['insufficient_evidence'] ?? false),
            ];
        }

        return [
            'score' => round((float) $data['score'], 2),
            'max_score' => round((float) $data['max_score'], 2),
            'criteria' => $criteria,
            'overall_feedback' => trim((string) $data['overall_feedback']),
            'confidence' => round($confidence, 4),
        ];
    }

    /**
     * Hints to include when asking the model to repair an invalid response.
     *
     * @return list<string>
     */
    public function repairHints(): array
    {
        return [
            'Return a single valid JSON object only (no markdown).',
            'Required top-level keys: score, max_score, criteria, overall_feedback, confidence.',
            'criteria must be a non-empty array of objects with name, score, max_score.',
            'Optional criterion fields: evidence, reasoning, feedback, insufficient_evidence.',
            'score must be >= 0 and <= max_score; max_score must be > 0.',
            'confidence must be a number between 0 and 1 inclusive.',
            'overall_feedback must be a non-empty string.',
            'Cite evidence from the student text; do not invent quotes.',
        ];
    }
}
