<?php
// database/seeders/AccountSeeder.php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $rows = [
            // ASSETS (Debit)
            ['code' => '111', 'name' => 'Kas',                          'type' => 'asset',   'balance_type' => 'debit'],
            ['code' => '112', 'name' => 'Bank',                         'type' => 'asset',   'balance_type' => 'debit'],
            ['code' => '113', 'name' => 'Piutang Usaha',                'type' => 'asset',   'balance_type' => 'debit'],
            ['code' => '114', 'name' => 'Peralatan',                    'type' => 'asset',   'balance_type' => 'debit'],

            // LIABILITIES (Credit)
            ['code' => '211', 'name' => 'Utang Usaha',                  'type' => 'liability', 'balance_type' => 'credit'],
            ['code' => '212', 'name' => 'Utang Gaji',                   'type' => 'liability', 'balance_type' => 'credit'],

            // EQUITY (Credit)
            ['code' => '311', 'name' => 'Modal Disetor',                'type' => 'equity',  'balance_type' => 'credit'],
            ['code' => '312', 'name' => 'Laba Ditahan',                 'type' => 'equity',  'balance_type' => 'credit'],

            // REVENUE (Credit)
            ['code' => '411', 'name' => 'Pendapatan Bunga',             'type' => 'revenue', 'balance_type' => 'credit'],
            ['code' => '412', 'name' => 'Pendapatan Penjualan/Jasa',    'type' => 'revenue', 'balance_type' => 'credit'],

            // EXPENSE (Debit)
            ['code' => '511', 'name' => 'Beban Gaji',                   'type' => 'expense', 'balance_type' => 'debit'],
            ['code' => '512', 'name' => 'Beban Sewa',                   'type' => 'expense', 'balance_type' => 'debit'],
            ['code' => '513', 'name' => 'Beban Utilitas',               'type' => 'expense', 'balance_type' => 'debit'],
            ['code' => '514', 'name' => 'Beban Bunga',                  'type' => 'expense', 'balance_type' => 'debit'],
        ];

        // Tambahkan metadata
        $rows = array_map(function ($r) use ($now) {
            return [
                'code'         => strtoupper(trim($r['code'])),
                'name'         => trim($r['name']),
                'type'         => $r['type'],
                'balance_type' => $r['balance_type'],
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }, $rows);

        Account::upsert($rows, ['code'], ['name', 'type', 'balance_type', 'is_active', 'updated_at']);
    }
}
