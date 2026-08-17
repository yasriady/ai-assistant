<?php

namespace App\Support;

class StudentFilenameParser
{
    /**
     * Parse student NIM (and optional name) from filenames like:
     * 230101001_Name.pdf, 230101001-Name.docx, 230101001 Name.txt
     *
     * @return array{nim: string|null, name: string|null, original: string}
     */
    public function parse(string $filename): array
    {
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $basename = trim($basename);

        // Leading numeric NIM (6–20 digits), then separator, then optional name.
        if (preg_match('/^(\d{6,20})(?:[_\-\s]+(.+))?$/u', $basename, $matches) === 1) {
            return [
                'nim' => $matches[1],
                'name' => isset($matches[2]) ? $this->normalizeName($matches[2]) : null,
                'original' => $filename,
            ];
        }

        // NIM embedded after optional prefix: Assignment_230101001_Name
        if (preg_match('/(?:^|[_\-\s])(\d{6,20})(?:[_\-\s]+(.+))?$/u', $basename, $matches) === 1) {
            return [
                'nim' => $matches[1],
                'name' => isset($matches[2]) ? $this->normalizeName($matches[2]) : null,
                'original' => $filename,
            ];
        }

        return [
            'nim' => null,
            'name' => $this->normalizeName($basename),
            'original' => $filename,
        ];
    }

    public function extractNim(string $filename): ?string
    {
        return $this->parse($filename)['nim'];
    }

    protected function normalizeName(string $name): string
    {
        $name = str_replace(['_', '-'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return trim($name);
    }
}
