<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'prenom' => 'required|string',
            'nom' => 'required|string',
            'email' => 'required|string|email',
            'contact' => 'required|string',
            'trans' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'transport_id' => 'required|exists:transports,id',
            'ville' => 'required|string',
            'adresse' => 'required|string',
            'postal' => 'nullable|string',
            'payment' => 'nullable|string',
            'commentaire' => 'required|string',
        ];
    }
}
