<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'position',
        'amount',
        'description',
        'line_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relasi
     */

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Scopes
     */

    public function scopeAccountId($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }
}
