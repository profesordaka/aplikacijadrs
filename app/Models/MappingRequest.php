<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'professor_id',
        'fakultet_id',
        'student_id',
        'status',
        'datum_finalizacije',
        'napomena',
    ];

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fakultet()
    {
        return $this->belongsTo(Fakultet::class);
    }

    public function subjects()
    {
        return $this->hasMany(MappingRequestSubject::class);
    }
}
