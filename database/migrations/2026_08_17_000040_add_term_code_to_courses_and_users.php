<?php

use App\Support\AcademicTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('active_term_code', 5)->nullable()->after('avatar');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->string('term_code', 5)->default('20251')->after('name')->index();
        });

        $defaultTerm = AcademicTerm::current();

        DB::table('courses')->orderBy('id')->chunkById(100, function ($courses) use ($defaultTerm): void {
            foreach ($courses as $course) {
                $term = $this->guessTermCode(
                    (string) ($course->semester ?? ''),
                    (string) ($course->academic_year ?? ''),
                    $defaultTerm,
                );

                DB::table('courses')->where('id', $course->id)->update([
                    'term_code' => $term,
                    'semester' => AcademicTerm::semesterName($term, 'id'),
                    'academic_year' => AcademicTerm::academicYear($term),
                ]);
            }
        });

        DB::table('users')->whereNull('active_term_code')->update([
            'active_term_code' => $defaultTerm,
        ]);
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('term_code');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('active_term_code');
        });
    }

    private function guessTermCode(string $semester, string $academicYear, string $fallback): string
    {
        if (preg_match('/^(\d{4})\s*\/\s*(\d{4})$/', trim($academicYear), $matches)) {
            $startYear = (int) $matches[1];
            $normalized = mb_strtolower($semester);
            $isEven = str_contains($normalized, 'genap')
                || str_contains($normalized, 'even')
                || $normalized === '2';

            return $startYear.($isEven ? '2' : '1');
        }

        if (AcademicTerm::isValid($semester)) {
            return $semester;
        }

        return $fallback;
    }
};
