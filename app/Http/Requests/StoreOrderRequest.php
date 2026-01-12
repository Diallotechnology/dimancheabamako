<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

final class StoreOrderRequest extends FormRequest
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
            'country_id' => 'required|exists:countries,id',
            'transport_id' => 'required|exists:transports,id',
            'livraison' => 'required|integer',
            'prenom' => 'required|string',
            'nom' => 'required|string',
            'email' => 'required|string|email',
            'contact' => 'required|string|phone:international',
            'ville' => 'required|string',
            'adresse' => 'required|string',
            'postal' => 'nullable|string',
            'commentaire' => 'nullable|string',
            'password' => 'nullable|string',
        ];
    }

    public function messages()
    {
        $locale = app()->getLocale();

        if ($locale === 'fr') {
            return [
                // FRANÇAIS
                'country_id.required' => 'Le pays est requis.',
                'country_id.exists' => 'Le pays sélectionné est invalide.',
                'transport_id.required' => 'Le transport est requis.',
                'transport_id.exists' => 'Le transport sélectionné est invalide.',
                'livraison.required' => 'Le moyen de livraison est requis.',
                // 'livraison.integer' => 'Le type de livraison doit être un nombre entier.',
                'prenom.required' => 'Le prénom est requis.',
                'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
                'nom.required' => 'Le nom est requis.',
                'nom.string' => 'Le nom doit être une chaîne de caractères.',
                'email.required' => 'L\'email est requis.',
                'email.string' => 'L\'email doit être une chaîne de caractères.',
                'email.email' => 'L\'email doit être valide.',
                'contact.required' => 'Le contact est requis.',
                'contact.string' => 'Le contact doit être une chaîne de caractères.',
                'contact.phone' => 'Le numéro de contact doit être valide au format international.',
                'ville.required' => 'La ville est requise.',
                'ville.string' => 'La ville doit être une chaîne de caractères.',
                'adresse.required' => 'L\'adresse est requise.',
                'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
                'postal.string' => 'Le code postal doit être une chaîne de caractères.',
                'commentaire.string' => 'Le commentaire doit être une chaîne de caractères.',
                'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            ];
        }

        // EN (default)
        return [
            // ENGLISH
            'country_id.required' => 'Country is required.',
            'country_id.exists' => 'The selected country is invalid.',
            'transport_id.required' => 'Transport is required.',
            'transport_id.exists' => 'The selected transport is invalid.',
            'livraison.required' => 'Delivery moyen is required.',
            // 'livraison.integer' => 'Delivery moyen must be an integer.',
            'prenom.required' => 'First name is required.',
            'prenom.string' => 'First name must be a string.',
            'nom.required' => 'Last name is required.',
            'nom.string' => 'Last name must be a string.',
            'email.required' => 'Email is required.',
            'email.string' => 'Email must be a string.',
            'email.email' => 'Email must be valid.',
            'contact.required' => 'Contact is required.',
            'contact.string' => 'Contact must be a string.',
            'contact.phone' => 'Contact number must be valid in international format.',
            'ville.required' => 'City is required.',
            'ville.string' => 'City must be a string.',
            'adresse.required' => 'Address is required.',
            'adresse.string' => 'Address must be a string.',
            'postal.string' => 'Postal code must be a string.',
            'commentaire.string' => 'Comment must be a string.',
            'password.string' => 'Password must be a string.',
        ];
    }
}
