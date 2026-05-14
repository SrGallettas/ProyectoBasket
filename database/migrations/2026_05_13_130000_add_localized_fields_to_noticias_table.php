<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->string('titulo_es')->nullable()->after('titulo');
            $table->string('titulo_val')->nullable()->after('titulo_es');
            $table->string('subtitulo_es')->nullable()->after('subtitulo');
            $table->string('subtitulo_val')->nullable()->after('subtitulo_es');
            $table->text('contenido_es')->nullable()->after('contenido');
            $table->text('contenido_val')->nullable()->after('contenido_es');
        });

        DB::table('noticias')->update([
            'titulo_es' => DB::raw('titulo'),
            'subtitulo_es' => DB::raw('subtitulo'),
            'contenido_es' => DB::raw('contenido'),
        ]);
    }

    public function down(): void
    {
        Schema::table('noticias', function (Blueprint $table) {
            $table->dropColumn([
                'titulo_es',
                'titulo_val',
                'subtitulo_es',
                'subtitulo_val',
                'contenido_es',
                'contenido_val',
            ]);
        });
    }
};
