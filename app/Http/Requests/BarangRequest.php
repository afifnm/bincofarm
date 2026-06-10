<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('barang');

        return [
            'kode'          => ['required', 'string', 'max:50', Rule::unique('barang', 'kode')->ignore($id)],
            'nama'          => ['required', 'string', 'max:150'],
            'satuan'        => ['required', 'string', 'max:30'],
            'harga_beli'    => ['required', 'numeric', 'min:0'],
            'harga_jual'    => ['required', 'numeric', 'min:0'],
            'stok_minimum'  => ['required', 'numeric', 'min:0'],
            'is_active'     => ['boolean'],
        ];
    }
}
