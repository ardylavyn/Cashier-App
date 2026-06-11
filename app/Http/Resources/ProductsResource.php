<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'image' => $this->image ? asset(Storage::url($this->image)) : null,

            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,

            // abis whenloaded itu nama relasi yg ada di model
            'category' => new ProductCategoryResource($this->whenLoaded('productCategory')),
        ];
    }
}
