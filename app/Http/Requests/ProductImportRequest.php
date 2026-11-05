<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProductImportRequest extends FormRequest
{
    public const int MAX_IMAGES = 20;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:'.self::MAX_IMAGES],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.required' => 'Ajoutez au moins une photo.',
            'images.array' => 'Les photos doivent être sous forme de liste.',
            'images.max' => 'Vous pouvez importer au maximum '.self::MAX_IMAGES.' photos à la fois.',
            'images.*.image' => 'Chaque fichier doit être une image.',
            'images.*.mimes' => 'Formats acceptés : jpg, png, webp.',
            'images.*.max' => 'Chaque photo doit faire 5 Mo au maximum.',
        ];
    }
}
