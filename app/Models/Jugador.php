<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'imagen_jugador',
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    public function equipo() {
        return $this->belongsTo(Equipo::class);
    }
}
