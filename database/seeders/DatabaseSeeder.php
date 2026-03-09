<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            EquipoSeeder::class,
            JugadorSeeder::class,
            NoticiaSeeder::class,
            MerchandisingSeeder::class,
            PartidoSeeder::class,
        ]);
    }
}
