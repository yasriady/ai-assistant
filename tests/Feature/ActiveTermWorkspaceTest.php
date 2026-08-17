<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Models\Course;
use App\Models\User;
use App\Support\AcademicTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActiveTermWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function lecturer_workspace_only_shows_courses_in_active_term(): void
    {
        $lecturer = User::factory()->create([
            'role' => UserRole::Lecturer,
            'active_term_code' => '20261',
        ]);

        Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'IF2101',
            'name' => 'Current Term Course',
            'term_code' => '20261',
            'is_active' => true,
        ]);

        Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'IF2101',
            'name' => 'Previous Term Course',
            'term_code' => '20252',
            'is_active' => true,
        ]);

        $this->actingAs($lecturer)
            ->withSession(['active_term_code' => '20261'])
            ->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Current Term Course')
            ->assertDontSee('Previous Term Course');

        session(['active_term_code' => '20261']);

        Livewire::actingAs($lecturer)
            ->test(CoursesIndex::class)
            ->assertSee('Current Term Course')
            ->assertDontSee('Previous Term Course');
    }

    #[Test]
    public function lecturer_can_switch_active_term(): void
    {
        $lecturer = User::factory()->create([
            'role' => UserRole::Lecturer,
            'active_term_code' => '20261',
        ]);

        $this->actingAs($lecturer)
            ->from(route('dashboard'))
            ->get(route('term.switch', '20252'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame('20252', session('active_term_code'));
        $this->assertSame('20252', $lecturer->fresh()->active_term_code);
        $this->assertTrue(AcademicTerm::isValid('20252'));
    }
}
