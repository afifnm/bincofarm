<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JenisMelonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('jenisMelon')?->id;

        return [
            'nama'      => ['required', 'string', 'max:100', Rule::unique('jenis_melon', 'nama')->ignore($id)->whereNull('deleted_at')],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }
}
