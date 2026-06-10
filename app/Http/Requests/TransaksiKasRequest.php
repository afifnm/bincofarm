<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TipeTransaksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransaksiKasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kas_id'      => ['required', 'exists:kas,id'],
            'kategori_id' => ['nullable', 'exists:kategori_transaksi,id'],
            'tanggal'     => ['required', 'date'],
            'tipe'        => ['required', Rule::in(['masuk', 'keluar'])],
            'jumlah'      => ['required', 'numeric', 'min:0.01'],
            'keterangan'  => ['nullable', 'string', 'max:255'],
        ];
    }
}
