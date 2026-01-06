# 🚀 Comandos Completos para Configurar el Ecommerce

## 1. Ejecutar Migraciones y Seeders (PRIMERA VEZ)

Este comando eliminará todas las tablas y las recreará con datos de ejemplo:

```bash
cd backend
php artisan migrate:fresh --seed
```

**Esto creará:**
- ✅ Tabla `users` con Admin y Vendor
- ✅ Tabla `categories` con 11 categorías
- ✅ Tabla `products` con 18 productos de ejemplo
- ✅ Tabla `orders` para el sistema de pedidos

## 2. Verificar que todo esté correcto

### Verificar productos creados:
```bash
cd backend
php artisan tinker
```

Luego ejecuta:
```php
\App\Models\Product::count(); // Debe mostrar 18
\App\Models\Category::count(); // Debe mostrar 11
\App\Models\User::count(); // Debe mostrar 2 (Admin y Vendor)
```

### Ver productos con descuento:
```php
\App\Models\Product::whereNotNull('discount_percentage')->get(['name', 'price', 'discount_percentage']);
```

### Ver productos nuevos:
```php
\App\Models\Product::where('is_new', true)->get(['name', 'is_new']);
```

## 3. Levantar el Backend

```bash
cd backend
php artisan serve
```

El backend estará disponible en: `http://localhost:8000`

## 4. Levantar el Frontend

```bash
cd frontend
npm install  # Solo la primera vez
npm run dev
```

El frontend estará disponible en: `http://localhost:5173`

## 5. Probar el Ecommerce

### Endpoints del Backend:

- **Categorías:** `GET http://localhost:8000/api/categories`
- **Productos:** `GET http://localhost:8000/api/products`
- **Productos nuevos:** `GET http://localhost:8000/api/products?is_new=true`
- **Productos con descuento:** `GET http://localhost:8000/api/products?has_discount=true`
- **Productos por categoría:** `GET http://localhost:8000/api/products?category_slug=vestidos`

### Páginas del Frontend:

- **Home:** `http://localhost:5173/`
- **Novedades:** `http://localhost:5173/novedades`
- **Ofertas:** `http://localhost:5173/ofertas`
- **Categorías:** `http://localhost:5173/categoria/vestidos`

## 6. Si necesitas resetear todo

```bash
cd backend
php artisan migrate:fresh --seed
```

## 📦 Productos de Ejemplo Creados

El seeder crea **18 productos** con:

- ✅ **8 productos nuevos** (is_new = true)
- ✅ **5 productos con descuento** (20%, 15%, 25%)
- ✅ **5 productos destacados** (is_featured = true)
- ✅ Productos en diferentes categorías: Vestidos, Blusas, Camisas, Conjuntos
- ✅ Precios variados desde S/ 59.00 hasta S/ 129.00
- ✅ Stock variado de 5 a 20 unidades

## 🔑 Credenciales de Usuario

- **Admin:**
  - Email: `admin@example.com`
  - Password: `password`

- **Vendor:**
  - Email: `vendor@example.com`
  - Password: `password`

