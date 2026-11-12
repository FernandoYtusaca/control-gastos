# Control de Gastos API

API REST para gestionar ingresos y gastos personales, construida con **Laravel 13**. Cada usuario administra sus propias categorías y transacciones, y puede consultar un resumen mensual de su balance.

## Características

- Registro, login y logout con tokens (Laravel Sanctum)
- CRUD de categorías (ingreso / gasto)
- CRUD de transacciones con filtros por mes, año, categoría y tipo
- Resumen mensual: total de ingresos, gastos, balance y detalle por categoría
- Cada usuario solo accede a sus propios datos
- Validaciones y reglas de negocio (por ejemplo, no se puede eliminar una categoría con transacciones)
- Tests automatizados con PHPUnit

## Tecnologías

- PHP 8.3+ y Laravel 13
- MySQL / MariaDB
- Laravel Sanctum
- PHPUnit

## Instalación

```bash
git clone https://github.com/[tu-usuario]/control-gastos.git
cd control-gastos
composer install
cp .env.example .env
php artisan key:generate
```

Crea una base de datos llamada `control_gastos` y configura el `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=control_gastos
DB_USERNAME=root
DB_PASSWORD=
```

Ejecuta las migraciones y levanta el servidor:

```bash
php artisan migrate
php artisan serve
```

La API queda disponible en `http://127.0.0.1:8000/api`.

## Endpoints

Todas las peticiones deben incluir el header `Accept: application/json`. Las rutas protegidas requieren `Authorization: Bearer {token}`.

| Método | Ruta | Descripción | Auth |
|---|---|---|---|
| POST | `/api/register` | Crear cuenta | No |
| POST | `/api/login` | Iniciar sesión | No |
| POST | `/api/logout` | Cerrar sesión | Sí |
| GET | `/api/user` | Datos del usuario autenticado | Sí |
| GET | `/api/categories` | Listar categorías | Sí |
| POST | `/api/categories` | Crear categoría | Sí |
| GET | `/api/categories/{id}` | Ver categoría | Sí |
| PUT | `/api/categories/{id}` | Editar categoría | Sí |
| DELETE | `/api/categories/{id}` | Eliminar categoría | Sí |
| GET | `/api/transactions` | Listar transacciones (paginadas) | Sí |
| POST | `/api/transactions` | Crear transacción | Sí |
| GET | `/api/transactions/{id}` | Ver transacción | Sí |
| PUT | `/api/transactions/{id}` | Editar transacción | Sí |
| DELETE | `/api/transactions/{id}` | Eliminar transacción | Sí |
| GET | `/api/summary` | Resumen mensual | Sí |

### Filtros de transacciones

`GET /api/transactions?month=11&year=2026&category_id=1&type=expense`

Todos los parámetros son opcionales y se pueden combinar.

### Ejemplo: crear una transacción

```http
POST /api/transactions
Authorization: Bearer {token}
Accept: application/json

{
  "category_id": 1,
  "amount": 35.50,
  "description": "Almuerzo",
  "date": "2026-11-05"
}
```

### Ejemplo: resumen mensual

`GET /api/summary?month=11&year=2026`

```json
{
  "month": 11,
  "year": 2026,
  "total_income": 2500,
  "total_expense": 35.5,
  "balance": 2464.5,
  "by_category": [
    { "category_id": 2, "name": "Sueldo", "type": "income", "total": "2500.00" },
    { "category_id": 1, "name": "Comida", "type": "expense", "total": "35.50" }
  ]
}
```

## Modelo de datos

- `users`: id, name, email, password
- `categories`: id, user_id, name, type (`income` / `expense`)
- `transactions`: id, user_id, category_id, amount, description, date

Un usuario tiene muchas categorías y transacciones; una categoría tiene muchas transacciones.

## Tests

```bash
php artisan test
```

Los tests usan una base de datos SQLite en memoria, por lo que no afectan a tu base de datos de desarrollo.

## Autor

Saúl Fernando Ytusaca Quispe: https://www.linkedin.com/in/saul-fernando-ytusaca-quispe