<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'number',
        'date',
        'memo',
        'is_posted',
        'posted_at'
    ];

    protected $casts = [
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
        'date' => 'date',
    ];

    /**
     *   Relasi
     */

    public function lines()
    {
        return $this->hasMany(JournalLine::class)->orderBy('line_number');
    }

    public function debitLines()
    {
        return $this->lines()->where('position', 'debit');
    }

    public function creditLines()
    {
        return $this->lines()->where('position', 'credit');
    }

    /**
     *   Scopes
     */

    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function scopeUnposted($query)
    {
        return $query->where('is_posted', false);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Total debit dan kredit
     */
    public function totals()
    {
        $debitTotal =  $this->debitLines()->sum('amount');
        $creditTotal =  $this->creditLines()->sum('amount');

        return [
            'debit' => $debitTotal,
            'credit' => $creditTotal,
        ];
    }


    /**
     * Untuk mengecek apakah jurnal sudah seimbang menggunakan round
     */
    public function isBalanced(): bool
    {
        $total = $this->totals();
        return round(((float)$total['debit'] - (float)$total['credit']), 2) === 0.0;
    }


    public function postJournalEntry()
    {
        if ($this->is_posted) {
            throw new \Exception("Journal entry already posted.");
        }

        if (!$this->isBalanced()) {
            throw new \Exception("Journal entry is not balanced.");
        }

        $this->is_posted = true;
        $this->posted_at = now();
        $this->save();
    }
}
