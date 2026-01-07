<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Univerzitet extends Model
{
    use HasFactory;

    // Ime tabele u bazi
    protected $table = 'univerziteti';

    // Koja polja mogu da se masovno dodaju (mass assignment)
    protected $fillable = [
        'naziv',
        'drzava',
        'grad',
        'email',
    ];

    // Relacija sa fakultetima (ako planiraš kasnije)
    public function fakulteti()
    {
        return $this->hasMany(Fakultet::class);
    }
}
