<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessorRequestComment extends Model
{
    protected $fillable = [
        'mapping_request_id',
        'professor_id',
        'comment',
    ];

    public function mappingRequest()
    {
        return $this->belongsTo(MappingRequest::class);
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }
}
