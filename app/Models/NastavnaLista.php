<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NastavnaLista extends Model
{
    protected $table = 'nastavne_liste';

    protected $fillable = [
        'predmet_id',
        'fakultet_id',
        'studijska_godina',
        'link',
        'file_path',
        'file_name',
        'mime_type',
    ];

    public function predmet()
    {
        return $this->belongsTo(Predmet::class);
    }

    public function fakultet()
    {
        return $this->belongsTo(Fakultet::class);
    }
}
