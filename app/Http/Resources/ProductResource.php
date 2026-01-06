<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $discountedPrice = $this->discount_percentage 
            ? $this->price * (1 - $this->discount_percentage / 100)
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discount_percentage' => $this->discount_percentage ? (float) $this->discount_percentage : null,
            'discounted_price' => $discountedPrice ? (float) $discountedPrice : null,
            'stock' => $this->stock,
            'condition' => $this->condition,
            'category' => $this->category,
            'category_id' => $this->category_id,
            'category_data' => $this->categoryModel ? [
                'id' => $this->categoryModel->id,
                'name' => $this->categoryModel->name,
                'slug' => $this->categoryModel->slug,
                'icon' => $this->categoryModel->icon,
            ] : null,
            'image_url' => $this->image_url, // Mantener para compatibilidad
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->image_path),
                    'path' => $image->image_path,
                    'order' => $image->order,
                ];
            }),
            'is_new' => $this->is_new,
            'is_featured' => $this->is_featured,
            'vendor' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone_number' => $this->user->phone_number,
                'whatsapp_number' => $this->user->whatsapp_number,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
