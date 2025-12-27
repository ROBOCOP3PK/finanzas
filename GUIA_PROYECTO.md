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

---

## 12. Despliegue en Servidor Casero

### 12.1 Hardware Disponible
| Componente | Especificación |
|------------|----------------|
| Procesador | Intel Core i5 |
| RAM | 8 GB |
| Almacenamiento | 500 GB HDD/SSD |
| Uso | Servidor dedicado para esta aplicación |

> **Nota:** Estas especificaciones son más que suficientes para una aplicación Laravel personal con pocos usuarios.

---

### 12.2 Sistema Operativo

**Recomendado:** Ubuntu Server 24.04 LTS (sin interfaz gráfica)

**¿Por qué Ubuntu Server sin GUI?**
- Consume ~200MB RAM vs ~2GB con escritorio
- Más estable para servidores 24/7
- Actualizaciones de seguridad por 5 años (LTS)
- Amplia documentación y comunidad

**Descarga:** https://ubuntu.com/download/server

---

### 12.3 Stack de Software

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENTE (Navegador)                   │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                      NGINX (Puerto 80/443)               │
│                   Servidor web + Proxy reverso           │
│                   + Certificado SSL (Let's Encrypt)      │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                      PHP-FPM 8.3                         │
│                   Procesa peticiones PHP                 │
└─────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                   Laravel 11 + SQLite                    │
│                   (Tu aplicación)                        │
└─────────────────────────────────────────────────────────┘
```

| Software | Versión | Propósito |
|----------|---------|-----------|
| Ubuntu Server | 24.04 LTS | Sistema operativo |
| Nginx | Última | Servidor web (más ligero que Apache) |
| PHP | 8.3 | Runtime de Laravel |
| PHP-FPM | 8.3 | Gestor de procesos PHP |
| Composer | 2.x | Dependencias PHP |
| Node.js | 20 LTS | Compilar assets Vue |
| NPM | 10.x | Dependencias JavaScript |
| Git | Última | Control de versiones y deploy |
| Certbot | Última | Certificados SSL gratuitos |
| UFW | Incluido | Firewall |
| Fail2ban | Última | Protección contra ataques |

---

### 12.4 Configuración de Red

#### 12.4.1 Red Local
```
┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   Internet   │◄────►│    Router    │◄────►│   Servidor   │
│              │      │ (Port Fwd)   │      │  192.168.1.X │
└──────────────┘      └──────────────┘      └──────────────┘
```

**Configurar IP estática en el servidor:**
```bash
# /etc/netplan/00-installer-config.yaml
network:
  version: 2
  ethernets:
    enp0s3:  # Nombre de tu interfaz (ver con: ip a)
      dhcp4: no
      addresses:
        - 192.168.1.100/24  # IP fija que elijas
      routes:
        - to: default
          via: 192.168.1.1  # IP de tu router
      nameservers:
        addresses:
          - 8.8.8.8
          - 8.8.4.4
```

#### 12.4.2 Port Forwarding en Router
Configurar en tu router (generalmente en 192.168.1.1):

| Puerto Externo | Puerto Interno | Protocolo | Destino |
|----------------|----------------|-----------|---------|
| 80 | 80 | TCP | 192.168.1.100 |
| 443 | 443 | TCP | 192.168.1.100 |
| 22 | 22 | TCP | 192.168.1.100 (opcional, para SSH remoto) |

#### 12.4.3 DNS Dinámico (Si no tienes IP pública fija)

**Opciones gratuitas:**
| Servicio | Dominio gratuito | Notas |
|----------|------------------|-------|
| DuckDNS | tuapp.duckdns.org | Simple, gratuito |
| No-IP | tuapp.ddns.net | Popular, requiere confirmar cada 30 días |
| FreeDNS | tuapp.afraid.org | Muchas opciones de dominio |

**Configurar DuckDNS (recomendado):**
1. Crear cuenta en https://www.duckdns.org
2. Crear subdominio (ej: `finanzas-david`)
3. Instalar cliente en servidor:
```bash
# Crear script de actualización
mkdir -p ~/duckdns
echo "url=\"https://www.duckdns.org/update?domains=finanzas-david&token=TU_TOKEN&ip=\"" > ~/duckdns/duck.sh
chmod 700 ~/duckdns/duck.sh

# Programar actualización cada 5 minutos
crontab -e
# Añadir línea:
*/5 * * * * ~/duckdns/duck.sh >/dev/null 2>&1
```

---

### 12.5 Instalación Paso a Paso

#### Paso 1: Instalar Ubuntu Server
1. Descargar ISO de Ubuntu Server 24.04 LTS
2. Crear USB booteable con Rufus o Balena Etcher
3. Instalar seleccionando:
   - Instalación mínima
   - Instalar OpenSSH Server
   - NO instalar snaps adicionales

#### Paso 2: Configuración inicial del servidor
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Configurar zona horaria
sudo timedatectl set-timezone America/Bogota

# Crear usuario para la app (opcional pero recomendado)
sudo adduser finanzas
sudo usermod -aG sudo finanzas
```

#### Paso 3: Instalar Nginx
```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

#### Paso 4: Instalar PHP 8.3 y extensiones
```bash
# Añadir repositorio de PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instalar PHP y extensiones necesarias para Laravel
sudo apt install php8.3-fpm php8.3-cli php8.3-common php8.3-mysql \
    php8.3-xml php8.3-curl php8.3-gd php8.3-mbstring php8.3-zip \
    php8.3-bcmath php8.3-intl php8.3-sqlite3 -y

# Verificar instalación
php -v
```

#### Paso 5: Instalar Composer
```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

#### Paso 6: Instalar Node.js 20 LTS
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y
node -v
npm -v
```

#### Paso 7: Instalar Git
```bash
sudo apt install git -y
```

#### Paso 8: Configurar Firewall (UFW)
```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

#### Paso 9: Instalar Fail2ban
```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

### 12.6 Desplegar la Aplicación

#### Paso 1: Crear directorio y clonar repositorio
```bash
# Crear directorio para la app
sudo mkdir -p /var/www/finanzas
sudo chown -R $USER:$USER /var/www/finanzas

# Clonar repositorio (ajustar URL)
cd /var/www/finanzas
git clone https://github.com/tu-usuario/finanzas.git .
# O copiar archivos manualmente con scp/rsync
```

#### Paso 2: Instalar dependencias
```bash
cd /var/www/finanzas

# Dependencias PHP (producción)
composer install --optimize-autoloader --no-dev

# Dependencias JS y compilar
npm install
npm run build
```

#### Paso 3: Configurar Laravel
```bash
# Copiar archivo de entorno
cp .env.example .env

# Editar configuración
nano .env
```

**Contenido de .env para producción:**
```env
APP_NAME="Finanzas Compartidas"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://finanzas-david.duckdns.org

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/finanzas/database/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

```bash
# Crear base de datos SQLite
touch database/database.sqlite

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate --seed

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar permisos
sudo chown -R www-data:www-data /var/www/finanzas
sudo chmod -R 755 /var/www/finanzas
sudo chmod -R 775 /var/www/finanzas/storage
sudo chmod -R 775 /var/www/finanzas/bootstrap/cache
```

#### Paso 4: Configurar Nginx
```bash
sudo nano /etc/nginx/sites-available/finanzas
```

**Contenido del archivo:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name finanzas-david.duckdns.org;  # Tu dominio
    root /var/www/finanzas/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Habilitar sitio
sudo ln -s /etc/nginx/sites-available/finanzas /etc/nginx/sites-enabled/

# Deshabilitar sitio por defecto
sudo rm /etc/nginx/sites-enabled/default

# Verificar configuración
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
```

#### Paso 5: Instalar certificado SSL (Let's Encrypt)
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtener certificado (tu dominio debe estar apuntando al servidor)
sudo certbot --nginx -d finanzas-david.duckdns.org

# Renovación automática (ya configurada, pero verificar)
sudo certbot renew --dry-run
```

---

### 12.7 Configuración del Portátil como Servidor

#### 12.7.1 Evitar suspensión al cerrar tapa
```bash
sudo nano /etc/systemd/logind.conf
```
Descomentar y modificar:
```
HandleLidSwitch=ignore
HandleLidSwitchExternalPower=ignore
HandleLidSwitchDocked=ignore
```
```bash
sudo systemctl restart systemd-logind
```

#### 12.7.2 Configurar arranque automático tras corte de luz
- Acceder a BIOS (F2, F10 o Del al iniciar)
- Buscar "Power Management" o "AC Power Recovery"
- Configurar en "Power On" o "Last State"

#### 12.7.3 Monitoreo de temperatura
```bash
# Instalar sensores
sudo apt install lm-sensors -y
sudo sensors-detect  # Aceptar todo con Enter

# Ver temperaturas
sensors

# Monitoreo continuo (opcional)
watch -n 2 sensors
```

---

### 12.8 Mantenimiento y Actualizaciones

#### 12.8.1 Script de deploy (actualizar aplicación)
```bash
# Crear script: /var/www/finanzas/deploy.sh
#!/bin/bash
cd /var/www/finanzas

# Modo mantenimiento
php artisan down

# Obtener cambios
git pull origin main

# Actualizar dependencias
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Migraciones
php artisan migrate --force

# Limpiar y optimizar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar permisos
sudo chown -R www-data:www-data /var/www/finanzas
sudo chmod -R 775 storage bootstrap/cache

# Salir de mantenimiento
php artisan up

echo "Deploy completado!"
```

```bash
chmod +x /var/www/finanzas/deploy.sh
```

#### 12.8.2 Backup automático de base de datos
```bash
# Crear directorio de backups
mkdir -p ~/backups

# Crear script: ~/backups/backup.sh
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
cp /var/www/finanzas/database/database.sqlite ~/backups/finanzas_$DATE.sqlite

# Mantener solo últimos 30 backups
cd ~/backups && ls -t *.sqlite | tail -n +31 | xargs -r rm

echo "Backup creado: finanzas_$DATE.sqlite"
```

```bash
chmod +x ~/backups/backup.sh

# Programar backup diario a las 3am
crontab -e
# Añadir:
0 3 * * * ~/backups/backup.sh
```

#### 12.8.3 Actualizaciones del sistema
```bash
# Actualizar manualmente
sudo apt update && sudo apt upgrade -y

# O configurar actualizaciones automáticas de seguridad
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure unattended-upgrades
```

---

### 12.9 Checklist de Despliegue

#### Pre-instalación
- [ ] Descargar Ubuntu Server 24.04 LTS
- [ ] Crear USB booteable
- [ ] Anotar IP del router (para port forwarding)
- [ ] Decidir IP estática para el servidor (ej: 192.168.1.100)
- [ ] Crear cuenta en DuckDNS y subdominio

#### Instalación del Sistema
- [ ] Instalar Ubuntu Server (sin GUI)
- [ ] Configurar IP estática
- [ ] Actualizar sistema (`apt update && apt upgrade`)
- [ ] Configurar zona horaria

#### Instalación del Stack
- [ ] Instalar Nginx
- [ ] Instalar PHP 8.3 + extensiones
- [ ] Instalar Composer
- [ ] Instalar Node.js 20
- [ ] Instalar Git
- [ ] Configurar UFW (firewall)
- [ ] Instalar Fail2ban

#### Configuración de Red
- [ ] Configurar port forwarding en router (80, 443)
- [ ] Configurar DuckDNS
- [ ] Verificar acceso desde internet

#### Despliegue de la Aplicación
- [ ] Clonar/copiar código a `/var/www/finanzas`
- [ ] Instalar dependencias (composer, npm)
- [ ] Configurar `.env` de producción
- [ ] Ejecutar migraciones
- [ ] Configurar Nginx
- [ ] Instalar certificado SSL

#### Configuración del Portátil
- [ ] Deshabilitar suspensión al cerrar tapa
- [ ] Configurar arranque tras corte de luz (BIOS)
- [ ] Instalar sensores de temperatura

#### Post-despliegue
- [ ] Crear script de deploy
- [ ] Configurar backup automático
- [ ] Probar acceso desde móvil (red móvil, no WiFi)
- [ ] Verificar renovación automática de SSL

---

### 12.10 Resolución de Problemas Comunes

| Problema | Solución |
|----------|----------|
| Error 502 Bad Gateway | `sudo systemctl restart php8.3-fpm` |
| Error de permisos en storage | `sudo chmod -R 775 storage bootstrap/cache` |
| Certificado SSL no funciona | Verificar que el dominio apunta a tu IP pública |
| No accesible desde internet | Verificar port forwarding en router |
| Página en blanco | Revisar logs: `tail -f storage/logs/laravel.log` |
| Assets no cargan (CSS/JS) | Ejecutar `npm run build` y limpiar caché |

**Logs útiles:**
```bash
# Laravel
tail -f /var/www/finanzas/storage/logs/laravel.log

# Nginx
sudo tail -f /var/log/nginx/error.log

# PHP-FPM
sudo tail -f /var/log/php8.3-fpm.log
```
