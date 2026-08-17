<?php

namespace App\Services\Cpl;

use App\Enums\CplCategory;
use App\Models\CplOutcome;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use RuntimeException;

class CplDocxImporter
{
    /**
     * @return array{created: int, updated: int, total: int, program: string}
     */
    public function import(string $path, string $program = 'S1 Informatika', bool $replaceMissing = false): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("DOCX file not found: {$path}");
        }

        $rows = $this->parseRows($path);

        if ($rows === []) {
            throw new RuntimeException('No CPL rows found in the document.');
        }

        $created = 0;
        $updated = 0;
        $officialCodes = [];

        DB::transaction(function () use ($rows, $program, $replaceMissing, &$created, &$updated, &$officialCodes): void {
            foreach ($rows as $row) {
                $officialCodes[] = $row['official_code'];

                $outcome = CplOutcome::query()->where('official_code', $row['official_code'])->first();

                if ($outcome) {
                    $outcome->update([
                        'program' => $program,
                        'category' => $row['category'],
                        'code' => $row['code'],
                        'description' => $row['description'],
                        'order_index' => $row['order_index'],
                    ]);
                    $updated++;
                } else {
                    CplOutcome::query()->create([
                        'program' => $program,
                        'category' => $row['category'],
                        'code' => $row['code'],
                        'official_code' => $row['official_code'],
                        'description' => $row['description'],
                        'order_index' => $row['order_index'],
                    ]);
                    $created++;
                }
            }

            if ($replaceMissing) {
                CplOutcome::query()
                    ->where('program', $program)
                    ->whereNotIn('official_code', $officialCodes)
                    ->delete();
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($rows),
            'program' => $program,
        ];
    }

    /**
     * @return list<array{category: CplCategory, code: string, official_code: string, description: string, order_index: int}>
     */
    public function parseRows(string $path): array
    {
        try {
            $document = IOFactory::load($path);
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to read DOCX file: '.$e->getMessage(), previous: $e);
        }

        $parsed = [];
        $order = 0;

        foreach ($document->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (! $element instanceof Table) {
                    continue;
                }

                foreach ($element->getRows() as $row) {
                    $cells = [];
                    foreach ($row->getCells() as $cell) {
                        $cells[] = $this->cellText($cell);
                    }

                    if (count($cells) < 4) {
                        continue;
                    }

                    [$no, $code, $officialCode, $description] = [
                        trim($cells[0]),
                        trim($cells[1]),
                        trim($cells[2]),
                        trim($cells[3]),
                    ];

                    if ($this->isHeaderRow($no, $code)) {
                        continue;
                    }

                    if ($this->isCategoryRow($no, $code)) {
                        continue;
                    }

                    $category = CplCategory::fromCode($code);
                    if ($category === null || $description === '') {
                        continue;
                    }

                    if (! preg_match('/^CPL\d+$/i', $officialCode)) {
                        continue;
                    }

                    $parsed[] = [
                        'category' => $category,
                        'code' => strtoupper($code),
                        'official_code' => strtoupper($officialCode),
                        'description' => $description,
                        'order_index' => $order++,
                    ];
                }
            }
        }

        return $parsed;
    }

    protected function isHeaderRow(string $no, string $code): bool
    {
        return strcasecmp($no, 'No') === 0 || strcasecmp($code, 'CPL') === 0;
    }

    protected function isCategoryRow(string $no, string $code): bool
    {
        if (in_array(strtoupper($no), ['A', 'B', 'C', 'D'], true) && $code === '') {
            return true;
        }

        return str_contains(strtolower($no), 'rumusan');
    }

    protected function cellText(AbstractContainer $cell): string
    {
        $parts = [];

        foreach ($cell->getElements() as $element) {
            if ($element instanceof Text) {
                $parts[] = (string) $element->getText();
            } elseif ($element instanceof TextRun) {
                $parts[] = $element->getText();
            } elseif ($element instanceof AbstractContainer) {
                $parts[] = $this->cellText($element);
            } elseif (method_exists($element, 'getText')) {
                $text = $element->getText();
                if (is_string($text)) {
                    $parts[] = $text;
                }
            }
        }

        return trim(preg_replace('/\s+/u', ' ', implode(' ', array_filter($parts))) ?? '');
    }
}
