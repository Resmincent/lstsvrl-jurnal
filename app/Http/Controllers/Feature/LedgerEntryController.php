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
    public function index(Request $req)
    {
        // filter date
        $start = $this->parseDateOrNull($req->get('start_date'));
        $end   = $this->parseDateOrNull($req->get('end_date'));


        // Scope periode reusable
        $byPeriod = fn($q) => $q
            ->when($start, fn($w) => $w->where('date', '>=', $start->toDateString()))
            ->when($end,   fn($w) => $w->where('date', '<=', $end->toDateString()));

        // ---- Helper saldo akun & tipe ----
        $normalDebitTypes = ['asset', 'expense'];

        $accountBalanceById = function (int $accountId) use ($byPeriod, $normalDebitTypes): string {
            $acc = Account::find($accountId);
            if (!$acc) return '0.00';
            $q  = $byPeriod(LedgerEntry::where('account_id', $acc->id));
            $dr = (string) $q->clone()->sum('debit');
            $cr = (string) $q->clone()->sum('credit');
            $normalDebit = in_array($acc->type, $normalDebitTypes, true);
            // saldo bernilai positif dalam sisi normal akun tsb.
            return $normalDebit ? bcsub($dr, $cr, 2) : bcsub($cr, $dr, 2);
        };

        $accountBalanceByCode = function (string $code) use ($accountBalanceById): string {
            $acc = Account::where('code', $code)->first();
            return $acc ? $accountBalanceById($acc->id) : '0.00';
        };

        $typeBalance = function (string $type) use ($byPeriod, $normalDebitTypes): string {
            $q = $byPeriod(
                LedgerEntry::whereHas('account', fn($a) => $a->where('type', $type))
            );
            $sumDr = (string) $q->clone()->sum('debit');
            $sumCr = (string) $q->clone()->sum('credit');
            $normalDebit = in_array($type, $normalDebitTypes, true);
            return $normalDebit ? bcsub($sumDr, $sumCr, 2) : bcsub($sumCr, $sumDr, 2);
        };

        // ---- Ambil saldo akun yang penting ----
        $cash      = $accountBalanceByCode('111'); // Kas
        $bank      = $accountBalanceByCode('112'); // Bank
        $ar        = $accountBalanceByCode('113'); // Piutang Usaha
        $equipment = $accountBalanceByCode('114'); // Peralatan


        $assets     = $typeBalance('asset');
        $liabilities = $typeBalance('liability');
        $equity     = $typeBalance('equity');
        $revenue    = $typeBalance('revenue');
        $expense    = $typeBalance('expense');
        $netIncome  = bcsub($revenue, $expense, 2);


        // 6) Label periode untuk UI
        $periodLabel = $this->formatPeriodLabel($start, $end);

        return Inertia::render('Dashboard', [
            'summary' => [
                // akun utama
                'cash'        => $cash,
                'bank'        => $bank,
                'accounts_receivable' => $ar,
                'equipment'   => $equipment,

                // agregat tipe
                'assets'      => $assets,
                'liabilities' => $liabilities,
                'equity'      => $equity,

                // kinerja
                'revenue'     => $revenue,
                'expense'     => $expense,
                'net_income'  => $netIncome,

                'period_label' => $periodLabel,
            ],
            'filters' => [
                'start_date' => $start?->toDateString(),
                'end_date'   => $end?->toDateString(),
            ],
        ]);
    }

    /** Parsing date atau */
    private function parseDateOrNull(?string $date): ?Carbon
    {
        if (!$date) return null;
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Buat label periode */
    private function formatPeriodLabel(?Carbon $start, ?Carbon $end): string
    {
        if ($start && $end) {
            return $start->isSameDay($end)
                ? $start->isoFormat('D MMMM YYYY')
                : $start->isoFormat('D MMM YYYY') . ' – ' . $end->isoFormat('D MMM YYYY');
        }
        if ($start && !$end)  return 'Sejak ' . $start->isoFormat('D MMM YYYY');
        if (!$start && $end)  return 'Hingga ' . $end->isoFormat('D MMM YYYY');
        return 'Semua periode';
    }
}
