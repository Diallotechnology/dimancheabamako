<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'categorie_id' => 'nullable|exists:categories,id',
            'reference' => 'required|unique:products,reference',
            'nom' => 'required|string',
            'color' => 'nullable|string',
            'taille' => 'nullable|string',
            'description' => 'nullable',
            'resume' => 'required',
            'favoris' => 'required|boolean',
            'status' => 'required|boolean',
            'poids' => 'required|string',
            'video' => 'nullable|file',
            'prix' => 'required|integer',
            'cover' => 'required|file',
            'image' => 'required|array',
            'stock' => 'required|integer',
        ];
    }
}
