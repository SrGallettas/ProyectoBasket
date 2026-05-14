<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Noticia extends Model
{
    use HasFactory;

    protected $table = 'noticias';

    protected $fillable = [
        'titulo',
        'titulo_es',
        'titulo_val',
        'subtitulo',
        'subtitulo_es',
        'subtitulo_val',
        'slug',
        'contenido',
        'contenido_es',
        'contenido_val',
        'imagen_noticia',
        'autor',
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    public function getTituloAttribute(): string
    {
        return $this->getLocalizedText('titulo');
    }

    public function getSubtituloAttribute(): string
    {
        return $this->getLocalizedText('subtitulo');
    }

    public function getContenidoAttribute(): string
    {
        return $this->getLocalizedText('contenido');
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

    public function getImagenNoticiaUrlAttribute(): string
    {
        if (!$this->imagen_noticia || $this->imagen_noticia === 'club_14.png') {
            return asset('img/club_14.png');
        }

        return Storage::disk('public')->url('noticias/' . ltrim($this->imagen_noticia, '/'));
    }
}
