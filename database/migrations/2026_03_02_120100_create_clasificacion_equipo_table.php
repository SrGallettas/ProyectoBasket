<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clasificacion_equipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clasificacion_id')->constrained('clasificaciones')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['clasificacion_id', 'equipo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clasificacion_equipo');
    }
};
