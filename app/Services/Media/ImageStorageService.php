<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorageService
{
    public function store(UploadedFile $file, string $directory, string $prefix = ''): string
    {
        $filename = $this->generateFilename($file, $prefix);
        $file->storeAs($directory, $filename, 'public');

        return $filename;
    }

    public function storeRelativePath(UploadedFile $file, string $directory, string $prefix = ''): string
    {
        return trim($directory, '/').'/'.$this->store($file, $directory, $prefix);
    }

    public function deleteFromDirectory(?string $filename, string $directory, array $protectedFilenames = ['club_14.png']): void
    {
        if (! $filename || in_array($filename, $protectedFilenames, true)) {
            return;
        }

        Storage::disk('public')->delete(trim($directory, '/').'/'.ltrim(basename($filename), '/'));
    }

    public function deleteRelativePath(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete(ltrim($path, '/'));
    }

    public function deleteDirectoryIfEmpty(string $directory): void
    {
        $directory = trim($directory, '/');

        if (Storage::disk('public')->exists($directory) && empty(Storage::disk('public')->files($directory))) {
            Storage::disk('public')->deleteDirectory($directory);
        }
    }

    private function generateFilename(UploadedFile $file, string $prefix = ''): string
    {
        return $prefix.time().'_'.Str::lower(Str::random(8)).'.'.$file->getClientOriginalExtension();
    }
}
