<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenjualanMelonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'greenhouse_id'  => ['required', 'exists:greenhouses,id'],
            'nama_pembeli'   => ['required', 'string', 'max:150'],
            'jenis_melon_id' => ['required', 'exists:jenis_melon,id'],
            'jumlah_kg'      => ['required', 'numeric', 'min:0.01'],
            'harga_per_kg'   => ['required', 'numeric', 'min:0'],
            'tanggal'        => ['required', 'date'],
        ];
    }
}
