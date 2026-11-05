<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Service\LoginService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],

        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        app(LoginService::class)->attempt(
            $this->string('email')->toString(),
            $this->string('password')->toString(),
            $this->boolean('remember'),
            (string) $this->ip(),
        );
    }
}
