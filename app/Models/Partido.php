<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipo_local_id', 
        'equipo_visitante_id', 
        'fecha_hora', 
        'lugar',
        'categoria',
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    public function equipoLocal()
    {
        return $this->belongsTo(Equipo::class, 'equipo_local_id');
    }

    // Relación: El partido tiene un equipo visitante
    public function equipoVisitante()
    {
        return $this->belongsTo(Equipo::class, 'equipo_visitante_id');
    }

    public function estadisticas()
    {
        return $this->hasMany(PartidoEstadistica::class);
    }

    public function cuartos()
    {
        return $this->hasMany(PartidoCuarto::class);
    }
}
