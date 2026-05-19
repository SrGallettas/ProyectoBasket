<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class GaleriaImageController extends Controller
{
    public function show(string $path): Response
    {
        $normalizedPath = 'galerias/'.ltrim($path, '/');

        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        return response(Storage::disk('public')->get($normalizedPath), 200, [
            'Content-Type' => Storage::disk('public')->mimeType($normalizedPath) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
