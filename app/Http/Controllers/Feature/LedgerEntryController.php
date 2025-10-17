<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\LedgerEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LedgerEntryController extends Controller
{


    public function index()
    {
        // ---- Helper saldo akun & tipe (tanpa filter tanggal) ----
        $normalDebitTypes = ['asset', 'expense'];

        $accountBalanceById = function (int $accountId) use ($normalDebitTypes): string {
            $acc = Account::find($accountId);
            if (!$acc) return '0.00';

            $q  = LedgerEntry::where('account_id', $acc->id);
            $dr = (string) $q->clone()->sum('debit');
            $cr = (string) $q->clone()->sum('credit');

            $normalDebit = in_array($acc->type, $normalDebitTypes, true);
            // saldo positif di sisi normal akun
            return $normalDebit ? bcsub($dr, $cr, 2) : bcsub($cr, $dr, 2);
        };

        $accountBalanceByCode = function (string $code) use ($accountBalanceById): string {
            $acc = Account::where('code', $code)->first();
            return $acc ? $accountBalanceById($acc->id) : '0.00';
        };

        $typeBalance = function (string $type) use ($normalDebitTypes): string {
            $q = LedgerEntry::whereHas('account', fn($a) => $a->where('type', $type));
            $sumDr = (string) $q->clone()->sum('debit');
            $sumCr = (string) $q->clone()->sum('credit');
            $normalDebit = in_array($type, $normalDebitTypes, true);
            return $normalDebit ? bcsub($sumDr, $sumCr, 2) : bcsub($sumCr, $sumDr, 2);
        };

        // Saldo akun utama
        $cash      = $accountBalanceByCode('111'); // Kas
        $bank      = $accountBalanceByCode('112'); // Bank
        $ar        = $accountBalanceByCode('113'); // Piutang Usaha
        $equipment = $accountBalanceByCode('114'); // Peralatan

        // Agregat per tipe
        $assets      = $typeBalance('asset');
        $liabilities = $typeBalance('liability');
        $equity      = $typeBalance('equity');
        $revenue     = $typeBalance('revenue');
        $expense     = $typeBalance('expense');
        $netIncome   = bcsub($revenue, $expense, 2);

        return Inertia::render('Dashboard', [
            'summary' => [
                'cash'                 => $cash,
                'bank'                 => $bank,
                'accounts_receivable'  => $ar,
                'equipment'            => $equipment,
                'assets'               => $assets,
                'liabilities'          => $liabilities,
                'equity'               => $equity,
                'revenue'              => $revenue,
                'expense'              => $expense,
                'net_income'           => $netIncome,
            ],
        ]);
    }
}
