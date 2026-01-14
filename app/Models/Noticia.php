<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Noticia extends Model
{
    use HasFactory;

    protected $table = 'noticias';

     protected $fillable = [
        'titulo', 
        'subtitulo', 
        'contenido', 
        'imagen_noticia', 
        'autor', 
    ];
}
