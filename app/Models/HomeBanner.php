<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_link',
        'background_image_url',
        'background_color',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the full URL for the background image.
     */
    public function getBackgroundImageUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // Si ya es una URL completa, retornarla tal cual
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Si es una ruta relativa que empieza con /storage, convertirla a URL completa
        if (str_starts_with($value, '/storage/')) {
            return asset($value);
        }

        // Si es una ruta relativa sin /, agregar /storage/
        if (!str_starts_with($value, 'http') && !str_starts_with($value, '/')) {
            return asset('storage/' . $value);
        }

        return $value;
    }
}
