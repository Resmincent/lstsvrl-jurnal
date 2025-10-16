<?php

namespace App\Http\Requests\Accounts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Mengambil Id dari route parameter
        $accountId = $this->route('account')->id;

        return [
            'name' => ['required', 'string', 'max:50'],
            'code' => ['sometimes', 'required', 'string', 'max:12', 'unique:accounts,code,' . $accountId],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'balance_type' => ['required', 'in:debit,credit'],
            'is_active' => ['boolean']
        ];
    }
}
