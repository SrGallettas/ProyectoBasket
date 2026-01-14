<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jugadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->integer('dorsal')->nullable();
            $table->string('imagen_jugador')->nullable();
        
            // AQUÍ VA LA RELACIÓN:
            // Crea una columna 'equipo_id' que se conecta con la tabla 'equipos'
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');
        
            $table->string('posicion');
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jugadores');
    }
};
