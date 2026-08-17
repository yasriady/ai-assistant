<?php

namespace App\Services\Document\Extractors;

use App\Services\Document\Contracts\ExtractorInterface;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\IOFactory;
use RuntimeException;

class DocxExtractor implements ExtractorInterface
{
    public function extract(string $path): string
    {
        if (! is_file($path)) {
            throw new RuntimeException("DOCX file not found: {$path}");
        }

        try {
            $phpWord = IOFactory::load($path);
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to read DOCX file: '.$e->getMessage(), previous: $e);
        }

        $chunks = [];

        foreach ($phpWord->getSections() as $section) {
            $chunks[] = $this->extractFromContainer($section);
        }

        return trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", array_filter($chunks))) ?? '');
    }

    public function supports(?string $mimeType, ?string $extension = null): bool
    {
        $extension = $extension !== null ? strtolower(ltrim($extension, '.')) : null;

        return in_array($mimeType, [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
        ], true) || in_array($extension, ['docx', 'doc'], true);
    }

    protected function extractFromContainer(AbstractContainer $container): string
    {
        $parts = [];

        foreach ($container->getElements() as $element) {
            if ($element instanceof Text || $element instanceof Title) {
                $parts[] = $this->normalizeText($element->getText());
            } elseif ($element instanceof TextRun) {
                $parts[] = $this->extractFromContainer($element);
            } elseif ($element instanceof AbstractContainer) {
                $parts[] = $this->extractFromContainer($element);
            } elseif (method_exists($element, 'getElements')) {
                /** @var AbstractContainer $element */
                $parts[] = $this->extractFromContainer($element);
            } elseif (method_exists($element, 'getText')) {
                $parts[] = $this->normalizeText($element->getText());
            }
        }

        return implode("\n", array_filter(array_map('trim', $parts)));
    }

    protected function normalizeText(mixed $text): string
    {
        if ($text instanceof TextRun) {
            return $this->extractFromContainer($text);
        }

        if (is_string($text) || is_numeric($text)) {
            return (string) $text;
        }

        return '';
    }
}
