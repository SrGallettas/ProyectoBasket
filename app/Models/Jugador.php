<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Jugador extends Model
{
    use HasFactory; // IMPORTANTE PARA EL FACTORY

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

    public function equipo() {
        return $this->belongsTo(Equipo::class);
    }

    public function getImagenJugadorUrlAttribute(): string
    {
        if (!$this->imagen_jugador || $this->imagen_jugador === 'club_14.png') {
            return asset('img/club_14.png');
        }

        return Storage::disk('public')->url('jugadores/' . ltrim(basename($this->imagen_jugador), '/'));
    }
}
