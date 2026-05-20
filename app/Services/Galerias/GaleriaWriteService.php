<?php

namespace App\Services\Galerias;

use App\Models\Galeria;
use App\Models\ImagenGaleria;
use App\Services\Media\ImageStorageService;
use App\Services\Translation\ApertiumTranslationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class GaleriaWriteService
{
    private ?string $translationWarning = null;

    public function __construct(
        private readonly ImageStorageService $imageStorage,
        private readonly ApertiumTranslationService $translationService,
    ) {}

    public function create(array $data): Galeria
    {
        $this->resetTranslationWarning();
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($data, &$storedPaths) {
                $translations = $this->resolveTranslations($data);

                $galeria = Galeria::create([
                    'titulo' => $translations['titulo_es'] ?: $translations['titulo_val'],
                    'titulo_es' => $translations['titulo_es'],
                    'titulo_val' => $translations['titulo_val'],
                    'slug' => $this->generateUniqueSlug($translations['titulo_es'] ?: $translations['titulo_val'] ?: $data['titulo']),
                    'carpeta' => $this->generateUniqueFolder($translations['titulo_es'] ?: $translations['titulo_val'] ?: $data['titulo']),
                    'descripcion' => $translations['descripcion_es'] ?: $translations['descripcion_val'],
                    'descripcion_es' => $translations['descripcion_es'],
                    'descripcion_val' => $translations['descripcion_val'],
                    'tipo' => $data['tipo'],
                    'oculto' => filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL),
                ]);

                $this->storeImages(
                    $galeria,
                    $data['imagenes'] ?? [],
                    null,
                    isset($data['imagen_principal_nueva']) ? (int) $data['imagen_principal_nueva'] : 0,
                    $storedPaths,
                );

                return $galeria->fresh('imagenes');
            });
        } catch (Throwable $exception) {
            $this->cleanupStoredPaths($storedPaths);

            throw $exception;
        }
    }

    public function update(Galeria $galeria, array $data): Galeria
    {
        $this->resetTranslationWarning();
        $storedPaths = [];
        $pathsToDeleteAfterCommit = [];

        try {
            return DB::transaction(function () use ($galeria, $data, &$storedPaths, &$pathsToDeleteAfterCommit) {
                $translations = $this->resolveTranslations($data);

                $galeria->update([
                    'titulo' => $translations['titulo_es'] ?: $translations['titulo_val'],
                    'titulo_es' => $translations['titulo_es'],
                    'titulo_val' => $translations['titulo_val'],
                    'slug' => $this->generateUniqueSlug($translations['titulo_es'] ?: $translations['titulo_val'] ?: $data['titulo'], $galeria->id),
                    'descripcion' => $translations['descripcion_es'] ?: $translations['descripcion_val'],
                    'descripcion_es' => $translations['descripcion_es'],
                    'descripcion_val' => $translations['descripcion_val'],
                    'tipo' => $data['tipo'],
                    'oculto' => filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL),
                ]);

                $idsAEliminar = collect($data['eliminar_imagenes'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $this->ensureGalleryWillKeepImages($galeria, $idsAEliminar, $data['imagenes'] ?? []);
                $pathsToDeleteAfterCommit = $this->deleteImages($galeria, $idsAEliminar);

                $imagenPrincipalExistente = array_key_exists('imagen_principal_existente', $data) && $data['imagen_principal_existente'] !== null
                    ? (int) $data['imagen_principal_existente']
                    : null;
                $indicePrincipalNueva = array_key_exists('imagen_principal_nueva', $data) && $data['imagen_principal_nueva'] !== null
                    ? (int) $data['imagen_principal_nueva']
                    : null;

                $nuevasImagenes = $this->storeImages(
                    $galeria,
                    $data['imagenes'] ?? [],
                    $imagenPrincipalExistente,
                    $indicePrincipalNueva,
                    $storedPaths,
                );
                $this->syncPrincipalImage($galeria, $imagenPrincipalExistente, $nuevasImagenes, $indicePrincipalNueva);

                DB::afterCommit(function () use ($pathsToDeleteAfterCommit, $galeria) {
                    $this->cleanupDeletedPaths($pathsToDeleteAfterCommit, $galeria);
                });

                return $galeria->fresh('imagenes');
            });
        } catch (Throwable $exception) {
            $this->cleanupStoredPaths($storedPaths);

            throw $exception;
        }
    }

    public function delete(Galeria $galeria): void
    {
        $pathsToDeleteAfterCommit = [];
        $directory = $this->galleryDirectory($galeria);

        DB::transaction(function () use ($galeria, &$pathsToDeleteAfterCommit, $directory) {
            $galeria->loadMissing('imagenes');
            $pathsToDeleteAfterCommit = $galeria->imagenes
                ->map(fn (ImagenGaleria $imagen) => 'galerias/'.$imagen->archivo)
                ->all();

            $galeria->delete();

            DB::afterCommit(function () use ($pathsToDeleteAfterCommit, $directory) {
                foreach ($pathsToDeleteAfterCommit as $path) {
                    $this->imageStorage->deleteRelativePath($path);
                }

                $this->imageStorage->deleteDirectoryIfEmpty($directory);
            });
        });
    }

    public function consumeTranslationWarning(): ?string
    {
        $warning = $this->translationWarning;
        $this->translationWarning = null;

        return $warning;
    }

    private function ensureGalleryWillKeepImages(Galeria $galeria, array $idsAEliminar, array $imagenesNuevas): void
    {
        $imagenesActuales = $galeria->imagenes()->count();
        $imagenesNuevasCount = count(array_filter($imagenesNuevas));
        $imagenesRestantes = $imagenesActuales - count($idsAEliminar) + $imagenesNuevasCount;

        if ($imagenesRestantes <= 0) {
            throw ValidationException::withMessages([
                'imagenes' => 'La galeria debe tener al menos una imagen.',
            ]);
        }
    }

    private function deleteImages(Galeria $galeria, array $idsAEliminar): array
    {
        if ($idsAEliminar === []) {
            return [];
        }

        $imagenes = $galeria->imagenes()->whereIn('id', $idsAEliminar)->get();
        $paths = $imagenes
            ->map(fn (ImagenGaleria $imagen) => 'galerias/'.$imagen->archivo)
            ->all();

        foreach ($imagenes as $imagen) {
            $imagen->delete();
        }

        return $paths;
    }

    private function storeImages(
        Galeria $galeria,
        array $imagenes,
        ?int $imagenPrincipalExistente,
        ?int $indicePrincipalNueva,
        array &$storedPaths
    ): array {
        $imagenesGuardadas = [];
        $hayImagenPrincipalActual = $galeria->imagenes()->where('es_principal', true)->exists();

        foreach ($imagenes as $imagen) {
            if (! $imagen instanceof UploadedFile) {
                continue;
            }

            $filename = $this->imageStorage->store($imagen, $this->galleryDirectory($galeria));
            $relativePath = $galeria->carpeta.'/'.$filename;
            $storedPaths[] = 'galerias/'.$relativePath;

            $imagenesGuardadas[] = $galeria->imagenes()->create([
                'archivo' => $relativePath,
                'es_principal' => false,
            ]);
        }

        if (! $hayImagenPrincipalActual && isset($imagenesGuardadas[0])) {
            $imagenesGuardadas[0]->update(['es_principal' => true]);
        }

        if ($imagenPrincipalExistente === null && $indicePrincipalNueva !== null && isset($imagenesGuardadas[$indicePrincipalNueva])) {
            $this->markPrincipalImage($galeria, $imagenesGuardadas[$indicePrincipalNueva]->id);
        }

        return $imagenesGuardadas;
    }

    private function syncPrincipalImage(
        Galeria $galeria,
        ?int $imagenPrincipalExistente,
        array $nuevasImagenes,
        ?int $indicePrincipalNueva
    ): void {
        if ($imagenPrincipalExistente !== null) {
            $imagenExiste = $galeria->imagenes()->where('id', $imagenPrincipalExistente)->exists();

            if ($imagenExiste) {
                $this->markPrincipalImage($galeria, $imagenPrincipalExistente);

                return;
            }
        }

        if ($indicePrincipalNueva !== null && isset($nuevasImagenes[$indicePrincipalNueva])) {
            $this->markPrincipalImage($galeria, $nuevasImagenes[$indicePrincipalNueva]->id);

            return;
        }

        $imagenActual = $galeria->imagenes()->where('es_principal', true)->first();

        if (! $imagenActual) {
            $primeraImagen = $galeria->imagenes()->orderBy('id')->first();

            if ($primeraImagen) {
                $this->markPrincipalImage($galeria, $primeraImagen->id);
            }
        }
    }

    private function markPrincipalImage(Galeria $galeria, int $imagenId): void
    {
        $galeria->imagenes()->update(['es_principal' => false]);
        $galeria->imagenes()->where('id', $imagenId)->update(['es_principal' => true]);
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slugBase = Str::slug($title);
        $slugBase = $slugBase !== '' ? $slugBase : 'galeria';
        $slug = $slugBase;
        $counter = 1;

        while (
            Galeria::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $slugBase.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function generateUniqueFolder(string $title): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'galeria';
        $folder = $base;
        $counter = 1;

        while (Galeria::where('carpeta', $folder)->exists()) {
            $folder = $base.'-'.$counter;
            $counter++;
        }

        return $folder;
    }

    private function galleryDirectory(Galeria $galeria): string
    {
        return 'galerias/'.$galeria->carpeta;
    }

    private function resolveTranslations(array $data): array
    {
        $sourceLocale = ($data['idioma_origen'] ?? 'es') === 'val' ? 'val' : 'es';
        $title = (string) $data['titulo'];
        $description = (string) ($data['descripcion'] ?? '');

        try {
            if ($sourceLocale === 'val') {
                return [
                    'titulo_es' => $this->translationService->translate($title, [], 'cat_valencia|spa'),
                    'titulo_val' => $title,
                    'descripcion_es' => $this->translationService->translate($description, [], 'cat_valencia|spa'),
                    'descripcion_val' => $description,
                ];
            }

            return [
                'titulo_es' => $title,
                'titulo_val' => $this->translationService->translate($title),
                'descripcion_es' => $description,
                'descripcion_val' => $this->translationService->translate($description),
            ];
        } catch (RuntimeException $exception) {
            $this->reportTranslationFallback('gallery', $sourceLocale, $exception);

            return $sourceLocale === 'val'
                ? [
                    'titulo_es' => null,
                    'titulo_val' => $title,
                    'descripcion_es' => null,
                    'descripcion_val' => $description,
                ]
                : [
                    'titulo_es' => $title,
                    'titulo_val' => null,
                    'descripcion_es' => $description,
                    'descripcion_val' => null,
                ];
        }
    }

    private function resetTranslationWarning(): void
    {
        $this->translationWarning = null;
    }

    private function reportTranslationFallback(string $resource, string $sourceLocale, RuntimeException $exception): void
    {
        $this->translationWarning = __('admin.flash.translation_partial');

        Log::warning('Automatic translation failed while saving content.', [
            'resource' => $resource,
            'source_locale' => $sourceLocale,
            'error' => $exception->getMessage(),
        ]);
    }

    private function cleanupStoredPaths(array $storedPaths): void
    {
        foreach ($storedPaths as $path) {
            $this->imageStorage->deleteRelativePath($path);
        }
    }

    private function cleanupDeletedPaths(array $pathsToDeleteAfterCommit, Galeria $galeria): void
    {
        foreach ($pathsToDeleteAfterCommit as $path) {
            $this->imageStorage->deleteRelativePath($path);
        }

        $this->imageStorage->deleteDirectoryIfEmpty($this->galleryDirectory($galeria));
    }
}
