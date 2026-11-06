<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Domain\Coverage\ValueObjects\TechnologyStatus;
use App\Domain\Coverage\ValueObjects\TechnologyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class SetZoneTechnologyStatusRequest extends FormRequest
{
    /** @return array<string, list<string|Enum>> */
    public function rules(): array
    {
        return [
            'technology' => ['required', 'string', Rule::enum(TechnologyType::class)],
            'status' => ['required', 'string', Rule::enum(TechnologyStatus::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'technology.*' => 'Elige una tecnología válida.',
            'status.*' => 'Elige un estado válido.',
        ];
    }

    public function technology(): TechnologyType
    {
        return TechnologyType::from($this->string('technology')->value());
    }

    public function status(): TechnologyStatus
    {
        return TechnologyStatus::from($this->string('status')->value());
    }
}
