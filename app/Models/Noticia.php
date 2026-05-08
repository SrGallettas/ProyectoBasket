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
        'subtitulo', 
        'slug',
        'contenido', 
        'imagen_noticia', 
        'autor', 
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    public function getImagenNoticiaUrlAttribute(): string
    {
        if (!$this->imagen_noticia || $this->imagen_noticia === 'club_14.png') {
            return asset('img/club_14.png');
        }

        return Storage::disk('public')->url('noticias/' . ltrim($this->imagen_noticia, '/'));
    }
}
