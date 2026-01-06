<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
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
            'label' => $this->label,
            'path' => $this->path,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'type' => $this->type,
            'category_id' => $this->category_id,
        ];
    }
}

