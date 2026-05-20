<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class UploadedMediaController extends Controller
{
    private const DIRECTORIES = [
        'noticias' => 'noticias',
        'merchandising' => 'merchandising',
        'jugadores' => 'jugadores',
        'escudos' => 'escudos',
        'plantillas' => 'equipos/plantillas',
    ];

    public function show(string $collection, string $path): Response
    {
        abort_unless(array_key_exists($collection, self::DIRECTORIES), 404);

        $normalizedPath = self::DIRECTORIES[$collection].'/'.ltrim($path, '/');

        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        return response(Storage::disk('public')->get($normalizedPath), 200, [
            'Content-Type' => Storage::disk('public')->mimeType($normalizedPath) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
