<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransportRequest extends FormRequest
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
            'zone_id' => 'required|exists:zones,id',
            'pays' => 'required|exists:pays,id',
            'nom' => 'required|string',
            'temps' => 'required|string',
            'poids' => 'required|string',
            'montant' => 'required|integer',
        ];
    }
}
