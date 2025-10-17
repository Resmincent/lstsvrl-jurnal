<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::query()
            ->when($request->filled('type'), fn($q) => $q->type($request->type))
            ->when(
                $request->filled('active'),
                fn($q) =>
                filter_var($request->active, FILTER_VALIDATE_BOOLEAN) ? $q->active() : $q->where('is_active', false)
            )

            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        // dd($accounts->toArray());

        return Inertia::render('account/Index', [
            'accounts' => $accounts,
            'filters' => $request->only(['type', 'active']),
        ]);
    }

    public function create()
    {
        return Inertia::render(
            'account/Create',
            [
                'account' => new Account(),
            ]
        );
    }

    public function store(StoreAccountRequest $request)
    {

        $data = $request->validated();

        // Mapped Data digunakan untuk memanipulasi data agar konsisten dari request sebelum disimpan ke database
        $mappedData = [
            'code' => strtoupper(trim($data['code'])),
            'name' => trim($data['name']),
            'type' => $data['type'],
            'balance_type' => $data['balance_type'] === 'debit' ? 'debit' : 'credit',
            'is_active' => $data['is_active'] ?? true,
        ];

        $account = Account::create($mappedData);

        return redirect()->route('accounts.index')->with('success', "Akun {$account->code} - {$account->name} berhasil dibuat.");
    }

    public function edit(Account $account)
    {
        return Inertia::render('account/Edit', [
            'account' => $account,
        ]);
    }

    public function update(UpdateAccountRequest $updateAccountRequest, Account $account)
    {

        $data = $updateAccountRequest->validated();

        $mappedData = [];

        if (array_key_exists('code', $data)) {
            $mappedData['code'] = strtoupper(trim($data['code']));
        }
        if (array_key_exists('name', $data)) {
            $mappedData['name'] = trim($data['name']);
        }
        if (array_key_exists('type', $data)) {
            $mappedData['type'] = $data['type'];
        }
        if (array_key_exists('balance_type', $data)) {
            $mappedData['balance_type'] = $data['balance_type'] === 'debit' ? 'debit' : 'credit';
        }
        if (array_key_exists('is_active', $data)) {
            $mappedData['is_active'] = (bool) $data['is_active'];
        }


        $account->update($mappedData);

        return redirect()->route('accounts.index')->with('success', "Akun {$account->code} - {$account->name} berhasil diupdate.");
    }


    public function destroy(Account $account)
    {
        // Cek apakah akun sudah pernah digunakan di jurnal
        $used = $account->journalLines()->exists();

        if ($used) {
            return redirect()->route('accounts.index')->with('error', "Akun {$account->code} - {$account->name} tidak dapat dihapus karena sedang digunakan.");
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', "Akun {$account->code} - {$account->name} berhasil dihapus.");
    }
}
