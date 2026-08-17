<?php

namespace App\Services\Rps;

use InvalidArgumentException;

class RpsDraftValidator
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     midterm_week: int,
     *     cpmks: list<array{code: string, description: string, cpl_codes: list<string>}>,
     *     topics: list<array{week_number: int, title: string, description: string, cpmk_codes: list<string>}>
     * }
     */
    public function validate(array $data): array
    {
        if (! isset($data['cpmks'], $data['topics'], $data['midterm_week'])) {
            throw new InvalidArgumentException('RPS draft missing required top-level fields.');
        }

        if (! is_numeric($data['midterm_week'])) {
            throw new InvalidArgumentException('midterm_week must be numeric.');
        }

        $midtermWeek = (int) $data['midterm_week'];
        if ($midtermWeek < 1 || $midtermWeek > 20) {
            throw new InvalidArgumentException('midterm_week must be between 1 and 20.');
        }

        if (! is_array($data['cpmks']) || $data['cpmks'] === []) {
            throw new InvalidArgumentException('cpmks must be a non-empty array.');
        }

        if (! is_array($data['topics']) || $data['topics'] === []) {
            throw new InvalidArgumentException('topics must be a non-empty array.');
        }

        $cpmks = [];
        $cpmkCodes = [];

        foreach (array_values($data['cpmks']) as $index => $row) {
            if (! is_array($row)) {
                throw new InvalidArgumentException("cpmks[{$index}] must be an object.");
            }

            $code = trim((string) ($row['code'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));

            if ($code === '' || $description === '') {
                throw new InvalidArgumentException("cpmks[{$index}] requires code and description.");
            }

            $cplCodes = $this->stringList($row['cpl_codes'] ?? [], "cpmks[{$index}].cpl_codes");

            $cpmks[] = [
                'code' => $code,
                'description' => $description,
                'cpl_codes' => $cplCodes,
            ];
            $cpmkCodes[] = $code;
        }

        $topics = [];

        foreach (array_values($data['topics']) as $index => $row) {
            if (! is_array($row)) {
                throw new InvalidArgumentException("topics[{$index}] must be an object.");
            }

            if (! is_numeric($row['week_number'] ?? null)) {
                throw new InvalidArgumentException("topics[{$index}].week_number must be numeric.");
            }

            $weekNumber = (int) $row['week_number'];
            $title = trim((string) ($row['title'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));

            if ($weekNumber < 1 || $weekNumber > 20) {
                throw new InvalidArgumentException("topics[{$index}].week_number out of range.");
            }

            if ($title === '') {
                throw new InvalidArgumentException("topics[{$index}] requires title.");
            }

            $topicCpmkCodes = $this->stringList($row['cpmk_codes'] ?? [], "topics[{$index}].cpmk_codes");

            foreach ($topicCpmkCodes as $cpmkCode) {
                if (! in_array($cpmkCode, $cpmkCodes, true)) {
                    throw new InvalidArgumentException("topics[{$index}] references unknown CPMK [{$cpmkCode}].");
                }
            }

            $topics[] = [
                'week_number' => $weekNumber,
                'title' => $title,
                'description' => $description,
                'cpmk_codes' => $topicCpmkCodes,
            ];
        }

        return [
            'midterm_week' => $midtermWeek,
            'cpmks' => $cpmks,
            'topics' => $topics,
        ];
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value, string $field): array
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException("{$field} must be an array.");
        }

        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $value,
        )));
    }
}
