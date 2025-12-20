<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\NotDisposableEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

final class StoreRegisterUserRequest extends FormRequest
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
            'prenom' => 'required|string|max:50',
            'nom' => 'required|string|max:50',
            'pays' => 'required|string|max:50',
            'contact' => ['required', 'phone:international'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new NotDisposableEmail()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }


    public function messages(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'fr') {
            return [
                'prenom.required' => 'Le prénom est obligatoire.',
                'prenom.string'   => 'Le prénom doit être une chaîne de caractères.',
                'prenom.max'      => 'Le prénom ne doit pas dépasser 50 caractères.',

                'nom.required' => 'Le nom est obligatoire.',
                'nom.string'   => 'Le nom doit être une chaîne de caractères.',
                'nom.max'      => 'Le nom ne doit pas dépasser 50 caractères.',

                'pays.required' => 'Le pays est obligatoire.',
                'pays.string'   => 'Le pays doit être valide.',
                'pays.max'      => 'Le pays ne doit pas dépasser 50 caractères.',

                'contact.required' => 'Le numéro de téléphone est obligatoire.',
                'contact.phone'    => 'Le numéro de téléphone doit être au format international (ex : +223xxxxxxxx).',

                'email.required' => 'L’adresse email est obligatoire.',
                'email.email'    => 'L’adresse email n’est pas valide.',
                'email.max'      => 'L’adresse email ne doit pas dépasser 255 caractères.',

                'password.required'  => 'Le mot de passe est obligatoire.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            ];
        }

        // EN (default)
        return [
            'prenom.required' => 'First name is required.',
            'prenom.string'   => 'First name must be a valid string.',
            'prenom.max'      => 'First name may not exceed 50 characters.',

            'nom.required' => 'Last name is required.',
            'nom.string'   => 'Last name must be a valid string.',
            'nom.max'      => 'Last name may not exceed 50 characters.',
            'pays.required' => 'Country is required.',
            'pays.string'   => 'Country must be valid.',
            'pays.max'      => 'Country may not exceed 50 characters.',

            'contact.required' => 'Phone number is required.',
            'contact.phone'    => 'Phone number must be in international format (e.g. +223xxxxxxxx).',

            'email.required' => 'Email address is required.',
            'email.email'    => 'Email address is not valid.',
            'email.max'      => 'Email address may not exceed 255 characters.',

            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
