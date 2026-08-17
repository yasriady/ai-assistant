<?php

namespace App\Services\AI\Contracts;

interface AIProvider
{
    /**
     * Assess a student document against a rubric.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function assessDocument(array $payload): array;

    /**
     * Assess a single exam answer.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function assessAnswer(array $payload): array;

    /**
     * Analyze a question (difficulty, key concepts, expected answer hints).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function analyzeQuestion(array $payload): array;

    /**
     * Generate formative feedback for a student.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function generateFeedback(array $payload): array;

    /**
     * Generate an MVP RPS draft (CPMK + weekly topics).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function generateRpsDraft(array $payload): array;

    /**
     * Extract student NIM and name from a document cover page / header.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function extractStudentIdentity(array $payload): array;
}
