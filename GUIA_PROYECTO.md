# Finanzas Compartidas - Guía de Implementación

## 1. Descripción General

**Nombre del proyecto:** Finanzas Compartidas
**Tecnologías:** Laravel 11 + Vue 3 + Tailwind CSS + SQLite + Laravel Sanctum
**Tipo:** PWA (Progressive Web App)
**Propósito:** Gestionar gastos personales y compartidos, calculando automáticamente la deuda que una persona secundaria (pareja) tiene con el usuario principal.

---

## 2. Modelo de Negocio

### 2.1 Concepto Principal

El sistema está diseñado desde la perspectiva del **usuario principal** (dueño de la cuenta):

- El usuario principal tiene tarjetas/cuentas propias
- Su **pareja** (persona secundaria) usa sus tarjetas, generando deuda
- Algunos gastos son regalos (no generan deuda)
- Otros gastos se comparten por porcentaje
- La pareja hace pagos esporádicos (abonos) para reducir la deuda

### 2.2 Modelo Multi-Usuario

- Cada usuario tiene una cuenta independiente
- Los datos están completamente aislados por usuario
- Cada usuario configura su propia persona secundaria y porcentajes
- **NO hay cuenta compartida** entre usuarios

### 2.3 Tipos de Gasto

| Tipo | Descripción | Impacto en Deuda |
|------|-------------|------------------|
| **Personal** | Gasto 100% del usuario principal | No genera deuda |
| **Pareja** | Gasto 100% de la persona secundaria | 100% a la deuda |
| **Compartido** | Gasto compartido del hogar | % configurado a la deuda |

### 2.4 Cálculo de la Deuda

```
Deuda de la Pareja =
    + Suma de gastos tipo "pareja" (100%)
    + Suma de (gastos tipo "compartido" × porcentaje_pareja)
    - Suma de abonos recibidos
```

**Ejemplo con porcentaje_pareja = 40%:**
- Gastos pareja: $100,000
- Gastos compartidos: $200,000 × 40% = $80,000
- Abonos recibidos: $50,000
- **Deuda total: $130,000**

### 2.5 División de Gastos Compartidos
- Por defecto: **40% Pareja** / **60% Usuario principal**
- Los porcentajes son configurables por usuario
- Siempre deben sumar 100%

### 2.6 Medios de Pago
Los medios de pago son **administrables** desde un módulo de configuración:
- Cada usuario tiene sus propios medios de pago
- El usuario puede **añadir**, **editar** y **eliminar** medios de pago
- Cada medio de pago tiene: nombre, icono (opcional) y estado (activo/inactivo)
- No se puede eliminar un medio de pago que tenga gastos asociados (solo desactivar)
- Medios de pago por defecto (creados con seeder):
  1. Davivienda Crédito
  2. Daviplata
  3. Nequi
  4. Efectivo

### 2.7 Abonos
- Los abonos representan pagos de la **pareja** al usuario principal
- Reducen el saldo pendiente (deuda)
- Se registra fecha, valor y nota opcional

### 2.8 Categorías de Gasto
Las categorías son **administrables** desde la aplicación:
- Cada usuario tiene sus propias categorías
- El usuario puede **añadir**, **editar** y **eliminar** categorías
- La categoría es **opcional** al registrar un gasto
- Categorías por defecto (creadas con seeder):
  1. Alimentación
  2. Transporte
  3. Servicios
  4. Entretenimiento
  5. Salud
  6. Otros

### 2.9 Conceptos Frecuentes
Sistema para acelerar el registro diario:
- Se guardan automáticamente los conceptos más usados por usuario
- **Autocompletado** al escribir en el campo concepto
- Lista de **favoritos** que el usuario puede marcar manualmente
- Al seleccionar un favorito, puede autocompletar medio de pago y tipo

### 2.10 Plantillas Rápidas
Combinaciones predefinidas para registro en **2-3 taps**:
- Cada usuario tiene sus propias plantillas
- El usuario crea plantillas con: nombre, concepto, medio de pago, tipo, categoría y valor (opcional)
- Acceso rápido desde el **dashboard** con botones destacados
- Al usar una plantilla: solo confirmar fecha y valor (si no está predefinido)
- Máximo 6 plantillas visibles en dashboard (las más usadas)

### 2.11 Gastos Recurrentes
Para gastos que se repiten mensualmente:
- Cada usuario configura sus propios gastos recurrentes
- El usuario configura: concepto, medio de pago, tipo, categoría, valor, día del mes
- Notificación visual cuando hay gastos recurrentes pendientes de confirmar

---

## 3. Autenticación

### 3.1 Sistema de Autenticación
- **Laravel Sanctum** para autenticación basada en tokens
- Tokens persistentes en `localStorage` del navegador
- No hay expiración de tokens (persisten hasta logout manual)
- Registro y login desde la aplicación

### 3.2 Flujo de Autenticación
1. Usuario accede a la app sin token → Redirigido a `/login`
2. Usuario ingresa credenciales → API devuelve token
3. Token guardado en `localStorage`
4. Todas las peticiones API incluyen el token en headers
5. Al hacer logout → Token eliminado del servidor y `localStorage`

### 3.3 Endpoints de Autenticación
| Método | Endpoint | Descripción | Autenticación |
|--------|----------|-------------|---------------|
| POST | `/api/register` | Registrar nuevo usuario | No |
| POST | `/api/login` | Iniciar sesión | No |
| POST | `/api/logout` | Cerrar sesión | Sí |
| GET | `/api/user` | Obtener usuario actual | Sí |

### 3.4 Credenciales por Defecto (Seeder)
```
Email: david@example.com
Password: password
```

---

## 4. Arquitectura del Sistema

### 4.1 Estructura de Carpetas
```
finanzas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Login, Register, Logout
│   │   │   ├── GastoController.php
│   │   │   ├── AbonoController.php
│   │   │   ├── MedioPagoController.php
│   │   │   ├── CategoriaController.php
│   │   │   ├── ConceptoFrecuenteController.php
│   │   │   ├── PlantillaController.php
│   │   │   ├── GastoRecurrenteController.php
│   │   │   ├── ConfiguracionController.php
│   │   │   └── DashboardController.php
│   │   └── Requests/
│   │       ├── GastoRequest.php
│   │       ├── AbonoRequest.php
│   │       └── ...
│   └── Models/
│       ├── User.php                        # Con método calcularDeudaPersona2()
│       ├── Gasto.php
│       ├── Abono.php
│       ├── MedioPago.php
│       ├── Categoria.php
│       ├── ConceptoFrecuente.php
│       ├── Plantilla.php
│       ├── GastoRecurrente.php
│       └── Configuracion.php
├── database/
│   ├── migrations/
│   │   ├── 2014_10_12_000000_create_users_table.php
│   │   ├── 2019_12_14_000001_create_personal_access_tokens_table.php
│   │   ├── 2024_01_01_000001_create_medios_pago_table.php
│   │   ├── 2024_01_01_000002_create_categorias_table.php
│   │   ├── 2024_01_01_000003_create_gastos_table.php
│   │   ├── 2024_01_01_000004_create_abonos_table.php
│   │   ├── 2024_01_01_000005_create_conceptos_frecuentes_table.php
│   │   ├── 2024_01_01_000006_create_plantillas_table.php
│   │   ├── 2024_01_01_000007_create_gastos_recurrentes_table.php
│   │   ├── 2024_01_01_000008_create_configuraciones_table.php
│   │   └── 2025_12_27_024044_add_multiuser_support.php  # Añade user_id a todas las tablas
│   └── seeders/
│       ├── UserSeeder.php
│       ├── MedioPagoSeeder.php
│       ├── CategoriaSeeder.php
│       └── ConfiguracionSeeder.php
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   ├── axios.js                        # Con interceptor de auth token
│   │   ├── router.js                       # Con navigation guards
│   │   ├── Components/
│   │   │   ├── Layout/
│   │   │   │   ├── AppLayout.vue           # Con botón de logout
│   │   │   │   └── BottomNav.vue
│   │   │   ├── Gastos/
│   │   │   ├── Dashboard/
│   │   │   └── UI/
│   │   ├── Pages/
│   │   │   ├── Login.vue                   # Página de login
│   │   │   ├── Dashboard.vue
│   │   │   ├── Gastos/
│   │   │   ├── Abonos/
│   │   │   ├── Historial.vue
│   │   │   └── Configuracion.vue
│   │   └── Stores/
│   │       ├── auth.js                     # Store de autenticación
│   │       ├── gastos.js
│   │       ├── dashboard.js
│   │       ├── theme.js
│   │       └── config.js
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── api.php                             # Rutas protegidas con sanctum
│   └── web.php
└── .env
```

---

## 5. Base de Datos

### 5.1 Diagrama Entidad-Relación

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                   users                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ id | name | email | password | persona_secundaria_id (FK, nullable) |       │
│ porcentaje_persona_2 | created_at | updated_at                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      │ user_id (FK)
                                      ▼
┌───────────────────────────────────────────────────────────────────────────────┐
│                                   gastos                                       │
├───────────────────────────────────────────────────────────────────────────────┤
│ id | user_id (FK) | fecha | medio_pago_id (FK) | categoria_id (FK, nullable) │
│ concepto | valor | tipo | registrado_por | created_at | updated_at            │
└───────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                                   abonos                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│ id | user_id (FK) | fecha | valor | nota | created_at | updated_at          │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              medios_pago                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│ id | user_id (FK) | nombre | icono | activo | orden | created_at            │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                               categorias                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│ id | user_id (FK) | nombre | icono | color | activo | orden | created_at    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          conceptos_frecuentes                                │
├─────────────────────────────────────────────────────────────────────────────┤
│ id | user_id (FK) | concepto | medio_pago_id | tipo | uso_count |           │
│ es_favorito | created_at                                                     │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                               plantillas                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│ id | user_id (FK) | nombre | concepto | medio_pago_id | categoria_id |      │
│ tipo | valor | uso_count | activo | orden | created_at                       │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          gastos_recurrentes                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│ id | user_id (FK) | concepto | medio_pago_id | categoria_id | tipo |        │
│ valor | dia_mes | activo | ultimo_registro | created_at                      │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 5.2 Tabla: `users`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| name | VARCHAR(255) | NOT NULL | Nombre del usuario |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Email para login |
| password | VARCHAR(255) | NOT NULL | Password hasheado |
| persona_secundaria_id | BIGINT | NULLABLE, FK → users.id | Referencia a otro usuario (pareja) |
| porcentaje_persona_2 | DECIMAL(5,2) | NOT NULL, DEFAULT 40.00 | % de gastos compartidos para la pareja |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

### 5.3 Tabla: `gastos`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| user_id | BIGINT | NOT NULL, FK → users.id | Usuario propietario |
| fecha | DATE | NOT NULL | Fecha del gasto |
| medio_pago_id | BIGINT | NOT NULL, FK → medios_pago.id | Referencia al medio de pago |
| categoria_id | BIGINT | NULLABLE, FK → categorias.id | Referencia a la categoría (opcional) |
| concepto | VARCHAR(255) | NOT NULL | Descripción del gasto |
| valor | DECIMAL(12,2) | NOT NULL, UNSIGNED | Monto del gasto |
| tipo | VARCHAR(20) | NOT NULL | Enum: personal, pareja, compartido |
| registrado_por | VARCHAR(20) | NULLABLE | Quién registró (usuario/pareja) |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Tipos de Gasto:**
- `personal`: 100% del usuario principal, NO genera deuda
- `pareja`: 100% de la persona secundaria, genera deuda completa
- `compartido`: Se divide según el porcentaje configurado

### 5.4 Tabla: `abonos`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| user_id | BIGINT | NOT NULL, FK → users.id | Usuario propietario |
| fecha | DATE | NOT NULL | Fecha del abono |
| valor | DECIMAL(12,2) | NOT NULL, UNSIGNED | Monto del abono |
| nota | VARCHAR(255) | NULLABLE | Nota opcional |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

---

## 6. Modelos Eloquent

### 6.1 Modelo: User
```php
// app/Models/User.php

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'persona_secundaria_id', 'porcentaje_persona_2'
    ];

    // Relaciones
    public function gastos() { return $this->hasMany(Gasto::class); }
    public function abonos() { return $this->hasMany(Abono::class); }
    public function mediosPago() { return $this->hasMany(MedioPago::class); }
    public function categorias() { return $this->hasMany(Categoria::class); }
    public function plantillas() { return $this->hasMany(Plantilla::class); }
    public function gastosRecurrentes() { return $this->hasMany(GastoRecurrente::class); }
    public function conceptosFrecuentes() { return $this->hasMany(ConceptoFrecuente::class); }

    // Cálculo de deuda
    public function calcularDeudaPersona2(): float
    {
        $gastosPareja = $this->gastos()->where('tipo', 'pareja')->sum('valor');
        $gastosCompartidos = $this->gastos()->where('tipo', 'compartido')->sum('valor');
        $porcionCompartida = $gastosCompartidos * ($this->porcentaje_persona_2 / 100);
        $abonos = $this->abonos()->sum('valor');

        return round($gastosPareja + $porcionCompartida - $abonos, 2);
    }

    // Gasto del mes actual
    public function gastoMesActual(): float
    {
        return $this->gastos()
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('valor');
    }
}
```

### 6.2 Modelo: Gasto
```php
// app/Models/Gasto.php

class Gasto extends Model
{
    protected $fillable = [
        'user_id', 'fecha', 'medio_pago_id', 'categoria_id',
        'concepto', 'valor', 'tipo', 'registrado_por'
    ];

    // Constantes para tipos
    const TIPO_PERSONAL = 'personal';    // 100% del usuario principal
    const TIPO_PAREJA = 'pareja';        // 100% de la persona secundaria (genera deuda)
    const TIPO_COMPARTIDO = 'compartido'; // Se divide por porcentaje

    const TIPOS = [
        self::TIPO_PERSONAL,
        self::TIPO_PAREJA,
        self::TIPO_COMPARTIDO
    ];

    // Relaciones
    public function user() { return $this->belongsTo(User::class); }
    public function medioPago() { return $this->belongsTo(MedioPago::class); }
    public function categoria() { return $this->belongsTo(Categoria::class); }

    // Scopes
    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```

---

## 7. API REST

### 7.1 Autenticación en Headers
Todas las rutas protegidas requieren el header:
```
Authorization: Bearer {token}
```

### 7.2 Endpoints

#### Autenticación (Sin token)
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/register` | Registrar nuevo usuario |
| POST | `/api/login` | Iniciar sesión |

#### Rutas Protegidas (Con token)
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/user` | Obtener usuario actual |

#### Dashboard
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/dashboard` | Datos completos del dashboard |
| GET | `/api/dashboard/saldo` | Solo deuda y gasto del mes |
| GET | `/api/dashboard/resumen-mes` | Resumen del mes actual |

#### Gastos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/gastos` | Listar gastos del usuario |
| POST | `/api/gastos` | Crear nuevo gasto |
| GET | `/api/gastos/{id}` | Obtener gasto específico |
| PUT | `/api/gastos/{id}` | Actualizar gasto |
| DELETE | `/api/gastos/{id}` | Eliminar gasto |

#### Abonos
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/abonos` | Listar abonos del usuario |
| POST | `/api/abonos` | Crear nuevo abono |
| PUT | `/api/abonos/{id}` | Actualizar abono |
| DELETE | `/api/abonos/{id}` | Eliminar abono |

*(El resto de endpoints siguen el mismo patrón, todos scopeados por user_id)*

### 7.3 Respuesta Dashboard
```json
{
    "success": true,
    "data": {
        "deuda_persona_2": 150000.00,
        "gasto_mes_actual": 450000.00,
        "porcentaje_persona_2": 40,
        "persona_secundaria": null,
        "resumen_mes": {
            "mes": 12,
            "anio": 2024,
            "total_gastos": 450000.00,
            "gastos_personal": 100000.00,
            "gastos_pareja": 150000.00,
            "gastos_compartido": 200000.00,
            "total_abonos": 50000.00
        },
        "por_medio_pago": { ... },
        "ultimos_movimientos": [ ... ],
        "pendientes_recurrentes": 2
    }
}
```

---

## 8. Frontend (Vue 3)

### 8.1 Store de Autenticación
```javascript
// resources/js/Stores/auth.js

import { defineStore } from 'pinia';
import api, { TOKEN_KEY } from '../axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem(TOKEN_KEY) || null,
        loading: false,
        error: null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        currentUser: (state) => state.user
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            const response = await api.post('/login', { email, password });
            this.token = response.data.token;
            this.user = response.data.user;
            localStorage.setItem(TOKEN_KEY, this.token);
        },

        async logout() {
            await api.post('/logout');
            this.token = null;
            this.user = null;
            localStorage.removeItem(TOKEN_KEY);
        },

        async checkAuth() {
            if (!this.token) return false;
            const response = await api.get('/user');
            this.user = response.data;
            return true;
        }
    }
});
```

### 8.2 Axios con Interceptor de Auth
```javascript
// resources/js/axios.js

import axios from 'axios';

export const TOKEN_KEY = 'finanzas_auth_token';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Interceptor para añadir token a todas las peticiones
api.interceptors.request.use(config => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
```

### 8.3 Store de Configuración
```javascript
// resources/js/Stores/config.js

import { defineStore } from 'pinia';

export const useConfigStore = defineStore('config', {
    state: () => ({
        loaded: true
    }),

    getters: {
        tiposGasto: () => [
            { value: 'personal', label: 'Personal (mío)' },
            { value: 'pareja', label: 'Pareja (100%)' },
            { value: 'compartido', label: 'Compartido' }
        ],

        getNombreTipo: () => (tipo) => {
            const tipos = {
                'personal': 'Personal',
                'pareja': 'Pareja',
                'compartido': 'Compartido'
            };
            return tipos[tipo] || tipo;
        }
    }
});
```

### 8.4 Router con Guards
```javascript
// resources/js/router.js

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './Stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('./Pages/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('./Pages/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    // ... resto de rutas con requiresAuth: true
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guard
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next('/');
    } else {
        next();
    }
});

export default router;
```

### 8.5 Dashboard Principal
El dashboard muestra prominentemente:

1. **Card "Te debe"**: Deuda actual de la pareja (rojo si > 0, verde si = 0)
2. **Card "Gasto este mes"**: Total de gastos del mes actual
3. **Alerta de recurrentes**: Si hay gastos recurrentes pendientes
4. **Plantillas rápidas**: Acceso rápido a plantillas favoritas
5. **Resumen del mes**: Desglose por tipo (personal, pareja, compartido)
6. **Últimos movimientos**: Lista de últimas transacciones

---

## 9. Configuración del Entorno

### 9.1 Variables de Entorno (.env)
```env
APP_NAME="Finanzas Compartidas"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=sqlite
DB_DATABASE=/ruta/absoluta/database/database.sqlite

# Importante: Usar file en lugar de database para evitar errores
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

### 9.2 Comandos de Instalación
```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install

# Crear base de datos
touch database/database.sqlite

# Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# Compilar assets
npm run build

# Iniciar servidor (puerto personalizado)
php artisan serve --port=8080
```

---

## 10. Plan de Implementación

### Fases Completadas

| Fase | Descripción | Estado |
|------|-------------|--------|
| 1 | Setup + Base de Datos | ✅ |
| 2 | Modelos Eloquent | ✅ |
| 3 | Validaciones (Form Requests) | ✅ |
| 4 | Controladores | ✅ |
| 5 | Rutas API | ✅ |
| 6 | Setup Frontend | ✅ |
| 7 | Stores (Pinia) | ✅ |
| 8 | Componentes | ✅ |
| 9 | Páginas | ✅ |
| 10 | PWA + Testing + Deploy | ✅ (parcial) |

### Fase 11: Autenticación Multi-Usuario ✅
1. [x] Instalar Laravel Sanctum
2. [x] Crear migración de users con campos adicionales
3. [x] Crear migración para añadir user_id a todas las tablas
4. [x] Actualizar todos los modelos con relación a User
5. [x] Actualizar todos los controladores para scopear por usuario
6. [x] Crear AuthController (login, register, logout)
7. [x] Crear store de autenticación en Vue
8. [x] Crear página de Login
9. [x] Configurar axios con interceptor de token
10. [x] Configurar router con navigation guards
11. [x] Añadir botón de logout en AppLayout
12. [x] Actualizar tipos de gasto (personal, pareja, compartido)
13. [x] Actualizar dashboard para mostrar deuda + gasto mensual

---

## 11. Notas Adicionales

### Seguridad
- Autenticación con tokens Sanctum
- Datos aislados por usuario
- Tokens persistentes en localStorage
- HTTPS recomendado en producción

### Rendimiento
- SQLite suficiente para uso personal
- Índices en columnas de filtro
- Paginación en listados largos

### Experiencia de Usuario
- Dashboard enfocado en deuda y gasto mensual
- Tipos de gasto claros: Personal, Pareja, Compartido
- Login persistente (no expira)
- Modo oscuro con preferencia del sistema

### Futuras Mejoras
- [ ] Registro de pareja con cuenta vinculada
- [ ] Notificaciones de deuda alta
- [ ] Gráficos de evolución de deuda
- [ ] Exportación de reportes
- [ ] Backup automático en la nube
