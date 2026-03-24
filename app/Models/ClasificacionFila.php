<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClasificacionFila extends Model
{
    use HasFactory;

    protected $fillable = [
        'clasificacion_id',
        'equipo_id',
        'jornada',
        'pj',
        'pg',
        'pp',
        'pf',
        'pc',
        'diff',
        'pts',
    ];

    public function clasificacion()
    {
        return $this->belongsTo(Clasificacion::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
