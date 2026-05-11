<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadExportController extends Controller
{
    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid signature or expired link.');
        }

        if (Storage::disk('reports')->exists($path)) {
            return response()->download(
                Storage::disk('reports')->path($path)
            );
        }

        if (Storage::disk('public')->exists($path)) {
            return response()->download(
                Storage::disk('public')->path($path)
            );
        }

        abort(404, 'File not found!!.');
    }
}
