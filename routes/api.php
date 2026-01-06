<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación (públicas)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register-vendor', [AuthController::class, 'registerVendor']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas de usuario autenticado
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);
});

// Rutas públicas de categorías
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Rutas públicas de menú
Route::get('/menu-items', [\App\Http\Controllers\Api\MenuItemController::class, 'index']);

// Rutas públicas de banners y categorías destacadas
Route::get('/promotional-banners', [\App\Http\Controllers\Api\PromotionalBannerController::class, 'index']);
Route::get('/featured-categories', [\App\Http\Controllers\Api\FeaturedCategoryController::class, 'index']);

// Rutas públicas del CMS del home
Route::get('/home-cms/top-banner', [\App\Http\Controllers\Api\HomeCmsController::class, 'getTopBanner']);
Route::get('/home-cms/home-banners', [\App\Http\Controllers\Api\HomeCmsController::class, 'getHomeBanners']);
Route::get('/home-cms/home-settings', [\App\Http\Controllers\Api\HomeCmsController::class, 'getHomeSettings']);
Route::get('/home-cms/footer-sections', [\App\Http\Controllers\Api\HomeCmsController::class, 'getFooterSections']);
Route::get('/home-cms/social-links', [\App\Http\Controllers\Api\HomeCmsController::class, 'getSocialLinks']);
Route::get('/home-cms/bottom-bar', [\App\Http\Controllers\Api\HomeCmsController::class, 'getBottomBarSettings']);
Route::get('/home-cms/newsletter-text', [\App\Http\Controllers\Api\HomeCmsController::class, 'getNewsletterText']);

// Rutas públicas del newsletter
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'subscribe']);
Route::post('/newsletter/unsubscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'unsubscribe']);

// Rutas protegidas de menú (solo admin)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/menu-items', [\App\Http\Controllers\Api\MenuItemController::class, 'store']);
    Route::put('/menu-items/{id}', [\App\Http\Controllers\Api\MenuItemController::class, 'update']);
    Route::delete('/menu-items/{id}', [\App\Http\Controllers\Api\MenuItemController::class, 'destroy']);

    // Rutas protegidas de banners promocionales (solo admin)
    Route::post('/promotional-banners', [\App\Http\Controllers\Api\PromotionalBannerController::class, 'store']);
    Route::put('/promotional-banners/{id}', [\App\Http\Controllers\Api\PromotionalBannerController::class, 'update']);
    Route::delete('/promotional-banners/{id}', [\App\Http\Controllers\Api\PromotionalBannerController::class, 'destroy']);

    // Rutas protegidas de categorías destacadas (solo admin)
    Route::post('/featured-categories', [\App\Http\Controllers\Api\FeaturedCategoryController::class, 'store']);
    Route::put('/featured-categories/{id}', [\App\Http\Controllers\Api\FeaturedCategoryController::class, 'update']);
    Route::delete('/featured-categories/{id}', [\App\Http\Controllers\Api\FeaturedCategoryController::class, 'destroy']);

    // Rutas protegidas del CMS del home (solo admin)
    Route::put('/home-cms/top-banner', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateTopBanner']);
    Route::post('/home-cms/home-banners/upload-image', [\App\Http\Controllers\Api\HomeCmsController::class, 'uploadBannerImage']);
    Route::post('/home-cms/home-banners', [\App\Http\Controllers\Api\HomeCmsController::class, 'createHomeBanner']);
    Route::put('/home-cms/home-banners/{id}', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateHomeBanner']);
    Route::delete('/home-cms/home-banners/{id}', [\App\Http\Controllers\Api\HomeCmsController::class, 'deleteHomeBanner']);
    Route::put('/home-cms/home-settings', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateHomeSetting']);
    Route::post('/home-cms/footer-sections', [\App\Http\Controllers\Api\HomeCmsController::class, 'createFooterSection']);
    Route::put('/home-cms/footer-sections/{id}', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateFooterSection']);
    Route::delete('/home-cms/footer-sections/{id}', [\App\Http\Controllers\Api\HomeCmsController::class, 'deleteFooterSection']);
    Route::put('/home-cms/social-links', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateSocialLink']);
    Route::put('/home-cms/bottom-bar', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateBottomBarSettings']);
    Route::put('/home-cms/newsletter-text', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateNewsletterText']);
    Route::get('/home-cms/featured-categories', [\App\Http\Controllers\Api\HomeCmsController::class, 'getFeaturedCategories']);
    Route::put('/home-cms/featured-categories/{id}/visibility', [\App\Http\Controllers\Api\HomeCmsController::class, 'updateFeaturedCategoryVisibility']);

    // Rutas protegidas del newsletter (solo admin)
    Route::get('/newsletter/subscriptions', [\App\Http\Controllers\Api\NewsletterController::class, 'index']);
});

// Rutas públicas de productos
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Rutas protegidas de productos (requieren autenticación y rol vendor/admin)
Route::middleware(['auth:sanctum', 'vendor.admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::match(['put', 'post'], '/products/{id}', [ProductController::class, 'update']); // Permitir POST con _method=PUT
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

// Rutas de pedidos
// Crear pedido (público - no requiere autenticación)
Route::post('/orders', [OrderController::class, 'store']);
// Obtener QR del vendedor (público)
Route::get('/orders/vendor-qr', [OrderController::class, 'getVendorQr']);

// Ver pedidos del vendor (solo vendors y admins autenticados)
// Ver pedidos del cliente (solo clientes autenticados)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/orders/vendor', [OrderController::class, 'vendorOrders']);
    Route::get('/orders/customer', [OrderController::class, 'customerOrders']);
    Route::get('/orders/last-address', [OrderController::class, 'getLastAddress']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Dashboard para vendors
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);

    // Gestión de vendedores (solo admin)
    Route::prefix('vendors')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\VendorController::class, 'index']);
        Route::get('/pending/count', [\App\Http\Controllers\Api\VendorController::class, 'pendingCount']);
        Route::patch('/{id}/status', [\App\Http\Controllers\Api\VendorController::class, 'updateStatus']);
        Route::put('/{id}', [\App\Http\Controllers\Api\VendorController::class, 'updateByAdmin']);
    });

    // Gestión de perfil del vendedor (solo vendors)
    Route::prefix('vendor')->middleware(['auth:sanctum', 'vendor.admin'])->group(function () {
        Route::put('/profile', [\App\Http\Controllers\Api\VendorController::class, 'updateProfile']);
        Route::post('/upload-qr', [\App\Http\Controllers\Api\VendorController::class, 'uploadQr']);
    });

    // Configuraciones (solo admin)
    Route::prefix('settings')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\SettingsController::class, 'index']);
        Route::put('/{key}', [\App\Http\Controllers\Api\SettingsController::class, 'update']);
    });
});

// Configuraciones públicas (solo lectura)
Route::get('/settings/{key}', [\App\Http\Controllers\Api\SettingsController::class, 'get']);

// Obtener vendedores aprobados (público - para filtros)
Route::get('/vendors/approved', function () {
    $vendors = \App\Models\User::where('role', 'vendor')
        ->where('status', 'approved')
        ->select('id', 'name', 'email')
        ->orderBy('name')
        ->get();

    return response()->json([
        'data' => $vendors,
    ]);
});

