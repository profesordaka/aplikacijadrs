<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prepis extends Model
{
    protected $table = 'prepisi';
    
    protected $fillable = [
        'student_id',
        'fakultet_id',
        'datum',
        'napomena',
    ];

    protected $casts = [
        'datum' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fakultet()
    {
        return $this->belongsTo(Fakultet::class);
    }

    public function agreements()
    {
        return $this->hasMany(PrepisAgreement::class);
    }
}
