<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return [
        //     'data' => [
        //         'id' => $this->data->id,
        //         'nom' => $this->nom,
        //         'color' => $this->color,
        //     ],
        //     'links' => [
        //         'self' => 'link-value',
        //     ],

        // ];
        dd($request);

        return parent::toArray($request);

    }
}
