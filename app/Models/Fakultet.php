<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultet extends Model
{
    use HasFactory;
    protected $table = 'fakulteti';
    protected $fillable = ['naziv', 'email', 'telefon', 'web', 'univerzitet_id'];

    public function univerzitet()
    {
         return $this->belongsTo(Univerzitet::class)->withDefault();
    }

    public function predmeti()
    {
        return $this->hasMany(Predmet::class);
    }

    public function studenti()
    {
        return $this->belongsToMany(Student::class, 'student_fakultet', 'fakultet_id', 'student_id');
    }
}
