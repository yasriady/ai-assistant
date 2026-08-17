<?php

namespace App\Services\Document\Extractors;

use App\Services\Document\Contracts\ExtractorInterface;
use RuntimeException;
use Smalot\PdfParser\Parser;

class PdfExtractor implements ExtractorInterface
{
    public function __construct(
        protected ?Parser $parser = null,
    ) {
        $this->parser ??= new Parser;
    }

    public function extract(string $path): string
    {
        if (! is_file($path)) {
            throw new RuntimeException("PDF file not found: {$path}");
        }

        try {
            $document = $this->parser->parseFile($path);
            $text = trim($document->getText());
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to extract text from PDF: '.$e->getMessage(), previous: $e);
        }

        return $text;
    }

    public function supports(?string $mimeType, ?string $extension = null): bool
    {
        $extension = $extension !== null ? strtolower(ltrim($extension, '.')) : null;

        return in_array($mimeType, ['application/pdf', 'application/x-pdf'], true)
            || $extension === 'pdf';
    }
}
