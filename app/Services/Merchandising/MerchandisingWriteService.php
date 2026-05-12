<?php

namespace App\Services\Merchandising;

use App\Models\Merchandising;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;

class MerchandisingWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function create(array $data): Merchandising
    {
        $item = new Merchandising;
        $this->fillItem($item, $data);
        $item->save();

        return $item;
    }

    public function update(Merchandising $item, array $data): Merchandising
    {
        $this->fillItem($item, $data, true);
        $item->save();

        return $item->refresh();
    }

    public function delete(Merchandising $item): void
    {
        $this->imageStorage->deleteFromDirectory($item->imagen_merchan, 'merchandising');
        $item->delete();
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

    private function fillItem(Merchandising $item, array $data, bool $updating = false): void
    {
        $item->nombre = $data['nombre'];
        $item->descripcion = $data['descripcion'] ?? null;
        $item->precio = $data['precio'];
        $item->oculto = filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL);
        $item->talla = $this->normalizeSizeSelection($data['talla'] ?? null);

        if (($data['imagen_merchan'] ?? null) instanceof UploadedFile) {
            if ($updating) {
                $this->imageStorage->deleteFromDirectory($item->imagen_merchan, 'merchandising');
            }

            $item->imagen_merchan = $this->imageStorage->store($data['imagen_merchan'], 'merchandising');
        }
    }
}
