<?php

namespace Tests\Feature;

use App\Enums\CplCategory;
use App\Enums\UserRole;
use App\Models\CplOutcome;
use App\Models\User;
use App\Services\Cpl\CplDocxImporter;
use App\Support\AcademicTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CplImportTest extends TestCase
{
    use RefreshDatabase;

    protected function lecturer(): User
    {
        return User::factory()->create([
            'role' => UserRole::Lecturer,
            'active_term_code' => AcademicTerm::current(),
        ]);
    }

    #[Test]
    public function lecturer_can_view_cpl_index(): void
    {
        $this->actingAs($this->lecturer())
            ->get(route('cpls.index'))
            ->assertOk();
    }

    #[Test]
    public function docx_importer_parses_uab_cpl_document(): void
    {
        $path = base_path('wip/CAPAIAN PEMBELAJARAN LULUSAN_Universitas_Awal_Bros_Pekanbaru.docx');

        $this->assertFileExists($path);

        $rows = app(CplDocxImporter::class)->parseRows($path);

        $this->assertGreaterThanOrEqual(45, count($rows));
        $this->assertSame('S01', $rows[0]['code']);
        $this->assertSame('CPL01', $rows[0]['official_code']);
        $this->assertSame(CplCategory::Attitude, $rows[0]['category']);
    }

    #[Test]
    public function lecturer_can_import_cpl_from_docx(): void
    {
        $path = base_path('wip/CAPAIAN PEMBELAJARAN LULUSAN_Universitas_Awal_Bros_Pekanbaru.docx');

        $result = app(CplDocxImporter::class)->import($path, 'S1 Informatika');

        $this->assertGreaterThanOrEqual(45, $result['total']);
        $this->assertDatabaseHas('cpl_outcomes', [
            'official_code' => 'CPL09',
            'code' => 'S09',
        ]);

        $this->assertGreaterThanOrEqual(45, CplOutcome::query()->count());
    }
}
