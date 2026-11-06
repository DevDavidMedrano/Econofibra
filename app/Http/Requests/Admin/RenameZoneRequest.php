<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class RenameZoneRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.*' => 'El nombre de la zona debe tener de 1 a 100 caracteres.'];
    }
}
