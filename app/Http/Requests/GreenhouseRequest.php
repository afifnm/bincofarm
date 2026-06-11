<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GreenhouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'      => ['required', 'string', 'max:100'],
            'lokasi'    => ['nullable', 'string', 'max:255'],
            'user_id'   => ['nullable', 'exists:users,id'],
            'kas_id'    => ['required', 'exists:kas,id'],
            'is_active' => ['boolean'],
        ];
    }
}
