<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SetZonePriceRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer'],
            'price' => ['required', 'string'],
            'installation_cost' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'plan_id.*' => 'Elige un plan válido.',
            'price.*' => 'Escribe el precio.',
            'installation_cost.*' => 'Escribe el costo de instalación; usa 0 si no tiene costo.',
        ];
    }
}
