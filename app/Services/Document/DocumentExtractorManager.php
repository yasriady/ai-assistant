<?php

namespace App\Services\Document;

use App\Services\Document\Contracts\ExtractorInterface;
use App\Services\Document\Extractors\DocxExtractor;
use App\Services\Document\Extractors\ImageOcrExtractor;
use App\Services\Document\Extractors\PdfExtractor;
use App\Services\Document\Extractors\TextExtractor;
use InvalidArgumentException;

class DocumentExtractorManager
{
    /** @var list<ExtractorInterface> */
    protected array $extractors;

    /**
     * @param  list<ExtractorInterface>|null  $extractors
     */
    public function __construct(?array $extractors = null)
    {
        $this->extractors = $extractors ?? [
            new PdfExtractor,
            new DocxExtractor,
            new TextExtractor,
            new ImageOcrExtractor,
        ];
    }

    public function extract(string $path, ?string $mimeType = null, ?string $extension = null): string
    {
        return $this->resolve($mimeType, $extension ?? pathinfo($path, PATHINFO_EXTENSION))->extract($path);
    }

    public function resolve(?string $mimeType, ?string $extension = null): ExtractorInterface
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($mimeType, $extension)) {
                return $extractor;
            }
        }

        $hint = $mimeType ?: ($extension ?: 'unknown');

        throw new InvalidArgumentException("No document extractor registered for type [{$hint}].");
    }

    /**
     * @return list<ExtractorInterface>
     */
    public function extractors(): array
    {
        return $this->extractors;
    }
}
