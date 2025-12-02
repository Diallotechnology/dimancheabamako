<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreShippingRequest extends FormRequest
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
            'transport_id' => 'required|exists:transports,id',
            'zone_id' => 'required|exists:zones,id',
            'poids_id' => 'required|exists:poids,id',
            'temps' => 'required|string',
            'montant' => 'required|integer',
        ];
    }
}
