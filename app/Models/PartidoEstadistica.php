<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartidoEstadistica extends Model
{
    use HasFactory;

    protected $table = 'partido_estadisticas';

    protected $fillable = [
        'partido_id',
        'equipo_id',
        'fgm',
        'fga',
        't2m',
        't2a',
        't3m',
        't3a',
        'ftm',
        'fta',
        'reb_of',
        'reb_def',
        'ast',
        'blk',
        'tov',
        'stl',
        'pf',
        'tf',
        'puntos_totales',
    ];

    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function cuartos()
    {
        return $this->hasMany(PartidoCuarto::class, 'partido_id', 'partido_id')
            ->orderBy('periodo');
    }
}
