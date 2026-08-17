# Architecture

## Overview

Modular Laravel monolith: Blade + Livewire UI, service-layer assessment engines, queue-backed document/AI jobs, pluggable AI providers.

```
User (lecturer/admin)
 └── Course
      └── Assessment (type → engine)
           ├── Rubric (document/project)
           ├── Exam questions (exam)
           └── Submissions
                ├── Files (private disk)
                ├── Answers
                ├── AI assessments
                └── Final assessment (lecturer)
```

## Assessment engines

| Engine | Types | Scoring |
|--------|-------|---------|
| `document` | assignment, practical_report, journal, paper | Rubric + AI |
| `exam` | quiz, midterm, final, essay, mixed | MCQ/TF deterministic; essay AI |
| `project` | project | Rubric + AI (extensible) |

## Key layers

- **HTTP / Livewire** — courses, students, rubrics, assessments, review, admin AI settings
- **Services** — `DocumentAssessmentService`, `ExamAssessmentService`, `RubricScoringService`, `DeterministicGradingService`, `ReviewService`, `AIManager`, extractors, analytics, export
- **Jobs** — extract text, OCR, assess document/answer, analytics
- **Policies** — course/assessment/submission ownership (admin bypass)
- **Audit** — `AuditLogger` on score accept/modify/finalize

## Document flow

Upload → validate → extract/normalize → queue → AI assess (rubric) → structured result → lecturer review → finalize

## Exam flow

Build exam from question bank → upload/parse answers → deterministic grade MCQ/TF → AI for essay → review → finalize

## Storage

Student files live on the private disk (`storage/app/private`), served only via signed routes.
