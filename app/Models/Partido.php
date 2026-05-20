<?php

namespace App\Models;

use App\Services\Translation\ProtectedTermsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => ProtectedTermsService::flushCache());
        static::deleted(fn () => ProtectedTermsService::flushCache());
    }

    protected $fillable = [
        'equipo_local_id',
        'equipo_visitante_id',
        'fecha_hora',
        'lugar',
        'categoria',
        'tipo_partido',
        'clasificacion_id',
        'jornada',
        'oculto',
        'oculto_en_clasificacion',
    ];

    protected $casts = [
        'oculto' => 'boolean',
        'oculto_en_clasificacion' => 'boolean',
        'jornada' => 'integer',
    ];

    public function equipoLocal()
    {
        return $this->belongsTo(Equipo::class, 'equipo_local_id');
    }

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

    public function clasificacion()
    {
        return $this->belongsTo(Clasificacion::class);
    }
}
