<?php

namespace App\Service;

use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function computedAccountBalance(int $accountId, ?string $startDate = null, ?string $endDate = null)
    {
        $row = DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->where('je.is_posted', 1)
            ->where('jl.account_id', $accountId)
            ->when($startDate, fn($q) => $q->where('je.date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('je.date', '<=', $endDate))
            ->selectRaw("
                 SUM(CASE WHEN jl.position = 'debit' THEN jl.amount ELSE 0 END) as total_debit,
                 SUM(CASE WHEN jl.position = 'credit' THEN jl.amount ELSE 0 END) as total_credit
                ")
            ->first();

        $totalDebit = $row->total_debit ?? 0;
        $totalCredit = $row->total_credit ?? 0;

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balance' => $totalDebit - $totalCredit,
            'position' => $totalDebit >= $totalCredit ? 'debit' : 'credit',
        ];
    }
}
