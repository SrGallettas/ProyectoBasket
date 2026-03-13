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
        Schema::create('partido_estadisticas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partido_id')->constrained('partidos')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();

            $table->unsignedSmallInteger('fgm')->nullable(); // Field goals made
            $table->unsignedSmallInteger('fga')->nullable(); // Field goals attempted
            $table->unsignedSmallInteger('t2m')->nullable();
            $table->unsignedSmallInteger('t2a')->nullable();
            $table->unsignedSmallInteger('t3m')->nullable();
            $table->unsignedSmallInteger('t3a')->nullable();
            $table->unsignedSmallInteger('ftm')->nullable();
            $table->unsignedSmallInteger('fta')->nullable();
            $table->unsignedSmallInteger('reb_of')->nullable();
            $table->unsignedSmallInteger('reb_def')->nullable();
            $table->unsignedSmallInteger('ast')->nullable();
            $table->unsignedSmallInteger('blk')->nullable();
            $table->unsignedSmallInteger('tov')->nullable();
            $table->unsignedSmallInteger('stl')->nullable();
            $table->unsignedSmallInteger('pf')->nullable();
            $table->unsignedSmallInteger('tf')->nullable();
            $table->unsignedSmallInteger('puntos_totales')->nullable();

            $table->timestamps();

            $table->unique(['partido_id', 'equipo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partido_estadisticas');
    }
};
