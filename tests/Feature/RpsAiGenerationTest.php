<?php

namespace Tests\Feature;

use App\Enums\CplCategory;
use App\Enums\UserRole;
use App\Models\CplOutcome;
use App\Models\Course;
use App\Models\User;
use App\Services\Rps\RpsGenerationService;
use App\Support\AcademicTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RpsAiGenerationTest extends TestCase
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
    public function lecturer_can_open_rps_generate_page(): void
    {
        $lecturer = $this->lecturer();
        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'INF46',
            'name' => 'Mobile Computing',
            'term_code' => AcademicTerm::current(),
            'is_active' => true,
        ]);

        $this->actingAs($lecturer)
            ->get(route('courses.rps.generate', $course))
            ->assertOk();
    }

    #[Test]
    public function ai_generates_and_applies_mvp_rps_draft(): void
    {
        $lecturer = $this->lecturer();
        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'INF46',
            'name' => 'Mobile Computing',
            'term_code' => AcademicTerm::current(),
            'is_active' => true,
        ]);

        $cpl = CplOutcome::query()->create([
            'program' => 'S1 Informatika',
            'category' => CplCategory::Knowledge,
            'code' => 'P01',
            'official_code' => 'CPL28',
            'description' => 'Menguasai konsep teoritis informatika.',
            'order_index' => 0,
        ]);

        $generator = app(RpsGenerationService::class);
        $draft = $generator->generate($course, [$cpl->id], [
            'total_weeks' => 16,
            'midterm_week' => 8,
            'user_id' => $lecturer->id,
        ]);

        $this->assertNotEmpty($draft['cpmks']);
        $this->assertCount(16, $draft['topics']);

        $generator->apply($course, $draft, [$cpl->id]);

        $course->refresh();
        $this->assertSame(8, $course->midterm_week);
        $this->assertGreaterThanOrEqual(3, $course->cpmks()->count());
        $this->assertSame(16, $course->topics()->count());
        $this->assertTrue($course->cplOutcomes()->whereKey($cpl->id)->exists());
    }
}
