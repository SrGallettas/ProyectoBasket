<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchandising extends Model
{
    use HasFactory;

    protected $table = 'merchandising';

    protected $fillable = [
        'nombre',
        'nombre_es',
        'nombre_val',
        'descripcion',
        'descripcion_es',
        'descripcion_val',
        'talla',
        'imagen_merchan',
        'precio',
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    public function getNombreAttribute(): string
    {
        return $this->getLocalizedText('nombre');
    }

    public function getDescripcionAttribute(): ?string
    {
        $value = $this->getLocalizedText('descripcion');

        return $value !== '' ? $value : null;
    }

    public function getLocalizedText(string $baseField, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $locale = $locale === 'val' ? 'val' : 'es';

        $localizedValue = $this->getRawOriginal("{$baseField}_{$locale}");
        $fallbackValue = $this->getRawOriginal("{$baseField}_es");
        $legacyValue = $this->getRawOriginal($baseField);

        return (string) ($localizedValue ?: $fallbackValue ?: $legacyValue ?: '');
    }

    public function getImagenMerchanUrlAttribute(): string
    {
        if (! $this->imagen_merchan || $this->imagen_merchan === 'club_14.png') {
            return asset('img/club_14.png');
        }

        return route('media.uploads', [
            'collection' => 'merchandising',
            'path' => ltrim($this->imagen_merchan, '/'),
        ]);
    }
}