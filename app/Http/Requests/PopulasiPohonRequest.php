<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PopulasiPohonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'total_pohon' => ['required', 'integer', 'min:0'],
            'pohon_hidup' => ['required', 'integer', 'min:0'],
            'pohon_mati'  => ['required', 'integer', 'min:0'],
            'catatan'     => ['nullable', 'string', 'max:255'],
        ];
    }
}
