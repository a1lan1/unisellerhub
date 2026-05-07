<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadExportController extends Controller
{
    public function __invoke(Request $request, string $filename): BinaryFileResponse
    {
        $request->validate([
            'expires' => ['required', 'numeric'],
            'signature' => ['required', 'string'],
        ]);

        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid signature or expired link.');
        }

        if (! Storage::disk('local')->exists($filename)) {
            abort(404, 'File not found.');
        }

        return response()->download(
            Storage::disk('local')->path($filename)
        );
    }
}
