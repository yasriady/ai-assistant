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
PROMPT;
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
PROMPT;
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
PROMPT;
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
PROMPT;
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
