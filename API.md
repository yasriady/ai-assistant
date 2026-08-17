# API

## Current MVP

This release is **web-first**. Lecturers use Blade + Livewire over session authentication. There is **no public JSON API** under `/api/v1` yet.

Primary surface: `routes/web.php` (login, dashboard, courses, students, rubrics, assessments, question banks, review, export, admin AI settings).

Signed file download: `GET /files/{file}` (`files.download`).

## Future `/api/v1` (planned)

When introduced, versioned REST endpoints may include:

| Method | Path | Purpose |
|--------|------|---------|
| POST | `/api/v1/auth/token` | Token / Sanctum login |
| GET | `/api/v1/courses` | List courses |
| GET/POST | `/api/v1/assessments` | List/create assessments |
| POST | `/api/v1/assessments/{id}/submissions` | Upload submissions |
| GET | `/api/v1/submissions/{id}` | Submission + AI result |
| POST | `/api/v1/submissions/{id}/finalize` | Lecturer finalize |
| GET | `/api/v1/assessments/{id}/export` | Export results |

Auth: Laravel Sanctum (or equivalent), role-aware policies reused from the web app.

Until then, automate via the web UI, artisan commands, or queued jobs—not a public HTTP API.
