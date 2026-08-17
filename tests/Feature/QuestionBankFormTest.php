<?php

namespace Tests\Feature;

use App\Enums\QuestionBankPurpose;
use App\Enums\UserRole;
use App\Livewire\QuestionBanks\Form;
use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\User;
use App\Support\AcademicTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuestionBankFormTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function edit_form_can_mount_when_bank_purpose_is_cast_to_enum(): void
    {
        $lecturer = User::factory()->create([
            'role' => UserRole::Lecturer,
            'active_term_code' => AcademicTerm::current(),
        ]);

        $course = Course::query()->create([
            'user_id' => $lecturer->id,
            'code' => 'INF46',
            'name' => 'Mobile Computing',
            'term_code' => AcademicTerm::current(),
            'is_active' => true,
        ]);

        $questionBank = QuestionBank::query()->create([
            'course_id' => $course->id,
            'user_id' => $lecturer->id,
            'name' => 'Soal UTS Mobile Computing',
            'description' => 'Question bank for midterm exam.',
            'purpose' => QuestionBankPurpose::Midterm,
        ]);

        Livewire::actingAs($lecturer)
            ->test(Form::class, ['questionBank' => $questionBank])
            ->assertSet('course_id', $course->id)
            ->assertSet('name', 'Soal UTS Mobile Computing')
            ->assertSet('purpose', QuestionBankPurpose::Midterm->value);
    }
}
