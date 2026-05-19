<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipo extends Model
{
    use HasFactory; // IMPORTANTE PARA EL FACTORY

    protected $fillable = [
        'nombre', 
        'categoria', 'imagen_club', 'foto_plantilla', 'es_local', 'oculto'
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

    public function partidosComoLocal()
    {
        return $this->hasMany(Partido::class, 'equipo_local_id');
    }

    public function partidosComoVisitante()
    {
        return $this->hasMany(Partido::class, 'equipo_visitante_id');
    }

    public function getImagenClubUrlAttribute(): string
    {
        if (!$this->imagen_club || $this->imagen_club === 'club_14.png') {
            return asset('img/club_14.png');
        }

        return asset('storage/escudos/' . ltrim($this->imagen_club, '/'));
    }

    public function getFotoPlantillaUrlAttribute(): string
    {
        if (!$this->foto_plantilla) {
            return asset('img/equipo.png');
        }

        return asset('storage/equipos/plantillas/' . ltrim($this->foto_plantilla, '/'));
    }
}
