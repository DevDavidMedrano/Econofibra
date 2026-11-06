<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A destructive action: the server demands the box that the administrator ticks on the page.
 */
final class ConfirmedActionRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['confirmation' => ['accepted']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['confirmation.accepted' => 'Marca la casilla para confirmar esta acción.'];
    }
}
