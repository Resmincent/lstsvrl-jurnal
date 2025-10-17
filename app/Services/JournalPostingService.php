<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;

class JournalPostingService
{
    public function post(JournalEntry $entry): void
    {
        // Cek jika jurnal sudah di posting
        if ($entry->is_posted) {
            throw new \RuntimeException('Journal already posted.');
        }

        // Cek jika jurnal tidak seimbang
        $entry->loadMissing(['lines.account']);
        if (! $entry->isBalanced()) {
            throw new \RuntimeException('Journal is not balanced.');
        }

        DB::transaction(function () use ($entry) {
            foreach ($entry->lines as $line) {
                $acc = $line->account;
                $debit = $line->position === 'debit' ? (string)$line->amount : '0.00';
                $credit = $line->position === 'credit' ? (string)$line->amount : '0.00';

                // Untuk mengambil saldo terakhir dari sebuah akun
                $prev = LedgerEntry::where('account_id', $acc->id)
                    ->orderByDesc('date')->orderByDesc('id')
                    ->value('running_balance') ?? '0.00';

                $normalDebit = in_array($acc->type, ['asset', 'expense']);
                $delta = $normalDebit
                    ? bcsub($debit, $credit, 2)
                    : bcsub($credit, $debit, 2);

                $newBalance = bcadd((string)$prev, (string)$delta, 2);

                LedgerEntry::create([
                    'account_id' => $acc->id,
                    'journal_entry_id' => $entry->id,
                    'date' => $entry->date,
                    'debit' => $debit,
                    'credit' => $credit,
                    'running_balance' => $newBalance,
                ]);
            }

            $entry->forceFill(['is_posted' => true, 'posted_at' => now()])->save();
        });
    }
}
