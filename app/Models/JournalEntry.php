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
        return $this->hasMany(JournalLine::class);
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
     *   Total debit and credit amounts
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
     * Menggunakan bcsub untuk mengurangin nilai dan menggunakan bccomp  untuk membandingan nilai dengan presisi tinggi
     */
    public function isBalanced(): bool
    {
        $totals = $this->totals();


        $difference = bcsub(
            (string) $totals['debit'],
            (string) $totals['credit'],
            2
        );


        return bccomp($difference, '0.00', 2) === 0;
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
