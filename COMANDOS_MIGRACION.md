# 🚀 Comandos para Ejecutar Migraciones y Seeders

## Backend - Primera vez o después de cambios

```bash
cd backend
php artisan migrate:fresh --seed
```

Este comando:
1. Elimina todas las tablas
2. Ejecuta todas las migraciones (incluyendo categories y los nuevos campos de products)
3. Ejecuta los seeders (CategorySeeder y UserSeeder)

## Si solo necesitas ejecutar las nuevas migraciones

```bash
cd backend
php artisan migrate
```

## Si solo necesitas ejecutar los seeders

```bash
cd backend
php artisan db:seed
```

## Estructura de la Base de Datos

Después de ejecutar las migraciones tendrás:

- **users**: Con campos role y phone_number
- **categories**: Con todas las categorías del ecommerce
- **products**: Con category_id, discount_percentage, is_new, is_featured
- **orders**: Para el sistema de pedidos Yape/Plin

