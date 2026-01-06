<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedCategoryResource extends JsonResource
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'icon' => $this->icon,
            'image_url' => $this->image_url,
            'background_color' => $this->background_color,
            'text_color' => $this->text_color,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
                'slug' => $this->category->slug ?? null,
            ],
        ];
    }
}

