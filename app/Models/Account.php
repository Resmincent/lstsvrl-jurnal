<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'balance_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    /**
     *   Relasi
     */

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     *   Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }


    /**
     *   Untuk mengambil journal yang sudah di posting
     */
    public function postedJournalLines()
    {
        return $this->journalLines()->whereHas('entry', fn($q) => $q->where('is_posted', true));
    }
}
