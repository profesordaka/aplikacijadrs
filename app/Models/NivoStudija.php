<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivoStudija extends Model
{
    use HasFactory;
    protected $table = 'nivo_studija';
    protected $fillable = ['naziv'];

}
