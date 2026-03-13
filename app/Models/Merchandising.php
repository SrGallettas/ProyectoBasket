<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Merchandising extends Model
{
    use HasFactory;

    protected $table = 'merchandising';

    protected $fillable = [
        'nombre', 
        'descripcion', 
        'talla', 
        'imagen_merchan', 
        'precio', 
        'oculto',
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];
}
