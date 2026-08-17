<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfirmDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function delete_requires_typing_delete_case_insensitive(): void
    {
        $lecturer = User::factory()->create(['role' => UserRole::Lecturer]);
        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'IF1001',
            'name' => 'Algoritma',
            'term_code' => \App\Support\AcademicTerm::current(),
            'is_active' => true,
        ]);

        Livewire::actingAs($lecturer)
            ->test(CoursesIndex::class)
            ->call('askDelete', $course->id, 'IF1001 — Algoritma')
            ->assertSet('confirmingDeletion', true)
            ->set('deleteConfirmation', 'please')
            ->call('confirmDelete')
            ->assertSet('confirmingDeletion', true);

        $this->assertDatabaseHas('courses', ['id' => $course->id]);

        Livewire::actingAs($lecturer)
            ->test(CoursesIndex::class)
            ->call('askDelete', $course->id, 'IF1001 — Algoritma')
            ->set('deleteConfirmation', 'DeLeTe')
            ->call('confirmDelete')
            ->assertSet('confirmingDeletion', false);

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
