<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferKasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kas_asal_id'   => ['required', 'exists:kas,id'],
            'kas_tujuan_id' => ['required', 'exists:kas,id', 'different:kas_asal_id'],
            'tanggal'       => ['required', 'date'],
            'jumlah'        => ['required', 'numeric', 'min:0.01'],
            'keterangan'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
