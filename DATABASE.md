# Database

MySQL 8 (production). PHPUnit uses SQLite `:memory:` (`phpunit.xml`).

## Core academic tables

| Table | Purpose |
|-------|---------|
| `users` | Lecturers/admins (`role`, optional `google_id`) |
| `courses` | Owned by `user_id` |
| `students` | Unique `nim` |
| `course_student` | Enrollment |
| `cpmks` | Course learning outcomes |
| `rubrics` / `rubric_criteria` / `rubric_levels` | Weighted rubrics |
| `assessments` | Type + engine (`document` / `exam` / `project`) |
| `assessment_cpmk` | Assessment ↔ CPMK |

## Questions & submissions

| Table | Purpose |
|-------|---------|
| `question_banks`, `questions`, `question_options` | Banks and items |
| `exam_questions` | Assessment ↔ question link |
| `submissions` | Per student per assessment |
| `submission_files` | Private file metadata + extraction status |
| `answers` | Exam answers (MCQ/TF/essay) |

## AI & audit

| Table | Purpose |
|-------|---------|
| `ai_assessments`, `ai_assessment_items` | AI results per submission/answer |
| `final_assessments` | Lecturer-confirmed grades |
| `feedback` | AI or human comments |
| `ai_usage` | Token/cost tracking |
| `audit_logs` | Score/review actions |
| `prompt_templates` | System/custom prompts |
| `ai_settings` | Active provider defaults |

## Seeding

`php artisan db:seed` → `DemoSeeder`: admin, lecturer, course, students, rubric, assignment + exam, sample submission with AI result, prompt templates, null AI setting.

## Migrations

- `0001_01_01_000000_*` — users/sessions/cache/jobs
- `2024_01_01_000010_*` — academic core
- `2024_01_01_000020_*` — questions & submissions
- `2024_01_01_000030_*` — AI & audit
