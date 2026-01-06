<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Mail\CustomerCredentialsMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class OrderController extends Controller
{
    /**
     * Store a newly created order.
     * El cliente envía sus datos y la foto del voucher de Yape/Plin.
     * Puede ser con o sin autenticación.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|string', // JSON string con array de productos
            'voucher_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'payment_method' => 'required|in:yape,plin',
        ], [
            'voucher_image.required' => 'El comprobante de pago es obligatorio.',
            'voucher_image.image' => 'El comprobante debe ser una imagen.',
            'voucher_image.mimes' => 'El comprobante debe ser una imagen en formato: jpeg, png, jpg o gif.',
            'voucher_image.max' => 'El comprobante no debe ser mayor a 5 MB.',
            'customer_name.required' => 'El nombre completo es obligatorio.',
            'customer_name.max' => 'El nombre no debe exceder 255 caracteres.',
            'customer_email.required' => 'El email es obligatorio.',
            'customer_email.email' => 'Debe ser un email válido.',
            'customer_email.max' => 'El email no debe exceder 255 caracteres.',
            'customer_phone.required' => 'El teléfono es obligatorio.',
            'customer_phone.max' => 'El teléfono no debe exceder 20 caracteres.',
            'customer_address.required' => 'La dirección es obligatoria.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.in' => 'El método de pago debe ser Yape o Plin.',
            'products.required' => 'Debe haber al menos un producto en el pedido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Parsear productos desde JSON
        $productsData = json_decode($request->products, true);
        if (!is_array($productsData) || empty($productsData)) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => ['products' => ['Debe haber al menos un producto en el pedido.']],
            ], 422);
        }

        // Validar que todos los productos existan
        $productIds = array_column($productsData, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($productIds as $productId) {
            if (!$products->has($productId)) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => ['products' => ["El producto con ID {$productId} no existe."]],
                ], 422);
            }
        }

        // Intentar obtener el usuario autenticado
        // Como la ruta es pública, verificamos manualmente si hay token
        $customer = null;
        $autoPassword = null;

        // Verificar si hay un token Bearer en el header
        $token = $request->bearerToken();
        if ($token) {
            // Intentar autenticar con el token
            try {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken) {
                    $customer = $accessToken->tokenable;
                }
            } catch (\Exception $e) {
                // Si falla, continuar como usuario no autenticado
                \Log::debug('Error al verificar token: ' . $e->getMessage());
            }
        }

        // Si no hay token, intentar obtener usuario de la sesión (para SPAs con cookies)
        if (!$customer) {
            $customer = $request->user();
        }

        // Si el cliente está autenticado, usar directamente ese usuario
        if ($customer) {
            // El usuario ya está autenticado, usar sus datos
            // No validar correo porque ya está autenticado
            // Usar el email del usuario autenticado (ignorar el del request si es diferente)
            // Esto previene que se intente crear un pedido con otro email
            \Log::info('Usuario autenticado detectado: ' . $customer->email);
        } else {
            // Si el cliente no está autenticado, validar correo y crear o buscar usuario
            $existingUser = User::where('email', $request->customer_email)->first();

            if ($existingUser) {
                // Si el correo ya existe, el usuario debe iniciar sesión
                return response()->json([
                    'message' => 'Este correo electrónico ya está registrado',
                    'errors' => [
                        'customer_email' => [
                            'Este correo electrónico ya está registrado en nuestro sistema. Por favor, inicia sesión para continuar con tu pedido.'
                        ]
                    ],
                    'requires_login' => true, // Flag para indicar que necesita login
                ], 422);
            }

            // Crear nuevo usuario cliente con contraseña autogenerada
            $autoPassword = Str::random(12); // Generar contraseña aleatoria de 12 caracteres
            $customer = User::create([
                'name' => $request->customer_name,
                'email' => $request->customer_email,
                'password' => Hash::make($autoPassword),
                'role' => 'customer',
                'phone_number' => $request->customer_phone,
                'status' => 'approved',
                'must_change_password' => true, // Debe cambiar la contraseña al primer login
            ]);

            // Enviar correo con credenciales
            try {
                Mail::to($customer->email)->send(new CustomerCredentialsMail($customer, $autoPassword));
            } catch (\Exception $e) {
                // Log el error pero no fallar la creación del pedido
                \Log::error('Error al enviar correo de credenciales: ' . $e->getMessage());
            }
        }

        // Obtener el primer vendedor (asumimos que todos los productos son del mismo vendedor)
        // Si hay múltiples vendedores, se puede agrupar por vendedor en el futuro
        $firstProduct = $products->first();
        $vendorId = $firstProduct->user_id;

        // Calcular precio total de todos los productos
        $totalPrice = 0;
        $orderItems = [];

        foreach ($productsData as $itemData) {
            $product = $products->get($itemData['product_id']);
            $quantity = $itemData['quantity'] ?? 1;

            // Calcular precio unitario (considerando descuento)
            $unitPrice = $product->discount_percentage
                ? $product->price * (1 - $product->discount_percentage / 100)
                : $product->price;

            $itemTotal = $unitPrice * $quantity;
            $totalPrice += $itemTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal,
            ];
        }

        // Guardar la imagen del voucher
        $voucherPath = $request->file('voucher_image')->store('vouchers', 'public');

        // Crear el pedido
        // Si el usuario está autenticado, usar sus datos de la cuenta, pero permitir actualizar dirección
        $orderData = [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name ?? $request->customer_name,
            'customer_email' => $customer->email, // Siempre usar el email del usuario autenticado o creado
            'customer_phone' => $customer->phone_number ?? $request->customer_phone,
            'customer_address' => $request->customer_address, // La dirección siempre viene del formulario
            'vendor_id' => $vendorId,
            'product_id' => $firstProduct->id, // Mantener para compatibilidad
            'total_price' => $totalPrice,
            'status' => 'pending',
            'voucher_image' => $voucherPath,
            'payment_method' => $request->payment_method,
        ];

        $order = Order::create($orderData);

        // Crear los items del pedido
        foreach ($orderItems as $itemData) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
            ]);
        }

        // Cargar relaciones
        $order->load(['customer', 'vendor', 'items.product']);

        // Si se creó un nuevo usuario, retornar las credenciales para login automático
        $responseData = [
            'message' => 'Pedido creado exitosamente',
            'data' => new OrderResource($order),
        ];

        // Si se creó un nuevo usuario, incluir las credenciales para login automático
        if (isset($autoPassword)) {
            $responseData['user_credentials'] = [
                'email' => $customer->email,
                'password' => $autoPassword,
                'must_change_password' => true,
            ];
        }

        return response()->json($responseData, 201);
    }

    /**
     * Get orders for the authenticated vendor.
     * El vendedor ve los pedidos que le han hecho.
     * Los administradores ven todos los pedidos.
     */
    public function vendorOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        // Verificar que el usuario sea un vendor o admin
        if (!in_array($user->role, ['vendor', 'admin'])) {
            return response()->json([
                'message' => 'No autorizado. Solo vendedores y administradores pueden ver pedidos.',
            ], 403);
        }

        $query = Order::with(['customer', 'vendor', 'product']);

        // Si es admin, puede ver todos los pedidos
        // Si es vendor, solo ve sus pedidos
        if ($user->role === 'vendor') {
            $query->where('vendor_id', $user->id);
        }

        // Filtrar por estado si se proporciona
        if ($request->has('status') && $request->status && $request->status !== '') {
            $query->where('status', $request->status);
            \Log::info('Filtro status aplicado:', ['status' => $request->status]);
        }

        // Filtrar por rango de fechas si se proporciona
        // Estos filtros son obligatorios para asegurar que siempre se filtren por fecha
        // Convertir las fechas considerando la zona horaria de Perú (America/Lima = GMT-5)
        $timezone = config('app.timezone', 'America/Lima');

        // Asegurar que siempre se filtren por fecha (por defecto hoy si no se proporciona)
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($dateFrom) {
            // date_from: inicio del día en la zona horaria local (Perú), luego convertir a UTC
            // Ejemplo: 2026-01-05 00:00:00 en Perú = 2026-01-05 05:00:00 UTC
            try {
                $dateFromLocal = Carbon::createFromFormat('Y-m-d', $dateFrom, $timezone)
                    ->startOfDay();
                $dateFromUtc = $dateFromLocal->copy()->utc();
                \Log::info('Filtro date_from aplicado:', [
                    'original' => $dateFrom,
                    'local' => $dateFromLocal->format('Y-m-d H:i:s T'),
                    'utc' => $dateFromUtc->format('Y-m-d H:i:s T'),
                    'timezone' => $timezone
                ]);
                $query->where('created_at', '>=', $dateFromUtc);
            } catch (\Exception $e) {
                \Log::error('Error al convertir date_from:', ['error' => $e->getMessage(), 'date' => $dateFrom]);
                // Si hay error, usar la fecha directamente sin conversión
                $query->whereDate('created_at', '>=', $dateFrom);
            }
        }

        if ($dateTo) {
            // date_to: fin del día en la zona horaria local (Perú), luego convertir a UTC
            // Ejemplo: 2026-01-05 23:59:59 en Perú = 2026-01-06 04:59:59 UTC
            try {
                $dateToLocal = Carbon::createFromFormat('Y-m-d', $dateTo, $timezone)
                    ->endOfDay();
                $dateToUtc = $dateToLocal->copy()->utc();
                \Log::info('Filtro date_to aplicado:', [
                    'original' => $dateTo,
                    'local' => $dateToLocal->format('Y-m-d H:i:s T'),
                    'utc' => $dateToUtc->format('Y-m-d H:i:s T'),
                    'timezone' => $timezone
                ]);
                $query->where('created_at', '<=', $dateToUtc);
            } catch (\Exception $e) {
                \Log::error('Error al convertir date_to:', ['error' => $e->getMessage(), 'date' => $dateTo]);
                // Si hay error, usar la fecha directamente sin conversión
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }

        // Log de la query SQL para depuración
        \Log::info('Query SQL antes de ejecutar:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        \Log::info('Pedidos encontrados:', ['total' => $orders->total(), 'count' => $orders->count()]);

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get orders for the authenticated customer.
     * El cliente ve sus propios pedidos (por customer_id o customer_email).
     */
    public function customerOrders(Request $request): JsonResponse
    {
        $customer = $request->user();

        // Verificar que el usuario sea un cliente
        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'No autorizado. Solo los clientes pueden ver sus pedidos.',
            ], 403);
        }

        $query = Order::with(['customer', 'vendor', 'product', 'items.product'])
            ->where(function ($q) use ($customer) {
                // Buscar por customer_id si existe
                $q->where('customer_id', $customer->id)
                  // O por email si el pedido fue hecho sin autenticación
                  ->orWhere('customer_email', $customer->email);
            });

        // Filtrar por estado si se proporciona
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Update the status of an order.
     * El vendedor puede cambiar el estado de 'pending' a 'paid' o 'rejected'.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:paid,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::with(['customer', 'vendor', 'product', 'items.product'])->findOrFail($id);
        $vendor = $request->user();

        // Verificar que el usuario sea el vendor del pedido o un admin
        if ($vendor->role !== 'admin' && $order->vendor_id !== $vendor->id) {
            return response()->json([
                'message' => 'No autorizado. Solo puedes cambiar el estado de tus propios pedidos.',
            ], 403);
        }

        // Verificar que el pedido esté en estado 'pending'
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Solo se pueden cambiar pedidos en estado "pending".',
            ], 400);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Estado del pedido actualizado exitosamente',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Get the last address used by the authenticated customer.
     */
    public function getLastAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No autorizado',
            ], 401);
        }

        // Buscar el último pedido del cliente con dirección
        $lastOrder = Order::where('customer_id', $user->id)
            ->whereNotNull('customer_address')
            ->where('customer_address', '!=', '')
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'data' => [
                'address' => $lastOrder?->customer_address ?? null,
            ],
        ]);
    }
}
