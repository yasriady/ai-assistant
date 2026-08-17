<?php

namespace App\Services\Document\Contracts;

interface ExtractorInterface
{
    /**
     * Extract plain text from a local filesystem path.
     */
    public function extract(string $path): string;

    /**
     * Whether this extractor supports the given mime type and/or extension.
     */
    public function supports(?string $mimeType, ?string $extension = null): bool;
}
