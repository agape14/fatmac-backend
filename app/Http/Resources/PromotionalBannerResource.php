<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionalBannerResource extends JsonResource
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
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'image_left_url' => $this->image_left_url,
            'image_right_url' => $this->image_right_url,
            'background_color' => $this->background_color,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];
    }
}

