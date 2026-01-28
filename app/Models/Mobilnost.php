<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobilnost extends Model
{
    protected $table = 'mobilnosti';
    protected $fillable = ['datum_pocetka', 'datum_kraja', 'student_id', 'fakultet_id', 'is_locked', 'tip_mobilnosti'];

    protected static function booted()
    {
        static::deleting(function ($mobilnost) {
            \Illuminate\Support\Facades\Storage::deleteDirectory("mobility_docs/{$mobilnost->id}");
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fakultet()
    {
        return $this->belongsTo(Fakultet::class);
    }

    public function documents()
    {
        return $this->hasMany(MobilnostDokument::class);
    }

    public function learningAgreements()
    {
        return $this->hasMany(LearningAgreement::class);
    }
    
}
