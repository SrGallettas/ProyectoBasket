<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clasificacion extends Model
{
    use HasFactory;

    protected $table = 'clasificaciones';

    protected $fillable = [
        'nombre',
        'categoria',
        'temporada',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'clasificacion_equipo')->withTimestamps();
    }

    public function filas()
    {
        return $this->hasMany(ClasificacionFila::class);
    }

    public function partidos()
    {
        return $this->hasMany(Partido::class);
    }
}
