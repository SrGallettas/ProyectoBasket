<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jugador extends Model
{
    use HasFactory; // IMPORTANTE PARA EL FACTORY

    private const POSITION_LABELS = [
        'Base' => [
            'es' => 'Base',
            'val' => 'Base',
        ],
        'Escolta' => [
            'es' => 'Escolta',
            'val' => 'Escorta',
        ],
        'Alero' => [
            'es' => 'Alero',
            'val' => 'Aler',
        ],
        'Ala-Pívot' => [
            'es' => 'Ala-Pívot',
            'val' => 'Ala-pivot',
        ],
        'Pívot' => [
            'es' => 'Pívot',
            'val' => 'Pivot',
        ],
    ];

    protected $table = 'jugadores';
    
    protected $fillable = [
        'nombre',
        'apellido',
        'dorsal',
        'fecha_nacimiento',
        'posicion',
        'equipo_id',
        'imagen_jugador',
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    protected $appends = [
        'posicion_display',
    ];

    public function equipo() {
        return $this->belongsTo(Equipo::class);
    }

    public static function positionOptions(): array
    {
        return array_keys(self::POSITION_LABELS);
    }

    public static function displayPositionLabel(?string $position, ?string $locale = null): string
    {
        $position = (string) $position;
        $locale = $locale ?? app()->getLocale();

        if ($position === '') {
            return '';
        }

        return self::POSITION_LABELS[$position][$locale]
            ?? self::POSITION_LABELS[$position]['es']
            ?? $position;
    }

    public function getPosicionDisplayAttribute(): string
    {
        return self::displayPositionLabel($this->posicion);
    }

    public function getImagenJugadorUrlAttribute(): string
    {
        if (!$this->imagen_jugador || $this->imagen_jugador === 'club_14.png') {
            return asset('img/club_14.png');
        }

        return asset('storage/jugadores/' . ltrim(basename($this->imagen_jugador), '/'));
    }
}
