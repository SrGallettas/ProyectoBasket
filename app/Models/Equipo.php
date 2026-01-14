<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipo extends Model
{
    use HasFactory; // IMPORTANTE PARA EL FACTORY

    protected $fillable = [
        'nombre', 
        'categoria', 'imagen_club', 'numero_jugadores', 'es_local'
    ];

    public function jugadores() {
        return $this->hasMany(Jugador::class);
    }
}
