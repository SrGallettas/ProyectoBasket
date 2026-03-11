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
        'slug',
        'carpeta',
        'descripcion',
        'tipo',
    ];

    public const TIPOS = [
        'partidos' => 'Partidos',
        'entrenamientos' => 'Entrenamientos',
        'equipo' => 'Equipo',
        'eventos' => 'Eventos',
        'otros' => 'Otros',
    ];

    public function imagenes()
    {
        return $this->hasMany(ImagenGaleria::class)->orderBy('id');
    }

    public function getPortadaAttribute(): ?ImagenGaleria
    {
        return $this->imagenes->firstWhere('es_principal', true) ?? $this->imagenes->first();
    }
}
