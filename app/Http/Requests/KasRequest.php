<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TipeKas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('kas');

        return [
            'kode'      => ['required', 'string', 'max:50', Rule::unique('kas', 'kode')->ignore($id)],
            'nama'      => ['required', 'string', 'max:100'],
            'tipe'      => ['required', Rule::enum(TipeKas::class)],
            'saldo_awal'=> ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
