<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 'studenti';

    protected $fillable = [
        'ime',
        'prezime',
        'br_indexa',
        'datum_rodjenja',
        'telefon',
        'email',
        'godina_studija',
        'jmbg',
        'nivo_studija_id',
        'pol',
        'status'
    ];

    protected $casts = [
    ];

    public function nivoStudija()
    {
        return $this->belongsTo(NivoStudija::class, 'nivo_studija_id');
    }

    public function mobilnosti()
    {
        return $this->hasMany(Mobilnost::class, 'student_id');
    }

    public function predmeti()
    {
        return $this->belongsToMany(Predmet::class, 'student_predmet', 'student_id', 'predmet_id')->withPivot('grade');
    }

    public function fakulteti()
    {
        return $this->belongsToMany(Fakultet::class, 'student_fakultet', 'student_id', 'fakultet_id');
    }

    public function mappingRequests()
    {
        return $this->hasMany(MappingRequest::class, 'student_id');
    }

}
