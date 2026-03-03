<?php

namespace Database\Seeders;

use App\Models\Merchandising;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MerchandisingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Merchandising::factory()->count(5)->create();
    }
}
