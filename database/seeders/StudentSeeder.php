<?php

namespace Database\Seeders;

use App\Models\NivoStudija;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $osnovne = NivoStudija::where('naziv', 'Osnovne')->first();
        $master = NivoStudija::where('naziv', 'Master')->first();
        $fit = \App\Models\Fakultet::where('naziv', 'FIT')->first();

        $students = [
            [
                'ime' => 'Danilo',
                'prezime' => 'Mugosa',
                'br_indexa' => '52-23',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'danilomugosa@example.com',
                'godina_studija' => 3,
                'jmbg' => '1234567890121',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
            [
                'ime' => 'Emir',
                'prezime' => 'Muhovic',
                'br_indexa' => '09-23',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'emirmuhovic@example.com',
                'godina_studija' => 3,
                'jmbg' => '1234567890122',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
            [
                'ime' => 'Luka',
                'prezime' => 'Vujacic',
                'br_indexa' => '109-22',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'lukavujacic@example.com',
                'godina_studija' => 3,
                'jmbg' => '1234567890123',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
            [
                'ime' => 'Luka',
                'prezime' => 'Vojinovic',
                'br_indexa' => '43-23',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'lukavojinovic@example.com',
                'godina_studija' => 3,
                'jmbg' => '1234567890124',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
            [
                'ime' => 'Sanja',
                'prezime' => 'Radulovic',
                'br_indexa' => '32-16',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'sanjaradulovic@example.com',
                'godina_studija' => 3,
                'jmbg' => '1234567890125',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'zensko',
            ],
            [
                'ime' => 'Damjan',
                'prezime' => 'Latinovic',
                'br_indexa' => '100-23',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'damjanlatinovic@example.com',
                'godina_studija' => 3,
                'jmbg' => '1234567890126',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
            [
                'ime' => 'Vlado',
                'prezime' => 'Vojinovic',
                'br_indexa' => '22-22',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'vladovojinovic@example.com',
                'godina_studija' => 3,
                'jmbg' => '1234567890127',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
            [
                'ime' => 'Marko',
                'prezime' => 'Markovic',
                'br_indexa' => '22-20',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'markomarkovic@example.com',
                'godina_studija' => 2,
                'jmbg' => '1234567890128',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
            [
                'ime' => 'Janko',
                'prezime' => 'Jankovic',
                'br_indexa' => '22-19',
                'datum_rodjenja' => '2000-12-12',
                'telefon' => '061111111',
                'email' => 'jankojankovic@example.com',
                'godina_studija' => 2,
                'jmbg' => '1234567890129',
                'nivo_studija_id' => $osnovne->id,
                'pol' => 'musko',
            ],
        ];

        $count = 0;
        foreach ($students as $studentData) {
            $status = $count < 5 ? 'prepis' : 'mobilnost';
            $studentData['status'] = $status;
            
            $student = Student::create($studentData);
            if ($fit) {
                $student->fakulteti()->attach($fit->id);
            }
            $count++;
        }
    }
}
