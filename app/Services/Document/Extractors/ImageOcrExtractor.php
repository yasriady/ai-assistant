<?php

namespace App\Services\Document\Extractors;

use App\Services\Document\Contracts\ExtractorInterface;
use RuntimeException;

/**
 * Architecture stub for image / scanned-PDF OCR.
 * Wire a real OCR engine (e.g. Tesseract, cloud Vision) before enabling.
 */
class ImageOcrExtractor implements ExtractorInterface
{
    public function extract(string $path): string
    {
        throw new RuntimeException(
            'OCR is not configured. ImageOcrExtractor is an architecture stub. '.
            'Configure an OCR engine (e.g. Tesseract or a cloud Vision API) before extracting text from images or scanned PDFs. '.
            "Attempted path: {$path}"
        );
    }

    public function supports(?string $mimeType, ?string $extension = null): bool
    {
        $extension = $extension !== null ? strtolower(ltrim($extension, '.')) : null;

        return in_array($mimeType, [
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/webp',
            'image/tiff',
            'image/gif',
        ], true) || in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'tif', 'tiff', 'gif'], true);
    }
}
