<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseTopic;
use App\Models\Cpmk;
use App\Models\User;
use Illuminate\Database\Seeder;

class MobileComputingRpsSeeder extends Seeder
{
    /**
     * Import RPS Mobile Computing INF46 (TA 2025/2026, Semester VI).
     * Source: wip/RPS-MobileComputing20252.pdf
     */
    public function run(): void
    {
        $lecturer = User::query()->where('email', 'demo@academic.test')->first();

        if (! $lecturer) {
            $this->command?->error('Demo lecturer (demo@academic.test) not found. Run DemoSeeder first.');

            return;
        }

        $course = Course::query()->updateOrCreate(
            [
                'user_id' => $lecturer->id,
                'code' => 'INF46',
                'term_code' => '20252',
            ],
            [
                'name' => 'Mobile Computing',
                'semester' => 'Genap',
                'academic_year' => '2025/2026',
                'class_name' => null,
                'description' => 'Mata kuliah Mobile Computing — RPS UAB FM-066 Rev 00 (TA 2025/2026). Pendekatan Hybrid: Case Based Learning dan Mini Project.',
                'midterm_week' => 8,
                'is_active' => true,
            ]
        );

        $cpmks = collect([
            ['code' => 'CPMK-1', 'description' => 'Mampu menjelaskan konsep dasar Mobile Computing, karakteristik perangkat bergerak, serta prinsip komunikasi nirkabel sebagai landasan sistem komputasi bergerak.', 'order_index' => 0],
            ['code' => 'CPMK-2', 'description' => 'Mampu menganalisis karakteristik sinyal, media transmisi, bandwidth, data rate, noise, dan berbagai impairment pada komunikasi nirkabel.', 'order_index' => 1],
            ['code' => 'CPMK-3', 'description' => 'Mampu membandingkan prinsip kerja dan karakteristik berbagai teknologi komunikasi nirkabel seperti WLAN, Bluetooth, RFID, Wireless Sensor Network, serta teknologi akses bergerak berdasarkan kebutuhan aplikasi.', 'order_index' => 2],
            ['code' => 'CPMK-4', 'description' => 'Mampu mengevaluasi permasalahan pada sistem Mobile Computing melalui pendekatan studi kasus serta mengusulkan solusi teknis yang sesuai berdasarkan konsep komunikasi nirkabel.', 'order_index' => 3],
            ['code' => 'CPMK-5', 'description' => 'Mampu menyusun dan mengomunikasikan hasil analisis atau mini project mengenai penerapan teknologi Mobile Computing secara mandiri maupun berkelompok dengan memperhatikan etika akademik.', 'order_index' => 4],
        ])->mapWithKeys(function (array $row) use ($course) {
            $cpmk = Cpmk::query()->updateOrCreate(
                ['course_id' => $course->id, 'code' => $row['code']],
                ['description' => $row['description'], 'order_index' => $row['order_index']]
            );

            return [$row['code'] => $cpmk];
        });

        $topics = [
            [
                'week_number' => 1,
                'title' => 'Pengantar Mobile Computing',
                'description' => 'Kontrak kuliah, karakteristik dan komponen Mobile Computing, ruang lingkup sistem komputasi bergerak. Metode: CBL kuliah interaktif dan diskusi kasus.',
                'cpmk_codes' => ['CPMK-1'],
            ],
            [
                'week_number' => 2,
                'title' => 'Sinyal Elektromagnetik',
                'description' => 'Sinyal elektromagnetik, spektrum elektromagnetik, propagasi sinyal sebagai media komunikasi nirkabel.',
                'cpmk_codes' => ['CPMK-1', 'CPMK-2'],
            ],
            [
                'week_number' => 3,
                'title' => 'Time Domain dan Frequency Domain',
                'description' => 'Representasi sinyal pada domain waktu dan domain frekuensi, Fourier Transform.',
                'cpmk_codes' => ['CPMK-2'],
            ],
            [
                'week_number' => 4,
                'title' => 'Data Rate, Bandwidth, dan Kapasitas Kanal',
                'description' => 'Hubungan bandwidth, data rate, dan kapasitas kanal komunikasi dalam sistem Mobile Computing.',
                'cpmk_codes' => ['CPMK-2'],
            ],
            [
                'week_number' => 5,
                'title' => 'Sinyal Analog, Digital, dan Transmission Impairments',
                'description' => 'Sinyal analog dan digital, gangguan transmisi nirkabel (wireless transmission impairments).',
                'cpmk_codes' => ['CPMK-2'],
            ],
            [
                'week_number' => 6,
                'title' => 'Noise dan Multipath Propagation',
                'description' => 'Pengaruh noise dan multipath propagation terhadap kualitas komunikasi nirkabel.',
                'cpmk_codes' => ['CPMK-2'],
            ],
            [
                'week_number' => 7,
                'title' => 'Fading pada Komunikasi Wireless',
                'description' => 'Jenis fading, dampak terhadap kualitas komunikasi wireless, dan solusi mitigasi.',
                'cpmk_codes' => ['CPMK-2'],
            ],
            [
                'week_number' => 8,
                'title' => 'UTS — Ujian Tengah Semester',
                'description' => 'Evaluasi kognitif pengetahuan materi minggu 1–7 (bobot 20%).',
                'cpmk_codes' => [],
            ],
            [
                'week_number' => 9,
                'title' => 'Error Compensation dan Error Correction',
                'description' => 'Teknik kompensasi kesalahan (Error Compensation) dan koreksi kesalahan (Error Correction) pada transmisi data.',
                'cpmk_codes' => ['CPMK-2'],
            ],
            [
                'week_number' => 10,
                'title' => 'Spread Spectrum',
                'description' => 'Konsep dan prinsip Spread Spectrum pada komunikasi modern.',
                'cpmk_codes' => ['CPMK-3'],
            ],
            [
                'week_number' => 11,
                'title' => 'FHSS dan DSSS',
                'description' => 'Perbandingan Frequency Hopping Spread Spectrum (FHSS) dan Direct Sequence Spread Spectrum (DSSS).',
                'cpmk_codes' => ['CPMK-3'],
            ],
            [
                'week_number' => 12,
                'title' => 'Code Division Multiple Access (CDMA)',
                'description' => 'Prinsip kerja dan penerapan CDMA pada komunikasi seluler.',
                'cpmk_codes' => ['CPMK-3'],
            ],
            [
                'week_number' => 13,
                'title' => 'Wireless Local Area Network (WLAN)',
                'description' => 'Prinsip kerja, arsitektur WLAN, dan standar IEEE 802.11.',
                'cpmk_codes' => ['CPMK-3'],
            ],
            [
                'week_number' => 14,
                'title' => 'Kategori WLAN dan Standar IEEE 802.11',
                'description' => 'Perbandingan kategori WLAN dan pemilihan standar sesuai kebutuhan aplikasi.',
                'cpmk_codes' => ['CPMK-3'],
            ],
            [
                'week_number' => 15,
                'title' => 'Studi Kasus Bluetooth, RFID, dan WSN',
                'description' => 'Evaluasi penerapan Bluetooth, RFID, dan Wireless Sensor Network (WSN) pada bidang kesehatan, industri, logistik, dan smart city.',
                'cpmk_codes' => ['CPMK-3', 'CPMK-4'],
            ],
            [
                'week_number' => 16,
                'title' => 'UAS — Ujian Akhir Semester',
                'description' => 'Evaluasi akhir semester seluruh materi RPS (bobot 30%).',
                'cpmk_codes' => ['CPMK-4', 'CPMK-5'],
            ],
        ];

        $keptTopicIds = [];

        foreach ($topics as $index => $row) {
            $topic = CourseTopic::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'week_number' => $row['week_number'],
                ],
                [
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'order_index' => $index,
                ]
            );

            $cpmkIds = collect($row['cpmk_codes'])
                ->map(fn (string $code) => $cpmks[$code]->id ?? null)
                ->filter()
                ->values()
                ->all();

            $topic->cpmks()->sync($cpmkIds);
            $keptTopicIds[] = $topic->id;
        }

        CourseTopic::query()
            ->where('course_id', $course->id)
            ->whereNotIn('id', $keptTopicIds)
            ->delete();

        $this->command?->info("RPS Mobile Computing imported for course {$course->code} (ID {$course->id}).");
        $this->command?->info('CPMK: '.$cpmks->count().', Topics: '.count($topics).', Midterm week: '.$course->midterm_week);
    }
}
