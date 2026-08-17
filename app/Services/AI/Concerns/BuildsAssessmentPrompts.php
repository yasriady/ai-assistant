<?php

namespace App\Services\AI\Concerns;

trait BuildsAssessmentPrompts
{
    protected function antiHallucinationSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an academic assessment engine for higher education.

Rules (mandatory):
1. Grade ONLY from the provided student text, rubric, and answer key. Do not invent facts, quotes, or page numbers that are not present.
2. Every criterion score MUST cite evidence as a short verbatim quote or paraphrase grounded in the student text. If evidence is missing, set insufficient_evidence=true and score conservatively.
3. Never award full marks without supporting evidence in the submission.
4. Do not invent student identity, course content, or external knowledge beyond what is needed to interpret the rubric.
5. Respond with a single JSON object only. No markdown fences, no commentary.
6. Confidence must be between 0 and 1 reflecting evidence quality and clarity.
PROMPT
            .$this->assessmentOutputLanguageRule();
    }

    protected function assessmentOutputLanguageRule(): string
    {
        return match ((string) config('ai.assessment_locale', 'id')) {
            'id' => <<<'RULE'

7. Language: Write ALL assessor-facing text in Bahasa Indonesia — including reasoning, feedback, and overall_feedback. Keep criterion names exactly as provided in the rubric JSON. Evidence quotes may stay in the student's original language.
RULE,
            default => '',
        };
    }

    protected function assessmentOutputLanguageReminder(): string
    {
        return match ((string) config('ai.assessment_locale', 'id')) {
            'id' => "\n\nPenting: Tulis reasoning, feedback, dan overall_feedback dalam Bahasa Indonesia.",
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function documentAssessmentUserPrompt(array $payload): string
    {
        $rubricJson = json_encode($payload['rubric'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $maxScore = $payload['max_score'] ?? 100;
        $instructions = $payload['instructions'] ?? '';
        $documentText = $payload['document_text'] ?? $payload['text'] ?? '';

        return <<<PROMPT
Assess the student document against the rubric.

Max score: {$maxScore}
Assessment instructions:
{$instructions}

Rubric (JSON):
{$rubricJson}

Student document text:
\"\"\"
{$documentText}
\"\"\"

Return JSON with this schema:
{
  "score": <number>,
  "max_score": <number>,
  "criteria": [
    {
      "name": "<criterion name>",
      "score": <number>,
      "max_score": <number>,
      "evidence": "<quote or grounded paraphrase>",
      "reasoning": "<brief scoring rationale>",
      "feedback": "<actionable feedback>",
      "insufficient_evidence": <boolean>
    }
  ],
  "overall_feedback": "<string>",
  "confidence": <0-1 number>
}
PROMPT
            .$this->assessmentOutputLanguageReminder();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function answerAssessmentUserPrompt(array $payload): string
    {
        $rubricJson = json_encode($payload['rubric'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $maxScore = $payload['max_score'] ?? 10;
        $question = $payload['question_text'] ?? '';
        $expected = $payload['expected_answer'] ?? '';
        $keyConcepts = json_encode($payload['key_concepts'] ?? [], JSON_UNESCAPED_UNICODE);
        $answer = $payload['answer_text'] ?? '';

        return <<<PROMPT
Assess the student answer for this exam question.

Max score: {$maxScore}
Question:
{$question}

Expected answer / marking guide:
{$expected}

Key concepts: {$keyConcepts}

Rubric (JSON, optional):
{$rubricJson}

Student answer:
\"\"\"
{$answer}
\"\"\"

Return JSON with this schema:
{
  "score": <number>,
  "max_score": <number>,
  "criteria": [
    {
      "name": "<criterion or aspect name>",
      "score": <number>,
      "max_score": <number>,
      "evidence": "<quote from student answer>",
      "reasoning": "<brief scoring rationale>",
      "feedback": "<actionable feedback>",
      "insufficient_evidence": <boolean>
    }
  ],
  "overall_feedback": "<string>",
  "confidence": <0-1 number>
}
PROMPT
            .$this->assessmentOutputLanguageReminder();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function studentIdentityUserPrompt(array $payload): string
    {
        $documentText = $payload['document_text'] ?? $payload['text'] ?? '';

        return <<<PROMPT
Read the student identity from this academic submission (cover page, title page, or header only).

Rules:
1. Extract NIM / student ID and full name ONLY if explicitly written in the document.
2. Do not guess from filename or invent values.
3. If a field is missing, return null for that field.

Document text (excerpt):
\"\"\"
{$documentText}
\"\"\"

Return JSON:
{
  "nim": "<student ID or null>",
  "name": "<full name or null>",
  "confidence": <0-1 number>,
  "evidence": "<short quote showing NIM/name or empty>"
}
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeStudentIdentityResult(array $data, array $meta = []): array
    {
        $nim = trim((string) ($data['nim'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        return array_merge([
            'nim' => $nim !== '' ? $nim : null,
            'name' => $name !== '' ? $name : null,
            'confidence' => (float) ($data['confidence'] ?? 0),
            'evidence' => (string) ($data['evidence'] ?? ''),
        ], $meta);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function analyzeQuestionUserPrompt(array $payload): string
    {
        $question = $payload['question_text'] ?? '';
        $type = $payload['question_type'] ?? '';
        $topic = $payload['topic'] ?? '';

        return <<<PROMPT
Analyze this academic exam question. Do not invent course materials that are not implied by the question text.

Topic: {$topic}
Type: {$type}
Question:
{$question}

Return JSON:
{
  "difficulty": "easy|medium|hard",
  "cognitive_level": "remember|understand|apply|analyze|evaluate|create",
  "key_concepts": ["..."],
  "expected_answer_outline": "...",
  "common_mistakes": ["..."],
  "confidence": <0-1 number>
}
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function feedbackUserPrompt(array $payload): string
    {
        $context = json_encode($payload['assessment_result'] ?? $payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $tone = $payload['tone'] ?? 'constructive academic';

        return <<<PROMPT
Generate formative feedback for the student in a {$tone} tone.
Use only the assessment context below. Do not invent scores or evidence.

Context JSON:
{$context}

Return JSON:
{
  "feedback": "<student-facing feedback>",
  "strengths": ["..."],
  "improvements": ["..."],
  "confidence": <0-1 number>
}
PROMPT
            .$this->assessmentOutputLanguageReminder();
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeJsonResponse(string $content): array
    {
        $content = trim($content);

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches) === 1) {
            $content = trim($matches[1]);
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            throw new \InvalidArgumentException('AI provider returned non-JSON content.');
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    protected function normalizeAssessmentResult(array $data, array $meta = []): array
    {
        return array_merge([
            'score' => (float) ($data['score'] ?? 0),
            'max_score' => (float) ($data['max_score'] ?? 0),
            'criteria' => array_values($data['criteria'] ?? []),
            'overall_feedback' => (string) ($data['overall_feedback'] ?? ''),
            'confidence' => (float) ($data['confidence'] ?? 0),
        ], $meta);
    }
}
