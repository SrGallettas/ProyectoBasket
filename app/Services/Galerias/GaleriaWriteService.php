<?php

namespace App\Services\Galerias;

use App\Models\Galeria;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GaleriaWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function create(array $data): Galeria
    {
        return DB::transaction(function () use ($data) {
            $galeria = Galeria::create([
                'titulo' => $data['titulo'],
                'slug' => $this->generateUniqueSlug($data['titulo']),
                'carpeta' => $this->generateUniqueFolder($data['titulo']),
                'descripcion' => $data['descripcion'],
                'tipo' => $data['tipo'],
                'oculto' => filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL),
            ]);

            $this->storeImages(
                $galeria,
                $data['imagenes'] ?? [],
                null,
                isset($data['imagen_principal_nueva']) ? (int) $data['imagen_principal_nueva'] : 0
            );

            return $galeria->fresh('imagenes');
        });
    }

    public function update(Galeria $galeria, array $data): Galeria
    {
        return DB::transaction(function () use ($galeria, $data) {
            $galeria->update([
                'titulo' => $data['titulo'],
                'slug' => $this->generateUniqueSlug($data['titulo'], $galeria->id),
                'descripcion' => $data['descripcion'],
                'tipo' => $data['tipo'],
                'oculto' => filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL),
            ]);

            $idsAEliminar = collect($data['eliminar_imagenes'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->all();

            $this->ensureGalleryWillKeepImages($galeria, $idsAEliminar, $data['imagenes'] ?? []);
            $this->deleteImages($galeria, $idsAEliminar);

            $imagenPrincipalExistente = array_key_exists('imagen_principal_existente', $data) && $data['imagen_principal_existente'] !== null
                ? (int) $data['imagen_principal_existente']
                : null;
            $indicePrincipalNueva = array_key_exists('imagen_principal_nueva', $data) && $data['imagen_principal_nueva'] !== null
                ? (int) $data['imagen_principal_nueva']
                : null;

            $nuevasImagenes = $this->storeImages($galeria, $data['imagenes'] ?? [], $imagenPrincipalExistente, $indicePrincipalNueva);
            $this->syncPrincipalImage($galeria, $imagenPrincipalExistente, $nuevasImagenes, $indicePrincipalNueva);

            return $galeria->fresh('imagenes');
        });
    }

    public function delete(Galeria $galeria): void
    {
        DB::transaction(function () use ($galeria) {
            $galeria->loadMissing('imagenes');

            foreach ($galeria->imagenes as $imagen) {
                $this->imageStorage->deleteRelativePath('galerias/'.$imagen->archivo);
            }

            $this->imageStorage->deleteDirectoryIfEmpty($this->galleryDirectory($galeria));
            $galeria->delete();
        });
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

    private function deleteImages(Galeria $galeria, array $idsAEliminar): void
    {
        if ($idsAEliminar === []) {
            return;
        }

        $imagenes = $galeria->imagenes()->whereIn('id', $idsAEliminar)->get();

        foreach ($imagenes as $imagen) {
            $this->imageStorage->deleteRelativePath('galerias/'.$imagen->archivo);
            $imagen->delete();
        }
    }

    private function storeImages(
        Galeria $galeria,
        array $imagenes,
        ?int $imagenPrincipalExistente = null,
        ?int $indicePrincipalNueva = null
    ): array {
        $imagenesGuardadas = [];
        $hayImagenPrincipalActual = $galeria->imagenes()->where('es_principal', true)->exists();

        foreach ($imagenes as $imagen) {
            if (! $imagen instanceof UploadedFile) {
                continue;
            }

            $imagenesGuardadas[] = $galeria->imagenes()->create([
                'archivo' => $galeria->carpeta.'/'.$this->imageStorage->store($imagen, $this->galleryDirectory($galeria)),
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
}
