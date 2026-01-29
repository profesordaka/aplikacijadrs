<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UniverzitetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $universities = [
            ['naziv' => 'Univerzitet Crna Gora'],
            ['naziv' => 'Univerzitet Mediteran Crna Gora'],
            ['naziv' => 'Mälardalen University'],
        ];

        foreach ($universities as $university) {
            \App\Models\Univerzitet::updateOrCreate(
                ['naziv' => $university['naziv']],
                $university
            );
        }
    }
}
