<?php

namespace App\Http\Requests\EntryJournals;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'number' => ['required', 'string', 'max:20', 'unique:journal_entries,number'],
            'date' => ['required', 'date_format:Y-m-d'],
            'memo' => ['required', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['distinct', 'required', 'exists:accounts,id'],
            'lines.*.position' => ['required', 'in:debit,credit'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.line_number' => ['nullable', 'integer', 'min:1', 'distinct']
        ];
    }

    public function messages(): array
    {
        return [
            'lines.min' => 'Minimal harus ada 2 baris jurnal (Debit & Kredit).',
            'lines.*.account_id.distinct' => 'Account pada baris jurnal tidak boleh duplikat.',
            'lines.*.line_number.distinct' => 'Urutan baris tidak boleh duplikat.',
        ];
    }
}
