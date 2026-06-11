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
            'greenhouse_id'          => ['required', 'exists:greenhouses,id'],
            'nama_pembeli'           => ['required', 'string', 'max:150'],
            'tanggal'                => ['required', 'date'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.jenis_melon_id' => ['required', 'exists:jenis_melon,id'],
            'items.*.jumlah_kg'      => ['required', 'numeric', 'min:0.01'],
            'items.*.harga_per_kg'   => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                  => 'Minimal satu item melon harus diisi.',
            'items.min'                       => 'Minimal satu item melon harus diisi.',
            'items.*.jenis_melon_id.required' => 'Jenis melon wajib dipilih di setiap item.',
            'items.*.jenis_melon_id.exists'   => 'Jenis melon tidak valid.',
            'items.*.jumlah_kg.required'      => 'Jumlah kg wajib diisi di setiap item.',
            'items.*.jumlah_kg.min'           => 'Jumlah kg minimal 0,01.',
            'items.*.harga_per_kg.required'   => 'Harga per kg wajib diisi di setiap item.',
        ];
    }
}
