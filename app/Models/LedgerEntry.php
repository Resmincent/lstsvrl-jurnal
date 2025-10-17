<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'account_id',
        'journal_entry_id',
        'date',
        'debit',
        'credit',
        'running_balance'
    ];
    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Relasi
     */

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
