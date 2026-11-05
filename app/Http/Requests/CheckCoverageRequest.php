<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Coverage\DTOs\CheckCoverageInput;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Only the four fields of RF-6 get through, so anything else the browser sends is ignored (RF-16).
 */
final class CheckCoverageRequest extends FormRequest
{
    /**
     * Reads the JSON body alone, because coordinates in the URL would reach the access logs.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $data = $this->json()->all();
        $margin = $data['accuracy_meters'] ?? null;

        if (is_string($margin) && trim($margin) === '') {
            $data['accuracy_meters'] = null;
        }

        return $data;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'latitude' => ['bail', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['bail', 'required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['bail', 'nullable', 'numeric', $this->positive(...)],
            'address_confirmed' => ['required', 'accepted'],
        ];
    }

    public function toInput(): CheckCoverageInput
    {
        $margin = $this->validated('accuracy_meters');

        return new CheckCoverageInput(
            (float) $this->validated('latitude'),
            (float) $this->validated('longitude'),
            $margin === null ? null : (float) $margin,
        );
    }

    /**
     * Compares the value as a float, which is what reaches PrecisionMargin. The gt rule compares
     * the exact text, so 1e999 or 1e-400 would pass it and end in a failure instead of a rejection.
     */
    private function positive(string $attribute, mixed $value, Closure $fail): void
    {
        $number = (float) $value;

        if (! is_finite($number) || $number <= 0.0) {
            $fail("The {$attribute} field must be a finite number greater than zero.");
        }
    }
}
