<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imagenes_galeria', function (Blueprint $table) {
            $table->boolean('es_principal')->default(false)->after('archivo');
        });

        $galeriaIds = DB::table('imagenes_galeria')
            ->select('galeria_id')
            ->groupBy('galeria_id')
            ->pluck('galeria_id');

        foreach ($galeriaIds as $galeriaId) {
            $primeraImagenId = DB::table('imagenes_galeria')
                ->where('galeria_id', $galeriaId)
                ->orderBy('id')
                ->value('id');

            if ($primeraImagenId) {
                DB::table('imagenes_galeria')
                    ->where('id', $primeraImagenId)
                    ->update(['es_principal' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('imagenes_galeria', function (Blueprint $table) {
            $table->dropColumn('es_principal');
        });
    }
};
