<?php

namespace App\Services\Merchandising;

use App\Models\Merchandising;
use App\Services\Media\ImageStorageService;
use App\Services\Translation\ApertiumTranslationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MerchandisingWriteService
{
    private ?string $translationWarning = null;

    public function __construct(
        private readonly ImageStorageService $imageStorage,
        private readonly ApertiumTranslationService $translationService,
    ) {}

    public function create(array $data): Merchandising
    {
        $this->resetTranslationWarning();

        $item = new Merchandising;
        $newImagePath = null;

        try {
            $this->fillItem($item, $data, false, $newImagePath);
            $item->save();

            return $item;
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newImagePath, 'merchandising');

            throw $exception;
        }
    }

    public function update(Merchandising $item, array $data): Merchandising
    {
        $this->resetTranslationWarning();

        $newImagePath = null;
        $oldImagePath = $item->imagen_merchan;

        try {
            $this->fillItem($item, $data, true, $newImagePath);
            $item->save();

            if ($newImagePath !== null && $oldImagePath && $oldImagePath !== $newImagePath) {
                $this->imageStorage->deleteFromDirectory($oldImagePath, 'merchandising');
            }

            return $item->refresh();
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newImagePath, 'merchandising');

            throw $exception;
        }
    }

    public function delete(Merchandising $item): void
    {
        $imagePath = $item->imagen_merchan;
        $item->delete();
        $this->imageStorage->deleteFromDirectory($imagePath, 'merchandising');
    }

    public function consumeTranslationWarning(): ?string
    {
        $warning = $this->translationWarning;
        $this->translationWarning = null;

        return $warning;
    }

    public function normalizeSizeSelection($sizes): ?string
    {
        if (! is_array($sizes)) {
            return null;
        }

        $normalized = collect($sizes)
            ->map(fn ($size) => trim((string) $size))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->contains(fn ($size) => strtolower($size) === 'sin talla')) {
            return 'Sin talla';
        }

        return $normalized->isNotEmpty() ? $normalized->implode(',') : null;
    }

    private function fillItem(Merchandising $item, array $data, bool $updating = false, ?string &$newImagePath = null): void
    {
        $translations = $this->resolveTranslations($data);

        $item->nombre = $translations['nombre_es'] ?: $translations['nombre_val'];
        $item->nombre_es = $translations['nombre_es'];
        $item->nombre_val = $translations['nombre_val'];
        $item->descripcion = $translations['descripcion_es'] ?: $translations['descripcion_val'];
        $item->descripcion_es = $translations['descripcion_es'];
        $item->descripcion_val = $translations['descripcion_val'];
        $item->precio = $data['precio'];
        $item->oculto = filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL);
        $item->talla = $this->normalizeSizeSelection($data['talla'] ?? null);

        if (($data['imagen_merchan'] ?? null) instanceof UploadedFile) {
            $newImagePath = $this->imageStorage->store($data['imagen_merchan'], 'merchandising');
            $item->imagen_merchan = $newImagePath;
        }
    }

    private function resolveTranslations(array $data): array
    {
        $sourceLocale = ($data['idioma_origen'] ?? 'es') === 'val' ? 'val' : 'es';
        $name = (string) $data['nombre'];
        $description = (string) ($data['descripcion'] ?? '');

        try {
            if ($sourceLocale === 'val') {
                return [
                    'nombre_es' => $this->translationService->translate($name, [], 'cat_valencia|spa'),
                    'nombre_val' => $name,
                    'descripcion_es' => $this->translationService->translate($description, [], 'cat_valencia|spa'),
                    'descripcion_val' => $description !== '' ? $description : null,
                ];
            }

            return [
                'nombre_es' => $name,
                'nombre_val' => $this->translationService->translate($name),
                'descripcion_es' => $description !== '' ? $description : null,
                'descripcion_val' => $this->translationService->translate($description),
            ];
        } catch (RuntimeException $exception) {
            $this->reportTranslationFallback('merchandising', $sourceLocale, $exception);

            return $sourceLocale === 'val'
                ? [
                    'nombre_es' => null,
                    'nombre_val' => $name,
                    'descripcion_es' => null,
                    'descripcion_val' => $description !== '' ? $description : null,
                ]
                : [
                    'nombre_es' => $name,
                    'nombre_val' => null,
                    'descripcion_es' => $description !== '' ? $description : null,
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

    private function cleanupNewImage(?string $path, string $directory): void
    {
        if ($path !== null) {
            $this->imageStorage->deleteFromDirectory($path, $directory);
        }
    }
}
