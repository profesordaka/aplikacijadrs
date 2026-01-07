<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fakultet extends Model
{
    use HasFactory;
    protected $table = 'fakulteti';
protected $fillable = [
    'naziv', 'email', 'telefon', 'web', 'univerzitet_id', 'uputstvo_za_ocjene', 'uputstvo_file'
];
    public function univerzitet()
    {
        return $this->belongsTo(Univerzitet::class);
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
