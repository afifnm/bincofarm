<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\JenisKategori;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'      => ['required', 'string', 'max:100'],
            'jenis'     => ['required', Rule::enum(JenisKategori::class)],
            'is_active' => ['boolean'],
        ];
    }
}
