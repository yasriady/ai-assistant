<?php

namespace App\Services\Document\Extractors;

use App\Services\Document\Contracts\ExtractorInterface;
use RuntimeException;

class TextExtractor implements ExtractorInterface
{
    public function extract(string $path): string
    {
        if (! is_file($path)) {
            throw new RuntimeException("Text file not found: {$path}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Unable to read text file: {$path}");
        }

        // Strip UTF-8 BOM if present.
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        }

        return trim($contents);
    }

    public function supports(?string $mimeType, ?string $extension = null): bool
    {
        $extension = $extension !== null ? strtolower(ltrim($extension, '.')) : null;

        return in_array($mimeType, ['text/plain', 'text/csv', 'application/csv'], true)
            || in_array($extension, ['txt', 'text', 'md', 'csv'], true);
    }
}
