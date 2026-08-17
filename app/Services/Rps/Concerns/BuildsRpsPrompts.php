<?php

namespace App\Services\Rps\Concerns;

trait BuildsRpsPrompts
{
    protected function rpsSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an academic curriculum designer for Indonesian higher education (OBE framework).

Rules:
1. Generate measurable CPMK (Course Learning Outcomes) aligned with the selected CPL (Program Learning Outcomes).
2. Plan weekly topics for one semester. Place UTS at the configured midterm week.
3. Weeks before midterm cover foundational material; weeks after midterm cover advanced/integration topics.
4. Use Bahasa Indonesia for descriptions unless the course clearly requires English.
5. Respond with ONE JSON object only. No markdown fences.
6. Do not invent CPL codes outside the provided list.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function rpsUserPrompt(array $payload): string
    {
        $course = json_encode($payload['course'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $cpls = json_encode($payload['cpls'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $weeks = (int) ($payload['total_weeks'] ?? 16);
        $midterm = (int) ($payload['midterm_week'] ?? 8);
        $notes = trim((string) ($payload['teaching_notes'] ?? ''));
        $reference = trim((string) ($payload['reference_excerpt'] ?? ''));

        $notesBlock = $notes !== '' ? "Additional lecturer notes:\n{$notes}\n" : '';
        $referenceBlock = $reference !== ''
            ? "Optional reference excerpt (use only if relevant):\n\"\"\"\n{$reference}\n\"\"\"\n"
            : '';

        return <<<PROMPT
Generate an MVP RPS draft for this course.

Course (JSON):
{$course}

Selected CPL to address (JSON):
{$cpls}

Total weeks: {$weeks}
Midterm (UTS) week: {$midterm}

{$notesBlock}{$referenceBlock}

Return JSON with this exact schema:
{
  "midterm_week": {$midterm},
  "cpmks": [
    {
      "code": "CPMK-1",
      "description": "<measurable outcome in Bahasa Indonesia>",
      "cpl_codes": ["S09", "P01"]
    }
  ],
  "topics": [
    {
      "week_number": 1,
      "title": "<topic title>",
      "description": "<brief learning activities / material>",
      "cpmk_codes": ["CPMK-1"]
    }
  ]
}

Requirements:
- Provide 4-6 CPMK items with codes CPMK-1, CPMK-2, ...
- Provide exactly {$weeks} topic rows (week_number 1..{$weeks})
- Week {$midterm} should be titled for UTS (no new heavy material)
- Final week should reflect UAS / evaluasi akhir
- Each topic must reference at least one valid CPMK code
- Map each CPMK to relevant CPL codes from the input list only
PROMPT;
    }
}
