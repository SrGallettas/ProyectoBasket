<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartidoAccionAnotacion extends Model
{
    use HasFactory;

    protected $table = 'partido_acciones_anotacion';

    protected $fillable = [
        'partido_id',
        'equipo_id',
        'lado',
        'accion',
        'periodo',
        'user_id',
    ];

    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
