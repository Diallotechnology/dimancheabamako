<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProductRequest extends FormRequest
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
            'color' => 'required|string',
            'taille' => 'required|string',
            'description' => 'nullable',
            'resume' => 'required',
            'poids' => 'required|string',
            'favoris' => 'required|boolean',
            'is_preorder' => 'required|boolean',
            'image' => 'nullable|array',
            'prix' => 'required|integer',
            'stock' => 'required|integer',

            'video' => 'nullable',
            'cover' => 'nullable',
            'image' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'categorie_id.exists' => 'La catégorie sélectionnée est invalide.',

            'reference.required' => 'La référence est obligatoire.',
            'reference.unique' => 'Cette référence existe déjà.',

            'nom.required' => 'Le nom du produit est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',

            'color.string' => 'La couleur doit être une chaîne valide.',
            'taille.string' => 'La taille doit être une chaîne valide.',

            'resume.required' => 'Le résumé est obligatoire.',

            'favoris.required' => 'Le champ favoris est obligatoire.',
            'favoris.boolean' => 'Le champ favoris doit être vrai ou faux.',

            'is_preorder.required' => 'Le champ précommande est obligatoire.',
            'is_preorder.boolean' => 'Le champ précommande doit être vrai ou faux.',

            'poids.required' => 'Le poids est obligatoire.',
            'poids.string' => 'Le poids doit être une valeur valide.',

            'prix.required' => 'Le prix est obligatoire.',
            'prix.integer' => 'Le prix doit être un nombre entier.',

            'stock.required' => 'Le stock est obligatoire.',
            'stock.integer' => 'Le stock doit être un nombre entier.',
        ];
    }
}
