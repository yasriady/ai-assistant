# Manual Penggunaan — AI Academic Assessment Platform

Panduan ini ditujukan untuk **dosen** dan **admin** yang menggunakan aplikasi sehari-hari.  
Prinsip utama aplikasi:

> AI membantu penilaian, tetapi **nilai final selalu ditentukan oleh dosen**.

---

## Daftar isi

1. [Masuk ke aplikasi](#1-masuk-ke-aplikasi)
2. [Bahasa antarmuka (ID | EN)](#2-bahasa-antarmuka-id--en)
3. [Dasbor](#3-dasbor)
4. [Mata kuliah](#4-mata-kuliah)
5. [Mahasiswa](#5-mahasiswa)
6. [Rubrik](#6-rubrik)
7. [Penilaian (assessment)](#7-penilaian-assessment)
8. [Unggah pengumpulan mahasiswa](#8-unggah-pengumpulan-mahasiswa)
9. [Review hasil AI & finalisasi](#9-review-hasil-ai--finalisasi)
10. [Bank soal & ujian](#10-bank-soal--ujian)
11. [Analitik & ekspor](#11-analitik--ekspor)
12. [Pengaturan AI (admin)](#12-pengaturan-ai-admin)
13. [Menghapus data](#13-menghapus-data)
14. [Status pengumpulan](#14-status-pengumpulan)
15. [Akun demo](#15-akun-demo)
16. [Tips & masalah umum](#16-tips--masalah-umum)

---

## 1. Masuk ke aplikasi

1. Buka URL aplikasi di browser.
2. Masuk dengan salah satu cara:
   - **Email & kata sandi** (login demo / akun yang sudah dibuat), atau
   - **Lanjutkan dengan Google** (jika OAuth sudah dikonfigurasi admin).
3. Setelah berhasil, Anda diarahkan ke **Dasbor**.
4. Untuk keluar, klik **Keluar** di pojok kanan atas.

---

## 2. Bahasa antarmuka (ID | EN)

Di halaman login dan header aplikasi terdapat tombol **ID | EN**.

| Tombol | Bahasa |
|--------|--------|
| **ID** | Bahasa Indonesia |
| **EN** | English |

Pilihan bahasa disimpan di sesi browser Anda.

---

## 3. Dasbor

Dasbor menampilkan ringkasan cepat:

- Jumlah mata kuliah
- Jumlah penilaian
- Jumlah mahasiswa
- Jumlah pengumpulan yang **menunggu review**
- Daftar penilaian & pengumpulan terbaru

Gunakan dasbor untuk melihat pekerjaan yang masih perlu ditindaklanjuti (review).

---

## 4. Mata kuliah

Menu: **Mata Kuliah**

Workspace dosen mengikuti **semester aktif** (contoh `20251`, `20252`) yang dipilih di header.

### Membuat mata kuliah

1. Pastikan semester aktif sudah benar (dropdown di header).
2. Klik **Mata kuliah baru**.
3. Isi minimal:
   - **Kode** (contoh: `IF2101`)
   - **Nama**
   - **Semester** (kode `YYYY1` / `YYYY2`)
4. Opsional: kelas, deskripsi.
5. Klik **Simpan**.

Data mata kuliah, penilaian, dan mahasiswa terdaftar di kelas hanya tampil untuk semester yang sedang dipilih.

### Mengubah / menghapus

- **Ubah**: klik tautan ubah pada baris data.
- **Hapus**: lihat bagian [Menghapus data](#13-menghapus-data).

---

## 5. Mahasiswa

Menu: **Mahasiswa**

### Tambah manual

1. Klik **Mahasiswa baru**.
2. Isi **NIM**, **Nama**, dan data lain jika ada.
3. Hubungkan ke mata kuliah jika diminta form.
4. Simpan.

### Impor CSV

1. Klik **Impor CSV**.
2. Siapkan file CSV berisi kolom seperti NIM, nama, email (sesuai petunjuk di halaman impor).
3. Unggah dan proses.

**Catatan:** NIM harus unik. NIM juga dipakai untuk mencocokkan file pengumpulan (lihat bagian unggah).

---

## 6. Rubrik

Menu: **Rubrik**

Rubrik adalah kriteria penilaian berbobot. Digunakan terutama untuk penilaian dokumen (tugas, laporan, jurnal, makalah, proyek).

### Membuat rubrik

1. Klik **Rubrik baru**.
2. Beri nama dan deskripsi.
3. Tambahkan **kriteria**, misalnya:
   - Pendahuluan
   - Landasan teori
   - Metodologi
   - Implementasi
   - Analisis
   - Kesimpulan
4. Untuk tiap kriteria isi bobot / nilai maksimum dan level performa (jika ada).
5. Simpan.

**Tips:** Buat rubrik yang jelas agar hasil AI lebih konsisten dan mudah direview.

---

## 7. Penilaian (assessment)

Menu: **Penilaian**

### Membuat penilaian dokumen (tugas / laporan / jurnal / makalah / proyek)

1. Klik **Penilaian baru**.
2. Pilih **mata kuliah**.
3. Pilih **jenis** (tugas, laporan praktikum, jurnal, dll.).
4. Hubungkan ke **rubrik**.
5. Isi judul, instruksi, nilai maksimum, tenggat (opsional).
6. Simpan.

### Membuat penilaian ujian (UTS / UAS / kuis / esai / campuran)

1. Buat penilaian dengan jenis ujian.
2. Buka **Penyusun ujian (Exam builder)**.
3. Lampirkan soal dari **Bank Soal**.
4. Susun urutan dan skor per soal.

---

## 8. Unggah pengumpulan mahasiswa

Dari detail penilaian, buka **Unggah pengumpulan**.

### Aturan nama file

Aplikasi mengenali mahasiswa dari **awal nama file (NIM)**.

Contoh yang benar:

```text
230101001_Andi_Pratama.pdf
230101002_Budi.pdf
230101003_Citra.docx
```

Format yang didukung (MVP):

- PDF
- DOCX
- TXT

### Alur setelah unggah

1. File disimpan secara privat (bukan folder publik).
2. Sistem mengekstrak teks (via antrean/queue).
3. AI menilai berdasarkan rubrik / soal.
4. Status berubah menjadi siap direview dosen.

**Jangan** mengandalkan hasil AI tanpa review.

---

## 9. Review hasil AI & finalisasi

Dari detail penilaian atau dasbor, buka halaman **Review**.

### Yang ditampilkan

- Informasi mahasiswa
- **Nilai AI**
- Penilaian per kriteria (skor, bukti/evidence, alasan, umpan balik)
- Umpan balik keseluruhan

### Tindakan dosen

| Aksi | Fungsi |
|------|--------|
| Terima nilai AI | Menyalin skor AI sebagai dasar review |
| Ubah skor / feedback | Menyesuaikan penilaian |
| Finalisasi | Mengunci **nilai final** |

Setelah difinalisasi, nilai dianggap resmi untuk mahasiswa tersebut.

Setiap perubahan skor penting dicatat dalam **audit log** (untuk jejak akuntabilitas).

---

## 10. Bank soal & ujian

Menu: **Bank Soal**

### Membuat bank soal

1. Buat bank baru dan kaitkan ke mata kuliah.
2. Tambah soal dengan jenis, misalnya:
   - Pilihan ganda
   - Benar / salah
   - Jawaban singkat
   - Esai
   - Hitungan
   - Studi kasus
   - Pemrograman / diagram (arsitektur siap dikembangkan)

### Penilaian otomatis vs AI

| Jenis soal | Cara dinilai |
|------------|--------------|
| Pilihan ganda | Deterministik (kunci jawaban) |
| Benar / salah | Deterministik |
| Esai / uraian / hitungan | AI + rubrik / kunci, lalu review dosen |

---

## 11. Analitik & ekspor

Pada penilaian (khususnya ujian), tersedia:

### Analitik

- Rata-rata, tertinggi, terendah
- Median, simpangan baku, tingkat kelulusan (jika tersedia)
- Performa per soal

### Ekspor

Unduh hasil penilaian (CSV / Excel / PDF sesuai opsi yang tersedia) berisi minimal:

- Mahasiswa / NIM
- Nilai AI
- Nilai final
- Status

---

## 12. Pengaturan AI (admin)

Menu: **Pengaturan AI** (hanya **Admin**)

Admin dapat mengatur:

- Provider AI (`null` demo, OpenAI, Gemini, Ollama)
- Model
- Temperature / max tokens
- Anggaran penggunaan (jika dikonfigurasi)

Untuk detail teknis API key, lihat [AI_CONFIGURATION.md](AI_CONFIGURATION.md).

Mode `null` berguna untuk demo tanpa API key (hasil simulasi, bukan penilaian produksi).

---

## 13. Menghapus data

Berlaku untuk hapus di daftar: Mata Kuliah, Mahasiswa, Rubrik, Penilaian, Bank Soal.

1. Klik **Hapus**.
2. Popup konfirmasi muncul.
3. Ketik kata **`delete`** (huruf besar/kecil bebas, contoh: `DELETE`, `Delete`).
4. Tombol hapus baru aktif.
5. Klik tombol hapus untuk mengonfirmasi.

Penghapusan bersifat permanen. Pastikan data tidak lagi dibutuhkan.

---

## 14. Status pengumpulan

| Status | Artinya |
|--------|---------|
| Diunggah | File sudah masuk |
| Diproses | Ekstraksi teks / AI sedang berjalan |
| Dinilai AI | Hasil AI siap direview |
| Direview | Dosen sudah mereview |
| Difinalisasi | Nilai final terkunci |
| Gagal | Proses gagal — cek pesan error / coba ulang |

---

## 15. Akun demo

Jika data demo sudah di-seed:

| Peran | Email | Kata sandi |
|-------|-------|------------|
| Admin | `admin@example.com` | `password` |
| Dosen | `demo@academic.test` | `password` |

Ganti kata sandi di lingkungan produksi.

---

## 16. Tips & masalah umum

### File tidak terhubung ke mahasiswa

- Pastikan nama file diawali **NIM** yang sudah terdaftar.
- Pastikan mahasiswa sudah terhubung ke mata kuliah terkait.

### AI tidak jalan / hasil aneh

- Cek provider & API key di `.env` / Pengaturan AI.
- Mode `null` hanya untuk demo.
- Pastikan **queue worker** berjalan (`php artisan queue:work`) agar ekstraksi & penilaian diproses.

### Nilai AI tidak sama dengan nilai final

- Itu perilaku yang diharapkan. Dosen wajib mereview sebelum finalisasi.

### Popup hapus tidak muncul di tengah layar

- Refresh keras browser (Ctrl+F5) agar aset CSS terbaru termuat.

### Alur kerja yang disarankan

```text
Login
 → Buat / pilih Mata Kuliah
 → Tambah Mahasiswa
 → Buat Rubrik (untuk dokumen) atau Bank Soal (untuk ujian)
 → Buat Penilaian
 → Unggah pengumpulan
 → Tunggu proses AI
 → Review & sesuaikan
 → Finalisasi
 → Ekspor / lihat analitik
```

---

## Dokumentasi terkait

| Dokumen | Isi |
|---------|-----|
| [INSTALLATION.md](INSTALLATION.md) | Instalasi teknis |
| [AI_CONFIGURATION.md](AI_CONFIGURATION.md) | Konfigurasi provider AI |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Arsitektur sistem |
| [SECURITY.md](SECURITY.md) | Keamanan & privasi data |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Deployment production |

---

*Versi manual mengikuti fitur MVP AI Academic Assessment Platform.*
