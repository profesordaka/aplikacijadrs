<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilnostDokument extends Model
{
    protected $table = 'mobilnost_dokumenti';

    protected $guarded = [];

    public function mobilnost()
    {
        return $this->belongsTo(Mobilnost::class);
    }
}
