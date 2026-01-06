<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'vendor_id',
        'product_id', // Mantener para compatibilidad, pero ahora usamos order_items
        'total_price',
        'status',
        'voucher_image',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Asegurar que las fechas se guarden en hora local de Perú
        static::creating(function ($order) {
            $timezone = config('app.timezone', 'America/Lima');
            $now = Carbon::now($timezone);
            
            // Si no se ha establecido created_at, establecerlo en hora local de Perú
            if (!$order->created_at) {
                $order->created_at = $now;
            }
            if (!$order->updated_at) {
                $order->updated_at = $now;
            }
        });

        static::updating(function ($order) {
            $timezone = config('app.timezone', 'America/Lima');
            $order->updated_at = Carbon::now($timezone);
        });
    }

    /**
     * Get the customer that made the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the vendor that owns the product.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the product in the order (legacy - mantener para compatibilidad).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the items in the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
