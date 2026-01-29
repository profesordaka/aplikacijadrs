<?php

namespace Database\Seeders;

use App\Models\Fakultet;
use App\Models\Univerzitet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FakultetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ucg = Univerzitet::where('naziv', 'Univerzitet Crna Gora')->first();
        $unimed = Univerzitet::where('naziv', 'Univerzitet Mediteran Crna Gora')->first();
        $malardalen = Univerzitet::where('naziv', 'Mälardalen University')->first();

        Fakultet::create([
            'naziv' => 'ETF',
            'email' => 'etf@ucg.cg',
            'telefon' => '033111222',
            'web' => 'etf.ucg.cg',

            'univerzitet_id' => $ucg->id ?? null,
        ]);

        Fakultet::create([
            'naziv' => 'FIT',
            'email' => 'fit@unimed.cg',
            'telefon' => '1111111',
            'web' => 'fit.unimed.cg',

            'univerzitet_id' => $unimed->id ?? null,
        ]);

     Fakultet::create([
        'naziv' => 'School of Innovation, Design and Engineering (IDT)',
        'email' => 'idt-international@mdu.se',
        'telefon' => '+4621101300',
        'web' => 'https://www.mdu.se/en/malardalen-university/about-mdu/organisation/school-of-innovation-design-and-engineering',

        'univerzitet_id' => $malardalen->id ?? null,
        
    ]);


    }
}
