<?php

namespace App\Services\AI;

use App\Services\AI\Concerns\BuildsAssessmentPrompts;
use App\Services\Rps\Concerns\BuildsRpsPrompts;
use App\Services\AI\Contracts\AIProvider;

/**
 * Deterministic mock provider for tests and demos when no API key is available.
 */
class NullAIProvider implements AIProvider
{
    use BuildsAssessmentPrompts;
    use BuildsRpsPrompts;

    public function assessDocument(array $payload): array
    {
        $text = (string) ($payload['document_text'] ?? $payload['text'] ?? '');
        $maxScore = (float) ($payload['max_score'] ?? 100);
        $rubric = $payload['rubric'] ?? [];
        $criteriaInput = is_array($rubric['criteria'] ?? null) ? $rubric['criteria'] : (is_array($rubric) && array_is_list($rubric) ? $rubric : []);

        if ($criteriaInput === []) {
            $criteriaInput = [
                ['name' => 'Content', 'max_score' => $maxScore * 0.4, 'weight' => 40],
                ['name' => 'Structure', 'max_score' => $maxScore * 0.3, 'weight' => 30],
                ['name' => 'Evidence', 'max_score' => $maxScore * 0.3, 'weight' => 30],
            ];
        }

        $criteria = [];
        $totalScore = 0.0;
        $totalMax = 0.0;

        foreach (array_values($criteriaInput) as $index => $criterion) {
            $name = (string) ($criterion['name'] ?? "Criterion ".($index + 1));
            $criterionMax = (float) ($criterion['max_score'] ?? ($maxScore / max(count($criteriaInput), 1)));
            $keywords = $this->criterionKeywords($name, $criterion);
            $ratio = $this->scoreRatio($text, $keywords);
            $score = round($criterionMax * $ratio, 2);
            $snippet = $this->evidenceSnippet($text, $keywords);

            $criteria[] = [
                'name' => $name,
                'score' => $score,
                'max_score' => round($criterionMax, 2),
                'evidence' => $snippet !== '' ? $snippet : '[insufficient evidence in extracted text]',
                'reasoning' => sprintf(
                    'Deterministic mock score based on text length (%d chars) and keyword coverage for "%s".',
                    mb_strlen($text),
                    $name
                ),
                'feedback' => $ratio >= 0.7
                    ? "Strong coverage for {$name}. Expand examples where possible."
                    : "Strengthen {$name} with clearer explanations and supporting detail.",
                'insufficient_evidence' => $snippet === '',
            ];

            $totalScore += $score;
            $totalMax += $criterionMax;
        }

        if ($totalMax <= 0) {
            $totalMax = $maxScore;
        }

        // Scale to requested max_score while preserving relative criterion totals.
        $scaledScore = $totalMax > 0 ? ($totalScore / $totalMax) * $maxScore : 0.0;
        $confidence = min(0.95, 0.45 + (mb_strlen(trim($text)) / 4000) * 0.4);

        return $this->normalizeAssessmentResult([
            'score' => round($scaledScore, 2),
            'max_score' => round($maxScore, 2),
            'criteria' => $criteria,
            'overall_feedback' => 'Demo assessment (NullAIProvider): scores are heuristic and for demonstration only. Configure a real AI provider for production grading.',
            'confidence' => round($confidence, 4),
        ], [
            '_provider' => 'null',
            '_model' => config('ai.providers.null.model', 'null-mock'),
            '_raw' => ['mock' => true],
            '_tokens_input' => max(1, (int) ceil(mb_strlen($text) / 4)),
            '_tokens_output' => 128,
        ]);
    }

    public function assessAnswer(array $payload): array
    {
        $answer = (string) ($payload['answer_text'] ?? '');
        $expected = (string) ($payload['expected_answer'] ?? '');
        $maxScore = (float) ($payload['max_score'] ?? 10);
        $keyConcepts = $payload['key_concepts'] ?? [];
        $keywords = is_array($keyConcepts) ? array_map('strval', $keyConcepts) : [];

        if ($expected !== '') {
            foreach (preg_split('/[\s,;]+/', mb_strtolower($expected)) ?: [] as $token) {
                if (mb_strlen($token) >= 4) {
                    $keywords[] = $token;
                }
            }
        }

        $ratio = $this->scoreRatio($answer, array_unique($keywords));
        if ($expected !== '' && $answer !== '') {
            similar_text(mb_strtolower($answer), mb_strtolower($expected), $percent);
            $ratio = max($ratio, min(1.0, $percent / 100));
        }

        $score = round($maxScore * $ratio, 2);
        $snippet = $this->evidenceSnippet($answer, $keywords);

        return $this->normalizeAssessmentResult([
            'score' => $score,
            'max_score' => round($maxScore, 2),
            'criteria' => [[
                'name' => 'Answer Quality',
                'score' => $score,
                'max_score' => round($maxScore, 2),
                'evidence' => $snippet !== '' ? $snippet : '[no matching keywords found in answer]',
                'reasoning' => 'Deterministic mock comparison against expected answer / key concepts.',
                'feedback' => $ratio >= 0.7
                    ? 'Answer covers key points. Add precision where needed.'
                    : 'Answer misses several expected concepts. Review the marking guide.',
                'insufficient_evidence' => $snippet === '',
            ]],
            'overall_feedback' => 'Demo answer grade (NullAIProvider).',
            'confidence' => round(min(0.9, 0.4 + $ratio * 0.5), 4),
        ], [
            '_provider' => 'null',
            '_model' => config('ai.providers.null.model', 'null-mock'),
            '_raw' => ['mock' => true],
            '_tokens_input' => max(1, (int) ceil(mb_strlen($answer.$expected) / 4)),
            '_tokens_output' => 64,
        ]);
    }

    public function analyzeQuestion(array $payload): array
    {
        $text = (string) ($payload['question_text'] ?? '');
        $length = mb_strlen($text);
        $difficulty = $length > 400 ? 'hard' : ($length > 150 ? 'medium' : 'easy');

        return [
            'difficulty' => $difficulty,
            'cognitive_level' => $length > 300 ? 'analyze' : 'understand',
            'key_concepts' => $this->extractTokens($text, 6),
            'expected_answer_outline' => 'Outline main points implied by the question stem (mock).',
            'common_mistakes' => ['Incomplete explanation', 'Missing units or definitions'],
            'confidence' => 0.55,
            '_provider' => 'null',
            '_model' => config('ai.providers.null.model', 'null-mock'),
            '_raw' => ['mock' => true],
            '_tokens_input' => max(1, (int) ceil($length / 4)),
            '_tokens_output' => 48,
        ];
    }

    public function generateFeedback(array $payload): array
    {
        $score = (float) data_get($payload, 'assessment_result.score', data_get($payload, 'score', 0));
        $max = (float) data_get($payload, 'assessment_result.max_score', data_get($payload, 'max_score', 100));
        $ratio = $max > 0 ? $score / $max : 0;

        return [
            'feedback' => $ratio >= 0.7
                ? 'Good work overall. Review weaker criteria and refine supporting evidence.'
                : 'Focus on strengthening core arguments and aligning responses with the rubric.',
            'strengths' => $ratio >= 0.5 ? ['Addresses the main task'] : ['Attempt submitted'],
            'improvements' => ['Add clearer evidence', 'Tighten structure'],
            'confidence' => 0.6,
            '_provider' => 'null',
            '_model' => config('ai.providers.null.model', 'null-mock'),
            '_raw' => ['mock' => true],
            '_tokens_input' => 32,
            '_tokens_output' => 48,
        ];
    }

    public function generateRpsDraft(array $payload): array
    {
        $courseName = (string) data_get($payload, 'course.name', 'Mata Kuliah');
        $totalWeeks = max(1, (int) ($payload['total_weeks'] ?? 16));
        $midtermWeek = max(1, min($totalWeeks, (int) ($payload['midterm_week'] ?? 8)));
        $cpls = is_array($payload['cpls'] ?? null) ? $payload['cpls'] : [];
        $cplCodes = array_values(array_filter(array_map(
            fn ($c) => is_array($c) ? (string) ($c['code'] ?? '') : '',
            $cpls,
        )));

        if ($cplCodes === []) {
            $cplCodes = ['P01', 'KU02'];
        }

        $cpmks = [
            [
                'code' => 'CPMK-1',
                'description' => "Mampu menjelaskan konsep dasar {$courseName} sesuai capaian program.",
                'cpl_codes' => array_slice($cplCodes, 0, min(2, count($cplCodes))),
            ],
            [
                'code' => 'CPMK-2',
                'description' => "Mampu menganalisis permasalahan pada {$courseName} menggunakan pendekatan sistematis.",
                'cpl_codes' => array_slice($cplCodes, 0, min(3, count($cplCodes))),
            ],
            [
                'code' => 'CPMK-3',
                'description' => "Mampu mengevaluasi solusi {$courseName} pada studi kasus sederhana.",
                'cpl_codes' => array_slice($cplCodes, -min(2, count($cplCodes))),
            ],
        ];

        $topics = [];
        for ($week = 1; $week <= $totalWeeks; $week++) {
            if ($week === $midtermWeek) {
                $topics[] = [
                    'week_number' => $week,
                    'title' => 'UTS — Ujian Tengah Semester',
                    'description' => 'Evaluasi materi minggu 1 hingga '.($midtermWeek - 1).'.',
                    'cpmk_codes' => ['CPMK-1', 'CPMK-2'],
                ];

                continue;
            }

            if ($week === $totalWeeks) {
                $topics[] = [
                    'week_number' => $week,
                    'title' => 'UAS — Ujian Akhir Semester',
                    'description' => 'Evaluasi akhir seluruh materi semester.',
                    'cpmk_codes' => ['CPMK-2', 'CPMK-3'],
                ];

                continue;
            }

            $cpmkCode = $week <= (int) floor($midtermWeek * 0.6) ? 'CPMK-1' : ($week < $midtermWeek ? 'CPMK-2' : 'CPMK-3');
            $topics[] = [
                'week_number' => $week,
                'title' => "Materi {$courseName} — Minggu {$week}",
                'description' => 'Pembelajaran hybrid dengan studi kasus singkat dan diskusi.',
                'cpmk_codes' => [$cpmkCode],
            ];
        }

        return [
            'midterm_week' => $midtermWeek,
            'cpmks' => $cpmks,
            'topics' => $topics,
            '_provider' => 'null',
            '_model' => config('ai.providers.null.model', 'null-mock'),
            '_raw' => ['mock' => true],
            '_tokens_input' => 256,
            '_tokens_output' => 512,
        ];
    }

    /**
     * @param  array<string, mixed>  $criterion
     * @return list<string>
     */
    protected function criterionKeywords(string $name, array $criterion): array
    {
        $keywords = $this->extractTokens($name.' '.((string) ($criterion['description'] ?? '')), 8);

        return array_values(array_unique(array_filter($keywords)));
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function scoreRatio(string $text, array $keywords): float
    {
        $lengthFactor = min(1.0, mb_strlen(trim($text)) / 1200);
        if ($keywords === []) {
            return max(0.35, $lengthFactor * 0.85);
        }

        $haystack = mb_strtolower($text);
        $hits = 0;
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($haystack, mb_strtolower($keyword))) {
                $hits++;
            }
        }

        $keywordFactor = $hits / count($keywords);

        return max(0.2, min(0.98, ($lengthFactor * 0.45) + ($keywordFactor * 0.55)));
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function evidenceSnippet(string $text, array $keywords): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            return '';
        }

        $haystack = mb_strtolower($text);
        foreach ($keywords as $keyword) {
            $pos = $keyword !== '' ? mb_strpos($haystack, mb_strtolower($keyword)) : false;
            if ($pos !== false) {
                $start = max(0, $pos - 40);
                $snippet = mb_substr($text, $start, 120);

                return trim($snippet).(mb_strlen($text) > $start + 120 ? '…' : '');
            }
        }

        return mb_substr($text, 0, 120).(mb_strlen($text) > 120 ? '…' : '');
    }

    /**
     * @return list<string>
     */
    protected function extractTokens(string $text, int $limit): array
    {
        $parts = preg_split('/[^a-zA-Z0-9]+/', mb_strtolower($text)) ?: [];
        $parts = array_values(array_filter($parts, fn (string $t): bool => mb_strlen($t) >= 4));

        return array_values(array_slice(array_unique($parts), 0, $limit));
    }
}
