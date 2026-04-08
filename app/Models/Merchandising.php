<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;


class Merchandising extends Model
{
    use HasFactory;

    protected $table = 'merchandising';

    protected $fillable = [
        'nombre', 
        'descripcion', 
        'talla', 
        'imagen_merchan', 
        'precio', 
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    public function getImagenMerchanUrlAttribute(): string
    {
        if (!$this->imagen_merchan || $this->imagen_merchan === 'club_14.png') {
            return asset('img/club_14.png');
        }

        if ($this->imagen_merchan === 'basket.jpeg') {
            return asset('img/basket.jpeg');
        }

        return '/storage/merchandising/' . ltrim($this->imagen_merchan, '/');
    }
}
