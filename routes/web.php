<?php

use App\Http\Controllers\AssessmentExportController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\SubmissionFileController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\ThemeController;
use App\Livewire\Admin\AiSettings;
use App\Livewire\Analytics\ExamAnalytics;
use App\Livewire\Assessments\ExamBuilder;
use App\Livewire\Assessments\Form as AssessmentForm;
use App\Livewire\Assessments\Index as AssessmentsIndex;
use App\Livewire\Assessments\ReviewSubmission;
use App\Livewire\Assessments\Show as AssessmentShow;
use App\Livewire\Assessments\UploadSubmissions;
use App\Livewire\Cpls\ImportDocx as CplImportDocx;
use App\Livewire\Cpls\Index as CplsIndex;
use App\Livewire\Courses\Form as CourseForm;
use App\Livewire\Courses\GenerateRps as CourseGenerateRps;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Livewire\Courses\Rps as CourseRps;
use App\Livewire\Dashboard;
use App\Livewire\QuestionBanks\Form as QuestionBankForm;
use App\Livewire\QuestionBanks\Index as QuestionBanksIndex;
use App\Livewire\QuestionBanks\QuestionForm;
use App\Livewire\Rubrics\Form as RubricForm;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Rubrics\Index as RubricsIndex;
use App\Livewire\Students\Form as StudentForm;
use App\Livewire\Students\ImportCsv;
use App\Livewire\Students\Index as StudentsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['id', 'en'])
    ->name('locale.switch');

Route::get('/theme/{theme}', ThemeController::class)
    ->whereIn('theme', ['default', 'vivid'])
    ->middleware('auth')
    ->name('theme.switch');

Route::middleware('auth')->group(function (): void {
    Route::get('/term/{term}', TermController::class)
        ->where('term', '[0-9]{4}[12]')
        ->name('term.switch');
});

// Root: guests see login; authenticated users are redirected to dashboard by LoginController.
Route::middleware('guest')->group(function (): void {
    Route::get('/', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/courses', CoursesIndex::class)->name('courses.index');
    Route::get('/courses/create', CourseForm::class)->name('courses.create');
    Route::get('/courses/{course}/edit', CourseForm::class)->name('courses.edit');
    Route::get('/courses/{course}/rps', CourseRps::class)->name('courses.rps');
    Route::get('/courses/{course}/rps/generate', CourseGenerateRps::class)->name('courses.rps.generate');

    Route::get('/cpls', CplsIndex::class)->name('cpls.index');
    Route::get('/cpls/import', CplImportDocx::class)->name('cpls.import');

    Route::get('/students', StudentsIndex::class)->name('students.index');
    Route::get('/students/create', StudentForm::class)->name('students.create');
    Route::get('/students/import', ImportCsv::class)->name('students.import');
    Route::get('/students/{student}/edit', StudentForm::class)->name('students.edit');

    Route::get('/rubrics', RubricsIndex::class)->name('rubrics.index');
    Route::get('/rubrics/create', RubricForm::class)->name('rubrics.create');
    Route::get('/rubrics/{rubric}/edit', RubricForm::class)->name('rubrics.edit');

    Route::get('/assessments', AssessmentsIndex::class)->name('assessments.index');
    Route::get('/assessments/create', AssessmentForm::class)->name('assessments.create');
    Route::get('/assessments/{assessment}', AssessmentShow::class)->name('assessments.show');
    Route::get('/assessments/{assessment}/edit', AssessmentForm::class)->name('assessments.edit');
    Route::get('/assessments/{assessment}/upload', UploadSubmissions::class)->name('assessments.upload');
    Route::get('/assessments/{assessment}/exam-builder', ExamBuilder::class)->name('assessments.exam-builder');
    Route::get('/assessments/{assessment}/submissions/{submission}/review', ReviewSubmission::class)->name('assessments.review');
    Route::get('/assessments/{assessment}/analytics', ExamAnalytics::class)->name('assessments.analytics');
    Route::get('/assessments/{assessment}/export', AssessmentExportController::class)->name('assessments.export');

    Route::get('/question-banks', QuestionBanksIndex::class)->name('question-banks.index');
    Route::get('/question-banks/create', QuestionBankForm::class)->name('question-banks.create');
    Route::get('/question-banks/{questionBank}/edit', QuestionBankForm::class)->name('question-banks.edit');
    Route::get('/question-banks/{questionBank}/questions/create', QuestionForm::class)->name('question-banks.questions.create');
    Route::get('/question-banks/{questionBank}/questions/{question}/edit', QuestionForm::class)->name('question-banks.questions.edit');

    Route::get('/settings', SettingsIndex::class)->name('settings');

    Route::get('/admin/ai-settings', AiSettings::class)
        ->middleware('role:admin')
        ->name('admin.ai-settings');

    Route::get('/files/{file}', SubmissionFileController::class)
        ->middleware('signed')
        ->name('files.download');
});
