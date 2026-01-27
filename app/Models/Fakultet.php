<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fakultet extends Model
{
    use HasFactory;

    protected $table = 'fakulteti';

    protected $fillable = [
        'naziv',
        'email',
        'telefon',
        'web',
        'univerzitet_id',
        'uputstvo_za_ocjene',
        'uputstvo_file',
        'uputstvo_preview',
        'image_url'
    ];

    /**
     * Preview - koristi direktno URL iz baze umjesto transformacije
     */
    public function getUputstvoPreviewAttribute()
    {
        // Ako već postoji preview URL u bazi, koristi ga
        if ($this->attributes['uputstvo_preview'] ?? null) {
            return $this->attributes['uputstvo_preview'];
        }

        // Fallback - ako nema u bazi, vrati null
        return null;
    }

    /**
     * URL za preuzimanje PDF-a - koristi /fetch/ za direktan link
     * Bez transformacija da bude originalni PDF
     */
    public function getUputstvoDownloadUrlAttribute()
    {
        if (!$this->uputstvo_file) {
            return null;
        }

        // Zaobiđi /raw/upload/ i koristi direktan URL za preuzimanje
        return $this->uputstvo_file;
    }

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
        return $this->belongsToMany(
            Student::class,
            'student_fakultet',
            'fakultet_id',
            'student_id'
        );
    }
}
