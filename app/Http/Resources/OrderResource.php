<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Si hay items, usar esos; si no, usar el producto legacy
        $products = $this->items->isNotEmpty() 
            ? $this->items->map(function ($item) {
                return [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'total' => (float) $item->total_price,
                ];
            })
            : ($this->product ? [[
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => (float) $this->product->price,
                'quantity' => 1,
                'total' => (float) $this->total_price,
            ]] : []);

        return [
            'id' => $this->id,
            'customer' => [
                'id' => $this->customer?->id ?? null,
                'name' => $this->customer?->name ?? $this->customer_name,
                'email' => $this->customer?->email ?? $this->customer_email,
            ],
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'vendor' => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'email' => $this->vendor->email,
            ],
            'products' => $products, // Array de productos
            'product' => $this->product ? [ // Mantener para compatibilidad
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => (float) $this->product->price,
            ] : null,
            'total_price' => (float) $this->total_price,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'voucher_image' => $this->voucher_image ? asset('storage/' . $this->voucher_image) : null,
            // Las fechas ahora se guardan en hora local de Perú (America/Lima)
            // Devolverlas en formato ISO con la zona horaria correcta
            'created_at' => $this->created_at 
                ? $this->created_at->setTimezone('America/Lima')->toIso8601String() 
                : null,
            'updated_at' => $this->updated_at 
                ? $this->updated_at->setTimezone('America/Lima')->toIso8601String() 
                : null,
        ];
    }
}
