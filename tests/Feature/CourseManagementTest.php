<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Courses\Form as CourseForm;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Models\Course;
use App\Models\User;
use App\Support\AcademicTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CourseManagementTest extends TestCase
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
    public function lecturer_can_view_courses_index(): void
    {
        $lecturer = $this->lecturer();

        $this->actingAs($lecturer)
            ->get(route('courses.index'))
            ->assertOk();

        Livewire::actingAs($lecturer)
            ->test(CoursesIndex::class)
            ->assertOk();
    }

    #[Test]
    public function lecturer_can_create_a_course(): void
    {
        $lecturer = $this->lecturer();
        $term = AcademicTerm::current();

        Livewire::actingAs($lecturer)
            ->test(CourseForm::class)
            ->set('code', 'IF2201')
            ->set('name', 'Struktur Data')
            ->set('term_code', $term)
            ->set('class_name', 'B')
            ->set('description', 'Demo course')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('courses', [
            'user_id' => $lecturer->id,
            'code' => 'IF2201',
            'name' => 'Struktur Data',
            'term_code' => $term,
        ]);
    }

    #[Test]
    public function lecturer_can_update_own_course(): void
    {
        $lecturer = $this->lecturer();
        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'IF1001',
            'name' => 'Old Name',
            'term_code' => AcademicTerm::current(),
            'is_active' => true,
        ]);

        Livewire::actingAs($lecturer)
            ->test(CourseForm::class, ['course' => $course])
            ->set('name', 'Updated Name')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function guest_cannot_access_courses(): void
    {
        $this->get(route('courses.index'))
            ->assertRedirect(route('login'));
    }
}
