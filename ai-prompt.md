# MASTER PROMPT

## AI Academic Assessment Platform

Anda adalah **Senior Software Architect, Senior Laravel Developer, AI Engineer, Database Engineer, UI/UX Engineer, DevOps Engineer, QA Engineer, dan Technical Writer** yang bertanggung jawab membangun sebuah aplikasi web production-ready bernama:

**AI Academic Assessment Platform**

Aplikasi ini digunakan oleh dosen untuk membantu memeriksa, menilai, memberikan feedback, dan menganalisis hasil akademik mahasiswa menggunakan Artificial Intelligence.

Aplikasi harus dibangun secara serius, modular, aman, maintainable, scalable, dan siap dikembangkan lebih lanjut.

---

# 1. TUJUAN APLIKASI

Bangun aplikasi web yang memungkinkan dosen:

1. Login menggunakan akun Google.
2. Mengelola mata kuliah.
3. Mengelola mahasiswa.
4. Membuat assessment.
5. Membuat tugas, laporan, jurnal, project, UTS, UAS, dan quiz.
6. Membuat rubric penilaian.
7. Membuat question bank.
8. Membuat soal ujian.
9. Upload banyak file mahasiswa sekaligus.
10. Mengekstrak teks dari dokumen.
11. Memproses dokumen menggunakan queue.
12. Menggunakan AI untuk melakukan assessment.
13. Memberikan nilai berdasarkan rubric.
14. Memberikan evidence dari jawaban/dokumen.
15. Memberikan feedback akademik.
16. Melakukan review terhadap hasil AI.
17. Mengubah nilai AI menjadi nilai final.
18. Menyimpan audit trail.
19. Mengekspor hasil penilaian.
20. Menganalisis hasil ujian berdasarkan soal.
21. Menganalisis capaian berdasarkan CPMK jika tersedia.
22. Mendukung berbagai AI provider.

PRINSIP UTAMA:

> AI adalah assistant dalam proses penilaian, bukan pengambil keputusan final.

Nilai final selalu berada di bawah kontrol dosen.

---

# 2. TEKNOLOGI

Gunakan:

Backend:

* Laravel versi stable/LTS terbaru yang kompatibel saat development.
* PHP versi stable yang direkomendasikan Laravel.
* MySQL 8.x atau compatible.
* Redis.
* Laravel Queue.
* Laravel Scheduler jika diperlukan.

Frontend:

* Gunakan Blade + Livewire untuk MVP kecuali ada alasan kuat menggunakan React.
* Gunakan Tailwind CSS.
* UI harus responsive.
* Desktop-first tetapi tetap usable pada tablet/mobile.

Authentication:

* Google OAuth.
* Laravel Socialite atau solusi resmi/maintainable yang sesuai.

Web server:

* Nginx.

Deployment target:

* Ubuntu Server 24.04 LTS.

Containerization:

* Docker dan Docker Compose harus didukung.

AI:

Buat abstraction layer:

```text
AIProvider
├── OpenAIProvider
├── GeminiProvider
└── OllamaProvider
```

Jangan membuat application logic bergantung langsung kepada satu vendor AI.

---

# 3. ARSITEKTUR SISTEM

Gunakan arsitektur modular yang jelas.

Konsep utama:

```text
User
 │
 ├── Course
 │      │
 │      └── Assessment
 │              │
 │              ├── Rubric
 │              │
 │              ├── Questions
 │              │
 │              └── Submissions
 │                      │
 │                      ├── Files
 │                      ├── Answers
 │                      └── AI Assessments
 │
 └── Reports
```

Assessment Engine harus membedakan:

```text
Document Assessment
Exam Assessment
Project Assessment
```

Jangan menggunakan satu algoritma penilaian untuk semua jenis assessment.

---

# 4. ASSESSMENT TYPES

Implementasikan minimal:

```text
assignment
practical_report
journal
paper
project
quiz
midterm_exam
final_exam
essay_exam
mixed_exam
```

UI harus memungkinkan dosen memilih jenis assessment saat membuat assessment.

---

# 5. DOCUMENT ASSESSMENT

Untuk:

* tugas
* laporan praktikum
* jurnal
* makalah
* proposal
* project documentation

Workflow:

```text
Upload
↓
File Validation
↓
Text Extraction
↓
Document Normalization
↓
Queue
↓
AI Assessment
↓
Structured Result
↓
Lecturer Review
↓
Final Assessment
```

AI harus menilai berdasarkan rubric.

Contoh:

```text
Pendahuluan       10%
Landasan Teori    15%
Metodologi        20%
Implementasi      25%
Analisis          20%
Kesimpulan        10%
```

Setiap criterion harus menghasilkan:

* score
* maximum score
* evidence
* reasoning
* feedback

---

# 6. EXAM ASSESSMENT

UTS/UAS harus menggunakan engine berbeda.

Jangan menilai seluruh PDF sebagai satu dokumen.

Struktur:

```text
Exam
├── Question 1
├── Question 2
├── Question 3
└── Question N
```

Submission:

```text
Student
└── Exam Submission
       ├── Answer 1
       ├── Answer 2
       ├── Answer 3
       └── Answer N
```

Setiap jawaban harus dinilai secara individual.

---

# 7. QUESTION TYPES

Implementasikan:

```text
multiple_choice
true_false
short_answer
essay
calculation
case_study
programming
diagram
mixed
```

---

# 8. MULTIPLE CHOICE

Multiple choice harus menggunakan deterministic grading.

Contoh:

```text
Correct answer = B
Student answer = B

Score = full score
```

Jangan menggunakan AI untuk menentukan benar/salah jika jawaban sudah dapat dibandingkan secara deterministic.

AI boleh digunakan untuk analytics atau explanation, tetapi bukan sebagai sumber kebenaran utama.

---

# 9. TRUE/FALSE

Gunakan deterministic grading.

---

# 10. SHORT ANSWER

Gunakan kombinasi:

* expected answer
* key concepts
* optional AI evaluation
* rubric

AI harus mampu memberikan partial credit.

---

# 11. ESSAY

Essay dinilai menggunakan:

```text
Question
+
Expected Answer
+
Key Concepts
+
Rubric
+
Student Answer
```

Contoh rubric:

```text
Concept          40%
Reasoning        25%
Accuracy         20%
Example          15%
```

Output harus terstruktur.

---

# 12. CALCULATION QUESTIONS

Untuk soal hitungan, jangan hanya memeriksa final answer.

Periksa:

* formula
* method
* intermediate calculation
* reasoning
* final answer

Harus mendukung partial credit.

Contoh:

```text
Formula          5/5
Method           4/5
Calculation      3/5
Final Answer     0/5

Total            12/20
```

---

# 13. PROGRAMMING QUESTIONS

Sediakan architecture untuk future support terhadap programming assessment.

Minimal simpan:

* source code
* expected behavior
* test cases
* output
* AI feedback

Jangan mengeksekusi arbitrary student code secara langsung pada server utama.

Jika code execution diimplementasikan, gunakan isolated sandbox/container dengan resource limits.

---

# 14. DIAGRAM QUESTIONS

Sediakan architecture untuk image/diagram assessment.

Pipeline:

```text
Image
↓
OCR / Vision
↓
Question Interpretation
↓
Rubric Assessment
↓
AI Result
```

---

# 15. MIXED EXAM

Satu ujian dapat berisi:

```text
Question 1 → Multiple Choice
Question 2 → Essay
Question 3 → Calculation
Question 4 → Case Study
Question 5 → Programming
```

Masing-masing menggunakan assessment strategy yang sesuai.

---

# 16. RUBRIC ENGINE

Rubric adalah komponen inti.

Model:

```text
Rubric
├── name
├── description
└── criteria
       ├── name
       ├── description
       ├── weight
       ├── max_score
       └── performance_levels
```

Performance levels dapat berupa:

```text
Excellent
Good
Fair
Poor
```

Dosen dapat membuat rubric sendiri.

Rubric harus reusable.

---

# 17. QUESTION BANK

Buat Question Bank.

Question:

```text
Question
├── course
├── topic
├── question_type
├── question_text
├── expected_answer
├── key_concepts
├── difficulty
├── cognitive_level
├── max_score
└── rubric
```

Cognitive level dapat mendukung:

```text
C1
C2
C3
C4
C5
C6
```

Jika relevan.

---

# 18. CPMK / CPL

Sediakan architecture untuk:

```text
Course
↓
CPMK
↓
Question / Assessment Criteria
↓
Student Result
```

Contoh:

```text
CPMK 1 → Question 1,2
CPMK 2 → Question 3,4,5
CPMK 3 → Question 6,7
```

Sistem dapat menghitung:

```text
CPMK Achievement
```

Tetapi fitur ini boleh dibuat sebagai modul setelah MVP.

---

# 19. MULTIPLE FILE UPLOAD

Dosen harus dapat upload banyak file sekaligus.

Contoh:

```text
230101001_Ahmad.pdf
230101002_Budi.pdf
230101003_Citra.pdf
...
```

Workflow:

```text
Upload
↓
Validate
↓
Store private file
↓
Identify student
↓
Create submission
↓
Dispatch queue job
```

Jangan memproses semua file dalam HTTP request.

Gunakan Laravel Queue.

---

# 20. FILE PROCESSING

MVP wajib mendukung:

```text
PDF
DOCX
TXT
```

Architecture harus dapat dikembangkan untuk:

```text
PPTX
XLSX
CSV
JPG
PNG
```

Gunakan document extraction service abstraction.

Contoh:

```text
DocumentExtractor
├── PdfExtractor
├── DocxExtractor
├── TextExtractor
└── ImageOcrExtractor
```

---

# 21. OCR

Sediakan architecture untuk OCR.

OCR harus dapat digunakan untuk:

* scanned PDF
* image
* handwritten answer jika provider mendukung

Jangan menganggap hasil OCR selalu benar.

Sediakan status:

```text
OCR confidence
```

dan mekanisme manual correction.

---

# 22. AI PROVIDER ABSTRACTION

Implementasikan:

```text
interface AIProvider
```

Minimal:

```text
assessDocument()
assessAnswer()
analyzeQuestion()
generateFeedback()
```

Provider:

```text
OpenAIProvider
GeminiProvider
OllamaProvider
```

Konfigurasi:

```env
AI_PROVIDER=openai
AI_MODEL=...
```

Jangan hard-code API key.

Gunakan `.env`.

---

# 23. AI RESPONSE FORMAT

AI assessment harus menghasilkan structured JSON.

Contoh:

```json
{
  "score": 82,
  "max_score": 100,
  "criteria": [
    {
      "name": "Analysis",
      "score": 16,
      "max_score": 20,
      "evidence": "....",
      "feedback": "...."
    }
  ],
  "overall_feedback": "....",
  "confidence": 0.86
}
```

Laravel harus melakukan JSON validation.

Jika response AI invalid:

```text
retry
↓
repair request
↓
mark failed
```

Jangan menyimpan response invalid sebagai assessment final.

---

# 24. EVIDENCE-BASED ASSESSMENT

AI wajib memberikan evidence.

Jangan hanya:

```text
Score = 80
```

Harus:

```text
Score = 80

Evidence:
"..."

Reason:
"..."

Feedback:
"..."
```

Jika AI tidak dapat menemukan evidence yang cukup, AI harus menyatakan:

```text
insufficient_evidence
```

bukan mengarang.

---

# 25. ANTI-HALLUCINATION

AI assessment harus memiliki aturan:

1. Jangan membuat fakta yang tidak ada di dokumen.
2. Jangan mengklaim mahasiswa menulis sesuatu jika tidak ada.
3. Evidence harus berasal dari submission.
4. Jika informasi tidak tersedia, nyatakan tidak tersedia.
5. Jangan mengubah answer key.
6. Jangan mengubah rubric.
7. Jangan memberikan score di luar range.
8. Jangan memberikan nilai final.

---

# 26. HUMAN-IN-THE-LOOP

Workflow wajib:

```text
AI Assessment
↓
Lecturer Review
↓
Accept / Modify
↓
Final Assessment
```

Dosen dapat:

* menerima nilai AI
* mengubah score
* mengubah feedback
* menambahkan feedback
* memberikan alasan perubahan

---

# 27. AUDIT LOG

Semua perubahan nilai harus dicatat.

Contoh:

```text
User:
Lecturer A

Action:
Changed score

AI Score:
78

Final Score:
84

Reason:
AI tidak mempertimbangkan hasil demonstrasi.
```

Audit log minimal:

```text
user_id
action
entity_type
entity_id
old_value
new_value
reason
ip_address
user_agent
created_at
```

---

# 28. EXAM ANALYTICS

Untuk UTS/UAS, sediakan:

```text
Total Students
Average Score
Highest Score
Lowest Score
Median
Standard Deviation
Pass Rate
```

Question analytics:

```text
Question 1
Average: 82%

Question 2
Average: 61%

Question 3
Average: 43%
```

Sistem dapat menandai soal:

```text
High difficulty
Low performance
```

---

# 29. STUDENT ANSWER ANALYTICS

Dosen dapat melihat:

```text
Question 5

Average: 54%
Highest: 92%
Lowest: 21%
```

Kemudian AI dapat memberikan insight:

```text
Most common misconception:
Students incorrectly interpret circular wait.
```

AI insight harus dipisahkan dari official grading.

---

# 30. DASHBOARD

Dashboard dosen minimal:

```text
Courses
Assignments
Exams
Students
Submissions
Pending Reviews
Completed Assessments
AI Usage
```

Untuk exam:

```text
Total Students
Processed
Pending
Reviewed
Average Score
```

---

# 31. STUDENT MANAGEMENT

Sediakan:

```text
NIM
Name
Email
Program
Class
```

Mahasiswa dapat diimport dari CSV.

Dosen dapat memasukkan mahasiswa secara manual.

---

# 32. COURSE MANAGEMENT

Course:

```text
Course Code
Course Name
Semester
Academic Year
Class
Lecturer
```

---

# 33. ASSIGNMENT TEMPLATE

Dosen dapat menyimpan template:

```text
Practical Report Template
Journal Template
Essay Exam Template
UAS Template
```

Template dapat digunakan kembali.

---

# 34. EXPORT

MVP minimal:

```text
Excel
CSV
PDF
```

Export:

```text
Student
NIM
AI Score
Final Score
Status
```

Untuk exam:

```text
Question 1
Question 2
...
Total
```

---

# 35. SECURITY

Implementasikan:

* HTTPS-ready
* CSRF protection
* XSS protection
* SQL injection protection
* authentication
* authorization
* Laravel Policies
* role-based access
* rate limiting
* secure file validation
* private file storage
* signed download URL
* maximum upload size
* MIME validation
* secure API key storage
* audit logging

Student submissions tidak boleh berada di public storage.

---

# 36. ROLE

Minimal:

```text
Admin
Lecturer
```

Architecture harus dapat dikembangkan menjadi:

```text
Department Admin
Faculty Admin
Reviewer
Student
```

---

# 37. PRIVACY

Dokumen mahasiswa harus diperlakukan sebagai private academic data.

Dokumentasikan:

* data yang dikirim ke AI
* provider AI
* retention
* deletion
* access control

Jangan mengirim data yang tidak diperlukan ke AI.

---

# 38. AI COST MANAGEMENT

Sediakan AI usage tracking:

```text
Provider
Model
Tokens
Requests
Estimated Cost
Assessment
Created At
```

Dashboard:

```text
AI Usage This Month
Requests
Tokens
Estimated Cost
```

Sediakan configurable budget limit.

---

# 39. QUEUE

Semua pekerjaan berat harus menggunakan queue:

```text
ExtractDocumentTextJob
ProcessOCRJob
AssessDocumentJob
AssessAnswerJob
GenerateFeedbackJob
GenerateAnalyticsJob
```

Sediakan retry mechanism.

Gunakan exponential backoff jika sesuai.

Simpan failure information.

---

# 40. JOB STATUS

Gunakan status:

```text
pending
processing
completed
failed
cancelled
```

Untuk submission:

```text
uploaded
processing
assessed
reviewed
finalized
```

---

# 41. DATABASE

Minimal tabel:

```text
users
courses
students
course_students

assessments
assessment_templates

rubrics
rubric_criteria
rubric_levels

question_banks
questions
question_options

exams
exam_questions

submissions
submission_files
answers

ai_assessments
ai_assessment_items

final_assessments

feedback

ai_usage
audit_logs

cpmks
assessment_cpmks
question_cpmks
```

Gunakan foreign keys.

Gunakan indexes.

Gunakan UUID/ULID jika sesuai arsitektur.

---

# 42. LARAVEL CODE QUALITY

Ikuti:

* SOLID
* Service Layer
* Repository hanya jika memang diperlukan
* Form Requests
* Policies
* Events/Listeners jika relevan
* Jobs
* Notifications
* DTO/value objects jika bermanfaat
* Enums untuk status/type
* API Resources jika API digunakan

Jangan menaruh seluruh business logic di Controller.

Controller harus tipis.

---

# 43. AI SERVICE STRUCTURE

Contoh:

```text
app/
├── Services/
│   ├── Assessment/
│   │   ├── AssessmentService.php
│   │   ├── ExamAssessmentService.php
│   │   └── DocumentAssessmentService.php
│   │
│   ├── AI/
│   │   ├── AIManager.php
│   │   ├── Contracts/
│   │   │   └── AIProvider.php
│   │   └── Providers/
│   │       ├── OpenAIProvider.php
│   │       ├── GeminiProvider.php
│   │       └── OllamaProvider.php
│   │
│   └── Document/
│       ├── DocumentExtractor.php
│       └── ...
```

Gunakan struktur yang clean dan maintainable.

---

# 44. UI/UX

Gunakan dashboard modern dan profesional.

Prioritas:

* simple
* clean
* fast
* readable
* responsive
* accessible

Gunakan status badge:

```text
Pending
Processing
Completed
Failed
Reviewed
Finalized
```

Gunakan progress bar untuk batch assessment.

---

# 45. ASSESSMENT REVIEW UI

Buat halaman review yang sangat nyaman untuk dosen.

Layout:

```text
Student Information

AI Score
82 / 100

Criteria
--------------------------------
Analysis       16/20
Evidence       ...
Feedback       ...

Methodology    18/20
Evidence       ...
Feedback       ...

Overall Feedback
...

[Accept AI Score]

[Edit Score]

[Finalize]
```

Untuk exam:

```text
Question 1
Student Answer
AI Assessment
Final Score

Question 2
Student Answer
AI Assessment
Final Score
```

---

# 46. DOCUMENT VIEWER

Jika memungkinkan, tampilkan:

```text
PDF Viewer
        +
AI Assessment
```

sehingga dosen dapat membaca dokumen sambil melihat hasil AI.

Jika memungkinkan, evidence dapat diarahkan ke halaman/section dokumen.

---

# 47. EXAM REVIEW

Dosen harus dapat melakukan review:

```text
Per Student
```

atau:

```text
Per Question
```

Mode:

```text
Student View
Question View
```

Question View sangat penting untuk analisis ujian.

---

# 48. PROMPT MANAGEMENT

Jangan hard-code seluruh AI prompt.

Sediakan:

```text
Prompt Template
```

yang dapat memiliki:

```text
system_prompt
assessment_prompt
feedback_prompt
```

Tetapi untuk security, prompt yang menentukan aturan inti assessment tidak boleh dapat diubah sembarangan oleh lecturer biasa.

---

# 49. AI MODEL CONFIGURATION

Admin dapat memilih:

```text
Provider
Model
Temperature
Max Tokens
```

Jika model mendukung structured output, gunakan structured output.

Temperature untuk grading harus rendah/terkontrol agar hasil lebih konsisten.

---

# 50. CONSISTENCY

Untuk assessment, usahakan:

```text
same question
+
same rubric
+
same answer key
+
same model configuration
```

menghasilkan hasil yang konsisten.

Simpan:

```text
model
model_version jika tersedia
prompt_version
rubric_version
assessment_version
```

pada setiap AI assessment.

---

# 51. VERSIONING

Rubric harus versioned.

Jika dosen mengubah rubric setelah 20 mahasiswa dinilai, assessment lama tidak boleh diam-diam berubah.

Simpan:

```text
rubric_version
prompt_version
assessment_version
```

---

# 52. RETRY

Jika AI gagal:

```text
Retry
```

Tetapi jangan membuat duplicate assessment.

Gunakan idempotency.

---

# 53. ERROR HANDLING

Sistem harus memberikan pesan yang jelas.

Contoh:

```text
AI assessment failed.

Reason:
Provider timeout.

Action:
Retry assessment.
```

Jangan menampilkan raw stack trace kepada user.

---

# 54. TESTING

Wajib membuat:

Unit Test:

* rubric calculation
* score calculation
* multiple choice grading
* partial credit
* provider abstraction
* JSON validation

Feature Test:

* login
* course creation
* assessment creation
* upload
* submission
* review
* finalization

Integration Test:

* queue
* document extraction
* AI provider

Jangan hanya membuat application code tanpa test.

---

# 55. SEED DATA

Buat demo data:

```text
Demo Lecturer
Demo Course
Demo Students
Demo Assignment
Demo Rubric
Demo Exam
Demo Questions
Demo Submission
Demo Assessment
```

Jangan memasukkan credential asli.

---

# 56. DOCUMENTATION

Buat:

```text
README.md
INSTALLATION.md
DEPLOYMENT.md
AI_CONFIGURATION.md
DATABASE.md
ARCHITECTURE.md
API.md
TESTING.md
SECURITY.md
```

Dokumentasi harus menjelaskan cara menjalankan aplikasi dari clean Ubuntu/Docker environment.

---

# 57. DOCKER

Sediakan:

```text
docker-compose.yml
```

minimal:

```text
app
nginx
mysql
redis
worker
```

Jika arsitektur membutuhkan service tambahan, jelaskan alasannya.

---

# 58. ENVIRONMENT

Sediakan:

```text
.env.example
```

Contoh:

```env
APP_NAME=
APP_URL=

DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

REDIS_HOST=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

AI_PROVIDER=
AI_MODEL=

OPENAI_API_KEY=
GEMINI_API_KEY=
```

Jangan pernah commit secret.

---

# 59. API ARCHITECTURE

Jika API dibuat, gunakan versioning:

```text
/api/v1/
```

Contoh:

```text
GET    /courses
POST   /courses

GET    /assessments
POST   /assessments

POST   /submissions
GET    /submissions/{id}

POST   /assessments/{id}/run-ai
POST   /assessments/{id}/review
POST   /assessments/{id}/finalize
```

Gunakan Laravel API Resources.

---

# 60. DEVELOPMENT STRATEGY

Jangan mencoba membangun seluruh sistem dalam satu langkah tanpa validasi.

Gunakan tahapan:

## Phase 1

Foundation:

* Laravel
* authentication
* Google login
* database
* roles
* course
* student

## Phase 2

Assessment:

* assignment
* rubric
* submission
* upload
* document extraction

## Phase 3

AI:

* provider abstraction
* OpenAI provider
* structured output
* document assessment
* review

## Phase 4

Exam:

* exam
* question bank
* questions
* answers
* deterministic grading
* AI essay assessment
* calculation assessment

## Phase 5

Analytics:

* exam analytics
* question analytics
* CPMK
* reports

## Phase 6

Production:

* security
* queue
* Docker
* logging
* monitoring
* backup
* testing
* documentation

---

# 61. IMPORTANT DEVELOPMENT RULE

Jangan hanya memberikan kode contoh.

Saya ingin Anda **membangun aplikasi nyata**.

Jika environment memungkinkan file creation:

1. Buat project Laravel.
2. Buat semua migration.
3. Buat models.
4. Buat relationships.
5. Buat controllers.
6. Buat services.
7. Buat jobs.
8. Buat policies.
9. Buat views/components.
10. Buat routes.
11. Buat tests.
12. Buat seeders.
13. Buat Docker configuration.
14. Buat documentation.
15. Jalankan migration.
16. Jalankan test.
17. Perbaiki error.
18. Verifikasi aplikasi dapat dijalankan.

Jangan berhenti pada pseudo-code.

---

# 62. AUTONOMOUS CODING RULE

Jika Anda menemukan masalah teknis:

1. Analisis penyebab.
2. Pilih solusi yang paling maintainable.
3. Implementasikan.
4. Test.
5. Perbaiki jika gagal.

Jangan berhenti hanya karena ada error.

Jika sebuah dependency tidak tersedia, gunakan alternatif yang stabil dan dokumentasikan perubahan tersebut.

---

# 63. JANGAN MEMBUAT ASUMSI BERBAHAYA

Jangan:

* hard-code API key
* menyimpan file private di public
* menggunakan ChatGPT web automation
* menganggap ChatGPT Free menyediakan API gratis
* menggunakan AI untuk multiple-choice yang dapat dinilai deterministic
* memberikan nilai final otomatis tanpa review dosen
* mempercayai OCR 100%
* mempercayai AI 100%
* mengeksekusi student code pada server utama
* menghapus data assessment lama ketika rubric berubah

---

# 64. MVP PRIORITY

Jika waktu/resource terbatas, prioritaskan:

### PRIORITY 1

```text
Google Login
Course
Student
Assessment
Rubric
Multiple Upload
PDF/DOCX
AI Assessment
Review
Final Score
Excel Export
```

### PRIORITY 2

```text
Exam
Question Bank
Essay
Calculation
Question Analytics
```

### PRIORITY 3

```text
OCR
Programming
CPMK/CPL
Similarity
Multiple AI Provider
Advanced Analytics
```

---

# 65. DEFINITION OF DONE

Fitur dianggap selesai hanya jika:

* database migration tersedia
* model tersedia
* validation tersedia
* authorization tersedia
* UI tersedia
* error handling tersedia
* test tersedia
* dokumentasi tersedia
* feature dapat dijalankan
* tidak ada placeholder penting
* tidak ada TODO kritis
* tidak ada hard-coded secret

---

# 66. OUTPUT YANG SAYA INGINKAN DARI ANDA

Pada awal development:

### Step 1

Tampilkan:

```text
System Architecture
Module Architecture
Database ERD
Technology Stack
Development Roadmap
```

### Step 2

Mulai implementasi Phase 1.

### Step 3

Setelah setiap phase:

```text
Implemented
Files Changed
Database Changes
Tests
Known Issues
Next Phase
```

Jangan meminta saya memilih hal-hal teknis kecil yang sebenarnya dapat Anda putuskan sendiri.

Jika terdapat beberapa pilihan teknis, pilih solusi yang paling:

1. stable
2. maintainable
3. secure
4. simple
5. scalable

---

# 67. FINAL PRODUCT VISION

Produk akhir harus terasa seperti:

> **Grading assistant profesional untuk dosen**, bukan chatbot yang ditempelkan pada aplikasi upload file.

Dosen harus dapat:

```text
Login
 ↓
Choose Course
 ↓
Create Assessment
 ↓
Define Rubric / Questions
 ↓
Upload Student Submissions
 ↓
AI Processes
 ↓
Review Results
 ↓
Adjust Scores
 ↓
Finalize
 ↓
Export
 ↓
Analyze Learning Results
```

Tujuan akhirnya adalah:

> Mengurangi pekerjaan administratif dan repetitif dosen dalam pemeriksaan akademik, meningkatkan konsistensi penilaian, menyediakan evidence yang jelas, dan memberikan insight terhadap hasil belajar mahasiswa — dengan tetap mempertahankan dosen sebagai pengambil keputusan akhir.

Mulai development dari **Phase 1 — Foundation** dan bangun aplikasi secara bertahap sampai seluruh MVP selesai.
