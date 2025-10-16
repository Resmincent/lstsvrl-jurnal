<?php

namespace App\Http\Requests\Accounts;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:12', 'unique:accounts,code'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'balance_type' => ['required', 'in:debit,credit'],
            'is_active' => ['boolean']
        ];
    }
}
