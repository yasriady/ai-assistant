<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFileController extends Controller
{
    public function __invoke(Request $request, SubmissionFile $file): StreamedResponse
    {
        $file->loadMissing('submission.assessment');

        Gate::authorize('view', $file->submission);

        $disk = $file->disk ?: 'local';

        abort_unless(Storage::disk($disk)->exists($file->path), 404);

        return Storage::disk($disk)->response(
            $file->path,
            $file->original_name,
            [
                'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            ],
        );
    }
}
