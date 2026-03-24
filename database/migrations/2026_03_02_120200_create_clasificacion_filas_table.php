<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clasificacion_filas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clasificacion_id')->constrained('clasificaciones')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->unsignedInteger('jornada')->nullable();
            $table->unsignedInteger('pj')->default(0);
            $table->unsignedInteger('pg')->default(0);
            $table->unsignedInteger('pp')->default(0);
            $table->unsignedInteger('pf')->default(0);
            $table->unsignedInteger('pc')->default(0);
            $table->integer('diff')->default(0);
            $table->integer('pts')->default(0);
            $table->timestamps();

            $table->index(['clasificacion_id', 'jornada']);
            $table->unique(['clasificacion_id', 'jornada', 'equipo_id'], 'clasif_jornada_equipo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clasificacion_filas');
    }
};
