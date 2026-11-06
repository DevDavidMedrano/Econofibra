<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\Coverage\ValueObjects\TechnologyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class SavePlanRequest extends FormRequest
{
    /** @return array<string, list<string|Enum>> */
    public function rules(): array
    {
        return [
            'technology' => ['required', 'string', Rule::enum(TechnologyType::class)],
            'name' => ['required', 'string', 'max:100'],
            'speed' => ['required', 'integer'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'technology.*' => 'Elige una tecnología válida.',
            'name.*' => 'El nombre del plan debe tener de 1 a 100 caracteres.',
            'speed.*' => 'La velocidad debe ser un número entero de 1 a 100 000 Mbps.',
        ];
    }

    public function technology(): TechnologyType
    {
        return TechnologyType::from($this->string('technology')->value());
    }
}
