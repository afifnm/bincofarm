<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\GradeHasil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PanenMelonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'greenhouse_id'  => ['required', 'exists:greenhouses,id'],
            'jenis_melon_id' => ['required', 'exists:jenis_melon,id'],
            'berat'          => ['required', 'numeric', 'min:0.01'],
            'grade'          => ['required', Rule::enum(GradeHasil::class)],
            'is_busuk'       => ['boolean'],
            'tanggal'        => ['required', 'date'],
        ];
    }
}
