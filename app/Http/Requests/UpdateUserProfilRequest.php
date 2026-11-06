<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

final class UpdateUserProfilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Un utilisateur ne peut modifier que son propre profil.
        return $this->user()?->is($this->route('user')) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Changer l'e-mail ou le mot de passe exige le mot de passe actuel (une session volée ne suffit pas).
        $sensitiveChange = $this->filled('password') || strcasecmp((string) $this->input('email'), (string) $this->user()?->email) !== 0;

        return [
            'current_password' => [Rule::requiredIf($sensitiveChange), 'nullable', 'current_password'],
            'name' => 'required|string',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
        ];
    }
}
