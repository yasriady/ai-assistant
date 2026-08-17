<?php

namespace Tests\Unit;

use App\Support\StudentFilenameParser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentFilenameParserTest extends TestCase
{
    #[Test]
    public function it_parses_nim_and_name_with_underscore(): void
    {
        $result = (new StudentFilenameParser)->parse('230101001_Andi_Pratama.pdf');

        $this->assertSame('230101001', $result['nim']);
        $this->assertSame('Andi Pratama', $result['name']);
    }

    #[Test]
    public function it_parses_nim_with_hyphen_separator(): void
    {
        $result = (new StudentFilenameParser)->parse('230101002-Budi.docx');

        $this->assertSame('230101002', $result['nim']);
        $this->assertSame('Budi', $result['name']);
    }

    #[Test]
    public function it_parses_nim_only(): void
    {
        $result = (new StudentFilenameParser)->parse('230101003.pdf');

        $this->assertSame('230101003', $result['nim']);
        $this->assertNull($result['name']);
    }

    #[Test]
    public function it_extracts_embedded_nim(): void
    {
        $result = (new StudentFilenameParser)->parse('Assignment_230101004_Citra.pdf');

        $this->assertSame('230101004', $result['nim']);
        $this->assertSame('Citra', $result['name']);
    }

    #[Test]
    public function it_returns_null_nim_when_missing(): void
    {
        $result = (new StudentFilenameParser)->parse('laporan-akhir.pdf');

        $this->assertNull($result['nim']);
        $this->assertSame('laporan akhir', $result['name']);
    }
}
