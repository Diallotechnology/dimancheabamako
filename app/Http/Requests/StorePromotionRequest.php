<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePromotionRequest extends FormRequest
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
            'nom' => 'required|string',
            'reduction' => 'required|integer',
            'debut' => 'required|date',
            'fin' => 'required|date',
            'product_id' => 'required|array|exists:products,id',
            // 'categorie_id' => 'required_if:product_id,false|exists:categories,id',
        ];
    }
}
