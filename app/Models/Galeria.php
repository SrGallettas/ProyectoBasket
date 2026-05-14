<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeria extends Model
{
    use HasFactory;

    protected $table = 'galerias';

    protected $fillable = [
        'titulo',
        'titulo_es',
        'titulo_val',
        'slug',
        'carpeta',
        'descripcion',
        'descripcion_es',
        'descripcion_val',
        'tipo',
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    public const TIPOS = [
        'partidos' => 'Partidos',
        'entrenamientos' => 'Entrenamientos',
        'equipo' => 'Equipo',
        'eventos' => 'Eventos',
        'otros' => 'Otros',
    ];

    public function getTituloAttribute(): string
    {
        return $this->getLocalizedText('titulo');
    }

    public function getDescripcionAttribute(): string
    {
        return $this->getLocalizedText('descripcion');
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

    public static function translatedTypes(string $translationPrefix): array
    {
        $types = [];

        foreach (array_keys(self::TIPOS) as $type) {
            $types[$type] = __("$translationPrefix.$type");
        }

        return $types;
    }

    public static function translatedTypeLabel(?string $type, string $translationPrefix): string
    {
        if (! $type || ! array_key_exists($type, self::TIPOS)) {
            return (string) ucfirst((string) $type);
        }

        return __("$translationPrefix.$type");
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenGaleria::class)->orderBy('id');
    }

    public function portadaImagen()
    {
        return $this->hasOne(ImagenGaleria::class)
            ->orderByDesc('es_principal')
            ->orderBy('id');
    }

    public function getPortadaAttribute(): ?ImagenGaleria
    {
        if ($this->relationLoaded('portadaImagen')) {
            return $this->getRelation('portadaImagen');
        }

        return $this->imagenes->firstWhere('es_principal', true) ?? $this->imagenes->first();
    }
}
