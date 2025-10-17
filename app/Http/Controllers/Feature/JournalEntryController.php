<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntryJournals\StoreJournalEntryRequest;
use App\Http\Requests\EntryJournals\UpdateJournalEntryRequest;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\JournalPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class JournalEntryController extends Controller
{

    public function index(Request $request)
    {

        $entries = JournalEntry::query()->with('lines.account')
            // Filter number, memo dan date
            ->when(
                $request->filled('q'),
                fn($q) =>
                $q->where(
                    fn($w) =>
                    $w->where('number', 'like', '%' . $request->q . '%')
                        ->orWhere('memo', 'like', '%' . $request->q . '%')
                )
            )->when(
                $request->filled('posted'),
                fn($q) =>
                filter_var($request->posted, FILTER_VALIDATE_BOOLEAN)
                    ? $q->posted() : $q->unposted()
            )->when(
                $request->filled('start_date') && $request->filled('end_date'),
                fn($q) => $q->betweenDates($request->start_date, $request->end_date)
            )->orderByDesc('date')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('journal-entry/Index', [
            'entries' => $entries,
            'filters' => $request->only(['q', 'posted', 'start_date', 'end_date']),

        ]);
    }

    public function create()
    {
        $accounts = Account::query()->active()->get(['id', 'name', 'code']);



        return Inertia::render('journal-entry/Create', [
            'entry' => new JournalEntry(),
            'accounts' => $accounts,
            'number' => JournalEntry::generateNumber(),
        ]);
    }

    public function store(StoreJournalEntryRequest $request)
    {
        $data = $request->validated();

        $entry = DB::transaction(function () use ($data) {
            $entry = JournalEntry::create([
                'number' => $data['number'] ?: JournalEntry::generateNumber(),
                'date' => $data['date'],
                'memo' => trim($data['memo'] ?? ''),
                'is_posted' => false,
                'posted_at' => null,
            ]);

            // Menyiapkan data jurnal line untuk di insert
            $entryLines = collect($data['lines'])->map(function ($line, $index) use ($entry) {
                return [
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'position' => $line['position'],
                    'amount' => $line['amount'],
                    'description' => trim($line['description'] ?? ''),
                    'line_number' => $line['line_number'] ?? ($index + 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            JournalLine::insert($entryLines);

            return $entry->load('lines.account');
        });

        if ($resp = $this->jurnalNotBalanced($entry)) {
            return $resp;
        }


        return redirect()->route('journal-entries.index')->with('success', "Jurnal entry {$entry->number} berhasil dibuat.");
    }

    public function edit(JournalEntry $entry)
    {
        if ($resp = $this->jurnalIsPosted($entry)) {
            return $resp;
        }

        $accounts = Account::query()->active()->get(['id', 'name']);

        $entry->load([
            'lines' => fn($q) => $q->orderBy('line_number'),
            'lines.account:id,name',
        ]);
        // dd($entry->toArray());


        return Inertia::render('journal-entry/Edit', [
            'entry' => $entry,
            'accounts' => $accounts,
        ]);
    }


    public function update(UpdateJournalEntryRequest $updateJournalEntryRequest, JournalEntry $entry)
    {
        if ($resp = $this->jurnalIsPosted($entry)) {
            return $resp;
        }
        $data = $updateJournalEntryRequest->validated();

        $entry = DB::transaction(function () use ($data, $entry) {

            $entry->update([
                'number' => $data['number'],
                'date' => $data['date'],
                'memo' => trim($data['memo'] ?? ''),
            ]);

            // Hapus baris jurnal yang lama
            $entry->lines()->delete();

            $entryLines = collect($data['lines'])->map(function ($line, $index) use ($entry) {
                return [
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'position' => $line['position'],
                    'amount' => $line['amount'],
                    'description' => trim($line['description'] ?? ''),
                    'line_number' => $line['line_number'] ?? ($index + 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            JournalLine::insert($entryLines);

            return $entry->load('lines.account');
        });

        if ($resp = $this->jurnalNotBalanced($entry)) {
            return $resp;
        }

        return redirect()->route('journal-entries.index')->with('success', "Jurnal entry {$entry->number} berhasil diupdate.");
    }


    public function show(JournalEntry $entry)
    {
        $entry->load([
            'lines' => fn($q) => $q->orderBy('line_number'),
            'lines.account:id,code,name',
        ]);

        return Inertia::render('journal-entry/Detail', [
            'entry'    => $entry,
            'totals'   => $entry->totals(),
            'balanced' => $entry->isBalanced(),
        ]);
    }


    public function destroy(JournalEntry $entry)
    {
        if ($resp = $this->jurnalIsPosted($entry)) {
            return $resp;
        }
        $entry->delete();

        return redirect()->route('journal-entries.index')->with('success', "Jurnal entry {$entry->number} berhasil dihapus.");
    }

    public function post(JournalEntry $entry, JournalPostingService $servicePosting)
    {
        try {
            $servicePosting->post($entry);
        } catch (\Throwable $e) {
            return redirect()->route('journal-entries.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('journal-entries.index')
            ->with('success', "Jurnal entry {$entry->number} berhasil di posting.");
    }


    /**
     * Pengecekan jika jurnal sudah di posting
     */
    private function jurnalIsPosted(JournalEntry $entry)
    {
        if ($entry->is_posted) {
            return redirect()->route('journal-entries.index')->with('error', "Jurnal entry {$entry->number} sudah di posting dan tidak bisa diubah.");
        }

        return null;
    }

    /**
     * Pengecekan jika jurnal tidak seimbang
     */
    private function jurnalNotBalanced(JournalEntry $entry)
    {
        if (! $entry->isBalanced()) {
            return redirect()->back()->withInput()->with('error', 'Total debit dan kredit harus sama');
        }

        return null;
    }
}
