<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galerias', function (Blueprint $table) {
            $table->string('carpeta')->nullable()->after('slug');
        });

        $galerias = DB::table('galerias')->get();
        $carpetasUsadas = [];
        $basePath = public_path('galerias');

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        foreach ($galerias as $galeria) {
            $baseCarpeta = Str::slug($galeria->slug ?: $galeria->titulo) ?: 'galeria';
            $carpeta = $baseCarpeta;
            $contador = 1;

            while (in_array($carpeta, $carpetasUsadas, true) || DB::table('galerias')->where('carpeta', $carpeta)->where('id', '!=', $galeria->id)->exists()) {
                $carpeta = $baseCarpeta . '-' . $contador;
                $contador++;
            }

            $carpetasUsadas[] = $carpeta;
            $destinoGaleria = $basePath . DIRECTORY_SEPARATOR . $carpeta;

            if (!File::exists($destinoGaleria)) {
                File::makeDirectory($destinoGaleria, 0755, true);
            }

            DB::table('galerias')->where('id', $galeria->id)->update(['carpeta' => $carpeta]);

            $imagenes = DB::table('imagenes_galeria')->where('galeria_id', $galeria->id)->get();

            foreach ($imagenes as $imagen) {
                $archivoActual = $imagen->archivo;
                $nombreArchivo = basename($archivoActual);
                $origen = $basePath . DIRECTORY_SEPARATOR . $archivoActual;
                $destinoRelativo = $carpeta . '/' . $nombreArchivo;
                $destinoAbsoluto = $basePath . DIRECTORY_SEPARATOR . $destinoRelativo;

                if ($archivoActual !== $destinoRelativo && File::exists($origen) && !File::exists($destinoAbsoluto)) {
                    File::move($origen, $destinoAbsoluto);
                }

                DB::table('imagenes_galeria')->where('id', $imagen->id)->update([
                    'archivo' => $destinoRelativo,
                ]);
            }
        }

        DB::statement("ALTER TABLE galerias MODIFY carpeta VARCHAR(255) NOT NULL");

        Schema::table('galerias', function (Blueprint $table) {
            $table->unique('carpeta');
        });
    }

    public function down(): void
    {
        Schema::table('galerias', function (Blueprint $table) {
            $table->dropUnique(['carpeta']);
            $table->dropColumn('carpeta');
        });
    }
};
