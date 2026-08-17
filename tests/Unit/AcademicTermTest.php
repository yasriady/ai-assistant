<?php

namespace Tests\Unit;

use App\Support\AcademicTerm;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AcademicTermTest extends TestCase
{
    #[Test]
    public function it_computes_current_term_for_indonesian_calendar(): void
    {
        $this->assertSame('20251', AcademicTerm::current(Carbon::parse('2025-09-01')));
        $this->assertSame('20251', AcademicTerm::current(Carbon::parse('2026-01-15')));
        $this->assertSame('20252', AcademicTerm::current(Carbon::parse('2026-02-01')));
        $this->assertSame('20252', AcademicTerm::current(Carbon::parse('2026-07-31')));
        $this->assertSame('20261', AcademicTerm::current(Carbon::parse('2026-08-17')));
    }

    #[Test]
    public function it_navigates_previous_and_next_terms(): void
    {
        $this->assertSame('20252', AcademicTerm::next('20251'));
        $this->assertSame('20261', AcademicTerm::next('20252'));
        $this->assertSame('20251', AcademicTerm::previous('20252'));
        $this->assertSame('20242', AcademicTerm::previous('20251'));
    }
}
