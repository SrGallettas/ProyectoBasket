<?php

namespace App\Models;

use App\Services\Translation\ProtectedTermsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => ProtectedTermsService::flushCache());
        static::deleted(fn () => ProtectedTermsService::flushCache());
    }

    protected $fillable = [
        'nombre',
        'categoria',
        'imagen_club',
        'foto_plantilla',
        'es_local',
        'oculto',
    ];

    protected $casts = [
        'es_local' => 'boolean',
        'oculto' => 'boolean',
    ];

    public function jugadores()
    {
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
        if (! $this->imagen_club || $this->imagen_club === 'club_14.png') {
            return asset('img/club_14.png');
        }

        return route('media.uploads', [
            'collection' => 'escudos',
            'path' => ltrim($this->imagen_club, '/'),
        ]);
    }

    public function getFotoPlantillaUrlAttribute(): string
    {
        if (! $this->foto_plantilla) {
            return asset('img/equipo.png');
        }

        return route('media.uploads', [
            'collection' => 'plantillas',
            'path' => ltrim($this->foto_plantilla, '/'),
        ]);
    }
}
