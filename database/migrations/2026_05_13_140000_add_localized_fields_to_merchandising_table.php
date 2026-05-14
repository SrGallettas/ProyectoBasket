<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchandising', function (Blueprint $table) {
            $table->string('nombre_es')->nullable()->after('nombre');
            $table->string('nombre_val')->nullable()->after('nombre_es');
            $table->text('descripcion_es')->nullable()->after('descripcion');
            $table->text('descripcion_val')->nullable()->after('descripcion_es');
        });

        DB::table('merchandising')->update([
            'nombre_es' => DB::raw('nombre'),
            'descripcion_es' => DB::raw('descripcion'),
        ]);
    }

    public function down(): void
    {
        Schema::table('merchandising', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_es',
                'nombre_val',
                'descripcion_es',
                'descripcion_val',
            ]);
        });
    }
};
