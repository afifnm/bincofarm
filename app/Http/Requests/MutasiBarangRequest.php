<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TipeMutasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MutasiBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barang_id'    => ['required', 'exists:barang,id'],
            'tanggal'      => ['required', 'date'],
            'tipe'         => ['required', Rule::enum(TipeMutasi::class)],
            'qty'          => ['required', 'numeric', 'min:0.01'],
            'harga_satuan' => ['nullable', 'numeric', 'min:0'],
            'referensi'    => ['nullable', 'string', 'max:100'],
            'keterangan'   => ['nullable', 'string', 'max:255'],
            'kas_id'       => ['nullable', 'exists:kas,id'],
            'kategori_id'  => ['nullable', 'exists:kategori_transaksi,id'],
        ];
    }
}
