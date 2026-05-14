<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galerias', function (Blueprint $table) {
            $table->string('titulo_es')->nullable()->after('titulo');
            $table->string('titulo_val')->nullable()->after('titulo_es');
            $table->text('descripcion_es')->nullable()->after('descripcion');
            $table->text('descripcion_val')->nullable()->after('descripcion_es');
        });

        foreach (DB::table('galerias')->select(['id', 'titulo', 'descripcion'])->orderBy('id')->get() as $galeria) {
            DB::table('galerias')
                ->where('id', $galeria->id)
                ->update([
                    'titulo_es' => $galeria->titulo,
                    'descripcion_es' => $galeria->descripcion,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('galerias', function (Blueprint $table) {
            $table->dropColumn([
                'titulo_es',
                'titulo_val',
                'descripcion_es',
                'descripcion_val',
            ]);
        });
    }
};
