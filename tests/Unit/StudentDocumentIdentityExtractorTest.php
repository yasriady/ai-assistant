<?php

namespace Tests\Unit;

use App\Services\Assessment\StudentDocumentIdentityExtractor;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentDocumentIdentityExtractorTest extends TestCase
{
    #[Test]
    public function it_extracts_nim_and_name_from_cover_page(): void
    {
        $text = <<<'TEXT'
JURNAL MOBILE COMPUTING

NIM : 202555202010
Nama : Syahid Rodhi Billah
Program Studi : Informatika
TEXT;

        $result = (new StudentDocumentIdentityExtractor)->extract($text);

        $this->assertSame('202555202010', $result['nim']);
        $this->assertSame('Syahid Rodhi Billah', $result['name']);
        $this->assertGreaterThan(0.6, $result['confidence']);
    }

    #[Test]
    public function it_extracts_nim_without_name_label(): void
    {
        $text = "Laporan Praktikum\nNIM 230101001\nDosen Pengampu: Dr. X";

        $result = (new StudentDocumentIdentityExtractor)->extract($text);

        $this->assertSame('230101001', $result['nim']);
    }
}
