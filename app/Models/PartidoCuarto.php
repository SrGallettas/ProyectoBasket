<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartidoCuarto extends Model
{
    use HasFactory;

    protected $table = 'partido_cuartos';

    protected $fillable = [
        'partido_id',
        'equipo_id',
        'periodo',
        'puntos',
    ];

    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
