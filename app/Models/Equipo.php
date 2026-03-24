<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipo extends Model
{
    use HasFactory; // IMPORTANTE PARA EL FACTORY

    protected $fillable = [
        'nombre', 
        'categoria', 'imagen_club', 'es_local', 'oculto'
    ];

    protected $casts = [
        'es_local' => 'boolean',
        'oculto' => 'boolean',
    ];

    public function jugadores() {
        return $this->hasMany(Jugador::class);
    }

    public function clasificaciones()
    {
        return $this->belongsToMany(Clasificacion::class, 'clasificacion_equipo')->withTimestamps();
    }
}
