<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepisAgreement extends Model
{
    protected $table = 'prepis_agreements';
    
    protected $fillable = [
        'prepis_id',
        'fit_predmet_id',
        'strani_predmet_id',
        'napomena',
        'ocjena',
    ];

    public function prepis()
    {
        return $this->belongsTo(Prepis::class);
    }

    public function fitPredmet()
    {
        return $this->belongsTo(Predmet::class, 'fit_predmet_id');
    }

    public function straniPredmet()
    {
        return $this->belongsTo(Predmet::class, 'strani_predmet_id');
    }
}
