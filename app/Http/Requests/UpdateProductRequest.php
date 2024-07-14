<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'reference' => ['required', 'string', Rule::unique(Product::class)->ignore($this->product->id)],
            'nom' => 'required|string',
            'color' => 'nullable|string',
            'taille' => 'nullable|string',
            'description' => 'required',
            'resume' => 'required',
            'poids' => 'required|string',
            'favoris' => 'required|boolean',
            'image' => 'nullable|array',
            'prix' => 'required|integer',
            'stock' => 'required|integer',
        ];
    }
}
