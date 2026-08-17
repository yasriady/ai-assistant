<?php

namespace App\Services\Assessment;

class StudentDocumentIdentityExtractor
{
    /**
     * Extract student NIM and name from document text (cover page / header).
     *
     * @return array{nim: string|null, name: string|null, confidence: float, source: string}
     */
    public function extract(string $text): array
    {
        $sample = $this->sampleText($text);
        $header = mb_substr($sample, 0, 2500);

        $nim = $this->extractNim($header);
        $name = $this->extractName($header) ?? $this->extractAuthorLine($header);

        $confidence = 0.0;
        $source = 'none';

        if ($nim && $name) {
            $confidence = 0.85;
            $source = 'regex';
        } elseif ($nim) {
            $confidence = 0.65;
            $source = 'regex';
        } elseif ($name) {
            $confidence = 0.45;
            $source = 'regex';
        }

        return [
            'nim' => $nim,
            'name' => $name,
            'confidence' => $confidence,
            'source' => $source,
        ];
    }

    protected function sampleText(string $text): string
    {
        $text = trim($text);

        return mb_substr($text, 0, 6000);
    }

    protected function extractNim(string $text): ?string
    {
        $patterns = [
            '/\bNIM\s*[:\.\-]?\s*(\d{6,20})\b/iu',
            '/\bN\.?\s*I\.?\s*M\.?\s*[:\.\-]?\s*(\d{6,20})\b/iu',
            '/Nomor Induk Mahasiswa\s*[:\.\-]?\s*(\d{6,20})/iu',
            '/Student ID\s*[:\.\-]?\s*(\d{6,20})/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    protected function extractName(string $text): ?string
    {
        $patterns = [
            '/\bNama(?:\s+Lengkap|\s+Mahasiswa|\s+Penulis)?\s*[:\.\-]?\s*([^\n\r\d]{3,120})/iu',
            '/\bName\s*[:\.\-]?\s*([^\n\r\d]{3,120})/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                return $this->cleanName($matches[1]);
            }
        }

        return null;
    }

    protected function extractAuthorLine(string $text): ?string
    {
        $lines = preg_split('/\R/u', $text) ?: [];

        foreach (array_slice($lines, 0, 12) as $line) {
            $line = trim($line);
            if ($line === '' || mb_strlen($line) > 120) {
                continue;
            }

            if (preg_match('/^([A-Za-z][A-Za-z\s\.\']{2,80}?)\d*,/u', $line, $matches) === 1) {
                return $this->cleanName($matches[1]);
            }
        }

        return null;
    }

    protected function cleanName(string $name): ?string
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        $name = trim($name, " \t\n\r\0\x0B:.-");

        if ($name === '' || mb_strlen($name) < 3) {
            return null;
        }

        if (preg_match('/^(jurnal|laporan|tugas|makalah|bab|bab i|cover|halaman)/iu', $name) === 1) {
            return null;
        }

        return $name;
    }
}
