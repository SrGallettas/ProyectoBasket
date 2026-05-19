<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenGaleria extends Model
{
    use HasFactory;

    protected $table = 'imagenes_galeria';

    protected $fillable = [
        'galeria_id',
        'archivo',
        'es_principal',
    ];

    public function galeria()
    {
        return $this->belongsTo(Galeria::class);
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->archivo) {
            return asset('img/club_14.png');
        }

        return url('media/galerias/'.ltrim($this->archivo, '/'));
    }
}
