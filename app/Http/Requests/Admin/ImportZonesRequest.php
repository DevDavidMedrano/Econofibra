<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class ImportZonesRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['kml' => ['required', 'file', 'max:'.$this->maxKilobytes()]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $tooLarge = sprintf('El archivo pesa más de %g MB.', $this->maxKilobytes() / 1024);

        return [
            'kml.required' => 'Elige un archivo KML.',
            'kml.file' => 'Elige un archivo KML.',
            'kml.max' => $tooLarge,
            'kml.uploaded' => "No se pudo recibir el archivo. {$tooLarge} Revisa que no lo supere.",
        ];
    }

    private function maxKilobytes(): int
    {
        return (int) config('admin.kml_max_kb');
    }
}
