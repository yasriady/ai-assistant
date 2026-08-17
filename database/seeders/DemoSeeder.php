<?php

namespace Database\Seeders;

use App\Enums\AssessmentEngine;
use App\Enums\AssessmentType;
use App\Enums\Difficulty;
use App\Enums\JobProcessStatus;
use App\Enums\QuestionScopeType;
use App\Enums\QuestionType;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\AiAssessment;
use App\Models\AiAssessmentItem;
use App\Models\AiSetting;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Cpmk;
use App\Models\ExamQuestion;
use App\Models\PromptTemplate;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Rubric;
use App\Models\RubricCriterion;
use App\Models\RubricLevel;
use App\Models\Student;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        $lecturer = User::query()->updateOrCreate(
            ['email' => 'demo@academic.test'],
            [
                'name' => 'Demo Lecturer',
                'password' => Hash::make('password'),
                'role' => UserRole::Lecturer,
                'email_verified_at' => now(),
            ]
        );

        $course = Course::query()->updateOrCreate(
            [
                'user_id' => $lecturer->id,
                'code' => 'IF2101',
                'term_code' => \App\Support\AcademicTerm::current(),
            ],
            [
                'name' => 'Pengantar Pemrograman',
                'semester' => \App\Support\AcademicTerm::semesterName(\App\Support\AcademicTerm::current(), 'id'),
                'academic_year' => \App\Support\AcademicTerm::academicYear(\App\Support\AcademicTerm::current()),
                'class_name' => 'A',
                'description' => 'Demo course for the AI Academic Assessment Platform.',
                'midterm_week' => 8,
                'is_active' => true,
            ]
        );

        $cpmk1 = Cpmk::query()->updateOrCreate(
            ['course_id' => $course->id, 'code' => 'CPMK-1'],
            ['description' => 'Memahami konsep algoritma dan pemrograman dasar.', 'order_index' => 0]
        );
        $cpmk2 = Cpmk::query()->updateOrCreate(
            ['course_id' => $course->id, 'code' => 'CPMK-2'],
            ['description' => 'Menerapkan struktur kontrol dan struktur data sederhana.', 'order_index' => 1]
        );

        $topicEarly = CourseTopic::query()->updateOrCreate(
            ['course_id' => $course->id, 'week_number' => 2, 'title' => 'Algoritma dan Pemrograman Dasar'],
            ['description' => 'Pengenalan algoritma, variabel, dan tipe data.', 'order_index' => 0]
        );
        $topicMid = CourseTopic::query()->updateOrCreate(
            ['course_id' => $course->id, 'week_number' => 6, 'title' => 'Struktur Kontrol'],
            ['description' => 'Percabangan, perulangan, dan flowchart.', 'order_index' => 1]
        );
        $topicLate = CourseTopic::query()->updateOrCreate(
            ['course_id' => $course->id, 'week_number' => 12, 'title' => 'Struktur Data Lanjut'],
            ['description' => 'Linked list, stack, queue.', 'order_index' => 2]
        );

        $topicEarly->cpmks()->sync([$cpmk1->id]);
        $topicMid->cpmks()->sync([$cpmk1->id, $cpmk2->id]);
        $topicLate->cpmks()->sync([$cpmk2->id]);

        $lecturer->forceFill([
            'active_term_code' => \App\Support\AcademicTerm::current(),
        ])->save();

        $admin->forceFill([
            'active_term_code' => \App\Support\AcademicTerm::current(),
        ])->save();

        $students = collect([
            ['nim' => '230101001', 'name' => 'Andi Pratama'],
            ['nim' => '230101002', 'name' => 'Budi Santoso'],
            ['nim' => '230101003', 'name' => 'Citra Lestari'],
            ['nim' => '230101004', 'name' => 'Dewi Anggraini'],
            ['nim' => '230101005', 'name' => 'Eko Wibowo'],
        ])->map(function (array $data) use ($course) {
            $student = Student::query()->updateOrCreate(
                ['nim' => $data['nim']],
                [
                    'name' => $data['name'],
                    'email' => strtolower(str_replace(' ', '.', $data['name'])).'@student.test',
                    'program' => 'Informatika',
                    'class_name' => 'A',
                ]
            );

            $course->students()->syncWithoutDetaching([$student->id]);

            return $student;
        });

        $rubric = Rubric::query()->updateOrCreate(
            [
                'user_id' => $lecturer->id,
                'course_id' => $course->id,
                'name' => 'Demo Document Rubric',
            ],
            [
                'description' => 'Weighted rubric for assignment / report assessment.',
                'version' => 1,
                'is_template' => false,
            ]
        );

        $criteriaSpec = [
            ['name' => 'Pendahuluan', 'weight' => 10, 'max_score' => 10, 'description' => 'Latar belakang dan rumusan masalah.'],
            ['name' => 'Landasan Teori', 'weight' => 15, 'max_score' => 15, 'description' => 'Relevansi teori dan sitasi.'],
            ['name' => 'Metodologi', 'weight' => 20, 'max_score' => 20, 'description' => 'Kejelasan langkah kerja.'],
            ['name' => 'Hasil & Analisis', 'weight' => 25, 'max_score' => 25, 'description' => 'Kualitas analisis data/hasil.'],
            ['name' => 'Kesimpulan', 'weight' => 15, 'max_score' => 15, 'description' => 'Ringkasan dan rekomendasi.'],
            ['name' => 'Tata Tulis', 'weight' => 15, 'max_score' => 15, 'description' => 'Struktur, ejaan, dan format.'],
        ];

        foreach ($criteriaSpec as $index => $spec) {
            $criterion = RubricCriterion::query()->updateOrCreate(
                [
                    'rubric_id' => $rubric->id,
                    'name' => $spec['name'],
                ],
                [
                    'description' => $spec['description'],
                    'weight' => $spec['weight'],
                    'max_score' => $spec['max_score'],
                    'order_index' => $index,
                ]
            );

            $levels = [
                ['name' => 'Kurang', 'min_score' => 0, 'max_score' => round($spec['max_score'] * 0.4, 2)],
                ['name' => 'Cukup', 'min_score' => round($spec['max_score'] * 0.4 + 0.01, 2), 'max_score' => round($spec['max_score'] * 0.7, 2)],
                ['name' => 'Baik', 'min_score' => round($spec['max_score'] * 0.7 + 0.01, 2), 'max_score' => (float) $spec['max_score']],
            ];

            foreach ($levels as $levelIndex => $level) {
                RubricLevel::query()->updateOrCreate(
                    [
                        'rubric_criterion_id' => $criterion->id,
                        'name' => $level['name'],
                    ],
                    [
                        'description' => "Level {$level['name']} untuk {$spec['name']}.",
                        'min_score' => $level['min_score'],
                        'max_score' => $level['max_score'],
                        'order_index' => $levelIndex,
                    ]
                );
            }
        }

        $assignment = Assessment::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $lecturer->id,
                'title' => 'Laporan Praktikum 1 — Algoritma Dasar',
            ],
            [
                'rubric_id' => $rubric->id,
                'description' => 'Demo assignment assessed with a weighted rubric.',
                'type' => AssessmentType::Assignment,
                'engine' => AssessmentEngine::Document,
                'instructions' => 'Upload laporan praktikum dalam format PDF/DOCX. Nama file: NIM_Nama.pdf',
                'due_at' => now()->addWeeks(2),
                'max_score' => 100,
                'status' => 'published',
                'rubric_version' => $rubric->version,
                'settings' => ['allow_late' => false],
            ]
        );

        $bank = QuestionBank::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $lecturer->id,
                'name' => 'Demo Question Bank',
            ],
            [
                'description' => 'Sample MCQ, true/false, and essay questions.',
                'purpose' => \App\Enums\QuestionBankPurpose::General,
            ]
        );

        $mcq = Question::query()->updateOrCreate(
            [
                'question_bank_id' => $bank->id,
                'question_text' => 'Manakah yang termasuk struktur kontrol percabangan?',
            ],
            [
                'course_id' => $course->id,
                'user_id' => $lecturer->id,
                'topic' => 'Dasar Pemrograman',
                'scope_type' => QuestionScopeType::Specific,
                'question_type' => QuestionType::MultipleChoice,
                'expected_answer' => 'if-else',
                'key_concepts' => ['selection', 'branching', 'if'],
                'difficulty' => Difficulty::Easy,
                'max_score' => 5,
            ]
        );
        $mcq->courseTopics()->sync([$topicMid->id]);
        $mcq->cpmks()->sync([$cpmk2->id]);

        $mcqOptions = [
            ['label' => 'A', 'option_text' => 'for loop', 'is_correct' => false],
            ['label' => 'B', 'option_text' => 'if-else', 'is_correct' => true],
            ['label' => 'C', 'option_text' => 'array', 'is_correct' => false],
            ['label' => 'D', 'option_text' => 'variable', 'is_correct' => false],
        ];

        foreach ($mcqOptions as $optIndex => $opt) {
            QuestionOption::query()->updateOrCreate(
                [
                    'question_id' => $mcq->id,
                    'label' => $opt['label'],
                ],
                [
                    'option_text' => $opt['option_text'],
                    'is_correct' => $opt['is_correct'],
                    'order_index' => $optIndex,
                ]
            );
        }

        $tf = Question::query()->updateOrCreate(
            [
                'question_bank_id' => $bank->id,
                'question_text' => 'Algoritma harus memiliki langkah yang berhingga (finite).',
            ],
            [
                'course_id' => $course->id,
                'user_id' => $lecturer->id,
                'topic' => 'Algoritma',
                'scope_type' => QuestionScopeType::Specific,
                'question_type' => QuestionType::TrueFalse,
                'expected_answer' => 'true',
                'key_concepts' => ['algorithm', 'finiteness'],
                'difficulty' => Difficulty::Easy,
                'max_score' => 5,
            ]
        );

        $tf->courseTopics()->sync([$topicEarly->id]);
        $tf->cpmks()->sync([$cpmk1->id]);

        foreach ([
            ['label' => 'T', 'option_text' => 'Benar', 'is_correct' => true],
            ['label' => 'F', 'option_text' => 'Salah', 'is_correct' => false],
        ] as $tfIndex => $opt) {
            QuestionOption::query()->updateOrCreate(
                [
                    'question_id' => $tf->id,
                    'label' => $opt['label'],
                ],
                [
                    'option_text' => $opt['option_text'],
                    'is_correct' => $opt['is_correct'],
                    'order_index' => $tfIndex,
                ]
            );
        }

        $essay = Question::query()->updateOrCreate(
            [
                'question_bank_id' => $bank->id,
                'question_text' => 'Jelaskan perbedaan antara algoritma dan program komputer.',
            ],
            [
                'course_id' => $course->id,
                'user_id' => $lecturer->id,
                'topic' => 'Algoritma',
                'scope_type' => QuestionScopeType::General,
                'question_type' => QuestionType::Essay,
                'expected_answer' => 'Algoritma adalah langkah logis penyelesaian masalah; program adalah implementasi algoritma dalam bahasa pemrograman.',
                'key_concepts' => ['algoritma', 'program', 'implementasi', 'bahasa'],
                'difficulty' => Difficulty::Medium,
                'max_score' => 10,
            ]
        );

        $exam = Assessment::query()->updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $lecturer->id,
                'title' => 'Kuis 1 — Konsep Dasar',
            ],
            [
                'description' => 'Demo exam with linked question bank items.',
                'type' => AssessmentType::Quiz,
                'engine' => AssessmentEngine::Exam,
                'instructions' => 'Jawab semua soal. MCQ dan true/false dinilai deterministik; essay memakai AI assist.',
                'due_at' => now()->addWeek(),
                'max_score' => 20,
                'status' => 'published',
                'settings' => ['shuffle' => false],
            ]
        );

        foreach ([
            [$mcq, 0, 5],
            [$tf, 1, 5],
            [$essay, 2, 10],
        ] as [$question, $order, $max]) {
            ExamQuestion::query()->updateOrCreate(
                [
                    'assessment_id' => $exam->id,
                    'question_id' => $question->id,
                ],
                [
                    'order_index' => $order,
                    'max_score' => $max,
                ]
            );
        }

        $firstStudent = $students->first();

        $sampleText = <<<'TEXT'
LAPORAN PRAKTIKUM 1
NIM: 230101001
Nama: Andi Pratama

1. Pendahuluan
Praktikum ini membahas algoritma dasar dan struktur kontrol. Latar belakangnya adalah kebutuhan memahami langkah penyelesaian masalah secara sistematis sebelum menulis program.

2. Landasan Teori
Algoritma adalah urutan langkah berhingga untuk menyelesaikan masalah. Struktur kontrol meliputi sequence, selection (if-else), dan iteration (for/while).

3. Metodologi
Langkah kerja: merancang flowchart, menulis pseudocode, mengimplementasikan dalam bahasa pemrograman, lalu menguji beberapa kasus input.

4. Hasil & Analisis
Program berhasil mengeksekusi percabangan dan perulangan sesuai spesifikasi. Analisis menunjukkan bahwa kesalahan umum terjadi pada kondisi batas (boundary conditions).

5. Kesimpulan
Mahasiswa memahami perbedaan algoritma dan program, serta mampu menerapkan struktur kontrol. Disarankan latihan tambahan pada nested selection.

Tata tulis mengikuti format laporan praktikum kampus.
TEXT;

        $submission = Submission::query()->updateOrCreate(
            [
                'assessment_id' => $assignment->id,
                'student_id' => $firstStudent->id,
            ],
            [
                'status' => SubmissionStatus::Assessed,
                'extracted_text' => $sampleText,
                'ai_score' => 82.5,
                'processed_at' => now(),
            ]
        );

        $aiAssessment = AiAssessment::query()->updateOrCreate(
            [
                'submission_id' => $submission->id,
                'answer_id' => null,
                'provider' => 'null',
                'model' => 'null-mock',
                'attempt' => 1,
            ],
            [
                'status' => JobProcessStatus::Completed,
                'score' => 82.5,
                'max_score' => 100,
                'confidence' => 0.72,
                'overall_feedback' => 'Demo AI assessment: laporan cukup lengkap. Perkuat sitasi pada landasan teori dan perjelas analisis kasus uji.',
                'prompt_version' => '1.0',
                'rubric_version' => (string) $rubric->version,
                'structured_result' => [
                    'score' => 82.5,
                    'max_score' => 100,
                    'confidence' => 0.72,
                    'overall_feedback' => 'Demo AI assessment sample.',
                ],
                'raw_response' => ['mock' => true, 'seeded' => true],
            ]
        );

        $itemScores = [
            ['Pendahuluan', 8.5, 10],
            ['Landasan Teori', 11.0, 15],
            ['Metodologi', 17.0, 20],
            ['Hasil & Analisis', 20.0, 25],
            ['Kesimpulan', 13.0, 15],
            ['Tata Tulis', 13.0, 15],
        ];

        foreach ($itemScores as $itemIndex => [$name, $score, $max]) {
            AiAssessmentItem::query()->updateOrCreate(
                [
                    'ai_assessment_id' => $aiAssessment->id,
                    'criterion_name' => $name,
                ],
                [
                    'score' => $score,
                    'max_score' => $max,
                    'evidence' => "Cuplikan terkait {$name} ditemukan pada teks laporan demo.",
                    'reasoning' => "Skor demo berdasarkan kelengkapan bagian {$name}.",
                    'feedback' => "Pertahankan kekuatan pada {$name}; perbaiki detail bila diperlukan.",
                    'insufficient_evidence' => false,
                    'order_index' => $itemIndex,
                ]
            );
        }

        PromptTemplate::query()->updateOrCreate(
            ['key' => 'document_assessment'],
            [
                'name' => 'Document / Essay Assessment',
                'system_prompt' => 'You are an academic assessment assistant. Score student documents against the provided rubric. Cite evidence from the student text only. Never invent quotes. Final grades are decided by the lecturer.',
                'assessment_prompt' => "Assess the following student document.\n\nRubric:\n{{rubric}}\n\nStudent text:\n{{document_text}}\n\nReturn JSON with keys: score, max_score, criteria, overall_feedback, confidence.",
                'feedback_prompt' => 'Write constructive academic feedback based on the structured assessment result. Be specific and actionable.',
                'is_system' => true,
                'version' => 1,
            ]
        );

        PromptTemplate::query()->updateOrCreate(
            ['key' => 'essay_answer_assessment'],
            [
                'name' => 'Essay Answer Assessment',
                'system_prompt' => 'You are an academic exam grader assistant. Evaluate essay answers against expected concepts. Prefer evidence from the student answer. Lecturer decides the final mark.',
                'assessment_prompt' => "Question:\n{{question_text}}\n\nExpected answer / key concepts:\n{{expected_answer}}\n\nStudent answer:\n{{answer_text}}\n\nMax score: {{max_score}}\n\nReturn JSON with keys: score, max_score, criteria, overall_feedback, confidence.",
                'feedback_prompt' => 'Provide short feedback highlighting strengths and missing concepts.',
                'is_system' => true,
                'version' => 1,
            ]
        );

        AiSetting::query()->updateOrCreate(
            ['provider' => 'null'],
            [
                'model' => 'null-mock',
                'temperature' => 0.2,
                'max_tokens' => 4000,
                'is_active' => true,
                'config' => ['note' => 'Default demo provider — no external API keys required.'],
            ]
        );

        // Ensure only one active setting points at null provider for demos.
        AiSetting::query()->where('provider', '!=', 'null')->update(['is_active' => false]);

        unset($admin, $assignment);
    }
}
