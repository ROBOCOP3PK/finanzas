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
| Modelo | HP G42 (portátil ~2010-2011) |
| Procesador | Intel Core i5 |
| RAM | 3 GB DDR3 |
| Almacenamiento | 500 GB HDD |
| Uso | Servidor dedicado para esta aplicación |

> **Nota:** Incluso con 3GB de RAM, estas especificaciones son suficientes para una aplicación Laravel personal con pocos usuarios concurrentes.

> **⚠️ Importante sobre RAM:** No mezclar módulos DDR3 (1.5V) con DDR3L (1.35V). Son incompatibles y causan Kernel Panic durante la instalación.

---

### 12.2 Sistema Operativo

**Recomendado:** Ubuntu Server 22.04 LTS (sin interfaz gráfica)

> **Nota:** Para hardware antiguo (pre-2012) usar Ubuntu 22.04 LTS en lugar de 24.04. La versión 24.04 puede tener problemas de compatibilidad con hardware antiguo (GRUB se queda colgado).

**¿Por qué Ubuntu Server sin GUI?**
- Consume ~200MB RAM vs ~2GB con escritorio
- Más estable para servidores 24/7
- Actualizaciones de seguridad por 5 años (LTS)
- Amplia documentación y comunidad

**Descarga:** https://ubuntu.com/download/server
- Para hardware moderno (2012+): Ubuntu Server 24.04 LTS
- Para hardware antiguo (pre-2012): Ubuntu Server 22.04 LTS

---

### 12.3 Instalación de Ubuntu Server

#### 12.3.1 Crear USB Booteable

**Requisitos:**
- USB de mínimo 4GB
- ISO de Ubuntu Server 22.04 LTS descargado

**En Windows (con Rufus):**

1. Descargar Rufus desde https://rufus.ie
2. Insertar USB de mínimo 4GB
3. Abrir Rufus y configurar según tu hardware:

**Para hardware MODERNO (2012+) - GPT + UEFI:**

| Opción | Qué seleccionar |
|--------|-----------------|
| **Dispositivo** | Tu USB (ej: "Kingston 8GB") |
| **Selección de arranque** | `ubuntu-22.04-live-server-amd64.iso` |
| **Esquema de partición** | **GPT** |
| **Sistema de destino** | **UEFI (no CSM)** (automático) |

```
┌─────────────────────────────────────────────────────────┐
│  RUFUS 4.x - Hardware Moderno (2012+)                   │
├─────────────────────────────────────────────────────────┤
│  Dispositivo:        [Kingston 8GB (F:)]           ▼   │
│  Selección arranque: [ubuntu-22.04...iso] [SELECCIONAR]│
│  Esquema partición:  [GPT]                         ▼   │ ← UEFI
│  Sistema de destino: [UEFI (no CSM)]               ▼   │
│                                          [EMPEZAR]      │
└─────────────────────────────────────────────────────────┘
```

**Para hardware ANTIGUO (pre-2012, como HP G42) - MBR + BIOS:**

| Opción | Qué seleccionar |
|--------|-----------------|
| **Dispositivo** | Tu USB (ej: "Kingston 8GB") |
| **Selección de arranque** | `ubuntu-22.04-live-server-amd64.iso` |
| **Esquema de partición** | **MBR** |
| **Sistema de destino** | **BIOS (o UEFI-CSM)** (automático) |

```
┌─────────────────────────────────────────────────────────┐
│  RUFUS 4.x - Hardware Antiguo (pre-2012)                │
├─────────────────────────────────────────────────────────┤
│  Dispositivo:        [Kingston 8GB (F:)]           ▼   │
│  Selección arranque: [ubuntu-22.04...iso] [SELECCIONAR]│
│  Esquema partición:  [MBR]                         ▼   │ ← BIOS Legacy
│  Sistema de destino: [BIOS (o UEFI-CSM)]           ▼   │
│                                          [EMPEZAR]      │
└─────────────────────────────────────────────────────────┘
```

**¿Cómo saber si mi PC es UEFI o BIOS Legacy?**

| Usar GPT + UEFI si... | Usar MBR + BIOS si... |
|-----------------------|-----------------------|
| Portátil de 2012 o más reciente | Portátil anterior a 2012 (HP G42, etc.) |
| Tenía Windows 8, 10 u 11 | Tenía Windows 7 o XP |
| BIOS tiene opción "UEFI" | BIOS no menciona UEFI |

> **⚠️ Error común:** Si al intentar bootear aparece: *"it can boot in uefi mode only but you are trying to boot it in bios/legacy mode"*, significa que creaste la USB con GPT+UEFI pero tu PC solo soporta BIOS Legacy. Solución: recrear la USB con **MBR + BIOS**.

4. Click en **EMPEZAR**
5. Si pregunta modo de escritura → "Escribir en modo Imagen ISO" → **OK**
6. Confirmar que borrará la USB → **OK**
7. Esperar ~5-10 minutos hasta que diga "LISTO"
8. Cerrar Rufus y expulsar USB de forma segura

**En Linux:**
```bash
# Identificar la USB (ej: /dev/sdb)
lsblk

# Crear booteable (reemplazar /dev/sdX con tu USB)
sudo dd if=ubuntu-22.04-live-server-amd64.iso of=/dev/sdX bs=4M status=progress
```

#### 12.3.2 Arrancar desde USB

1. Insertar la USB en el portátil
2. Encender y entrar al **menú de boot**:
   - **HP:** Presionar **Esc** repetidamente al encender, luego **F9** para Boot Menu
   - **Otras marcas:** F12, F2, o Del (varía según fabricante)
   - Si no funciona, entrar a BIOS y cambiar orden de arranque
3. Seleccionar la USB como dispositivo de arranque

> **Nota:** Si no arranca, desactivar "Secure Boot" en BIOS (si existe la opción)

#### 12.3.3 Menú de Inicio del Instalador

Al bootear desde la USB aparecerá un menú con varias opciones:

```
┌────────────────────────────────────────────────────────────┐
│  GNU GRUB                                                   │
├────────────────────────────────────────────────────────────┤
│  Try or Install Ubuntu Server                               │
│  Ubuntu Server with the HWE kernel        ← SELECCIONAR     │
│  Test memory                                                │
│  Boot from next volume                                      │
└────────────────────────────────────────────────────────────┘
```

**Seleccionar:** `Ubuntu Server with the HWE kernel`
- HWE (Hardware Enablement) = mejor compatibilidad con hardware antiguo
- Tardará unos segundos en cargar, es normal

> **⚠️ Problema conocido:** Si usas Ubuntu 24.04 en hardware antiguo y se queda en "GRUB loading. Welcome to GRUB" sin avanzar, usa Ubuntu 22.04 LTS en su lugar.

#### 12.3.4 Proceso de Instalación Paso a Paso

**Pantalla 1: Idioma**
- Seleccionar: `English` (recomendado) o `Español`
- Presionar **Enter**

**Pantalla 2: Teclado**
- Layout: `Spanish` o `Spanish (Latin American)`
- Variant: Dejar por defecto
- Seleccionar **[ Done ]**

**Pantalla 3: Tipo de instalación**
- Seleccionar: `Ubuntu Server` (NO minimized)
- **[ Done ]**

**Pantalla 4: Configuración de Red**
```
┌─────────────────────────────────────────────────────────────┐
│  Network connections                                         │
├─────────────────────────────────────────────────────────────┤
│  NAME       TYPE      NOTES                                  │
│  eth0       ethernet  192.168.1.184/24  ← Tu IP asignada     │
└─────────────────────────────────────────────────────────────┘
```
- El sistema detectará automáticamente la red si hay cable conectado
- **Anotar la IP mostrada** (ej: 192.168.1.184) - la necesitarás para SSH
- Seleccionar **[ Done ]**

**Pantalla 5: Proxy**
- Dejar **vacío** (a menos que tu red requiera proxy)
- **[ Done ]**

**Pantalla 6: Mirror**
- Dejar el mirror por defecto (se detecta automáticamente)
- **[ Done ]**

**Pantalla 7: Almacenamiento (Storage configuration)**
```
┌─────────────────────────────────────────────────────────────┐
│  Guided storage configuration                                │
├─────────────────────────────────────────────────────────────┤
│  (X) Use an entire disk           ← SELECCIONAR              │
│  [ ] Set up this disk as an LVM group   ← NO MARCAR          │
│                                                              │
│  [ ] Encrypt the LVM group with LUKS    ← NO MARCAR          │
└─────────────────────────────────────────────────────────────┘
```
- Marcar: `Use an entire disk`
- **NO marcar** "Set up this disk as LVM group" (simplifica el manejo)
- Seleccionar el disco principal (ej: 500GB)
- **[ Done ]** → Confirmar que borrará todo el disco

**Pantalla 8: Configuración del Perfil**
```
┌─────────────────────────────────────────────────────────────┐
│  Profile setup                                               │
├─────────────────────────────────────────────────────────────┤
│  Your name: servidor                                         │
│  Your server's name: finanzas-server                         │
│  Pick a username: servidor                                   │
│  Choose a password: ********                                 │
│  Confirm your password: ********                             │
└─────────────────────────────────────────────────────────────┘
```

| Campo | Qué poner | Notas |
|-------|-----------|-------|
| Your name | `servidor` | Nombre descriptivo |
| Server's name | `finanzas-server` | Hostname del equipo |
| Username | `servidor` | Usuario para login y SSH |
| Password | Tu contraseña | Puede ser simple inicialmente, se cambia después con `passwd` |

> **💡 Tip:** El nombre de usuario `servidor` es genérico y útil si planeas hospedar varias apps. Puedes usar otro nombre si prefieres.

**Pantalla 9: Ubuntu Pro**
- Seleccionar: `Skip for now`
- **[ Continue ]**

**Pantalla 10: SSH Setup**
```
┌─────────────────────────────────────────────────────────────┐
│  SSH Setup                                                   │
├─────────────────────────────────────────────────────────────┤
│  [X] Install OpenSSH server        ← MARCAR                  │
│  [ ] Import SSH identity           ← Dejar sin marcar        │
└─────────────────────────────────────────────────────────────┘
```
- **Marcar:** `Install OpenSSH server` (esencial para administrar remotamente)
- Dejar sin marcar: Import SSH identity
- **[ Done ]**

**Pantalla 11: Featured Server Snaps**
```
┌─────────────────────────────────────────────────────────────┐
│  Featured Server Snaps                                       │
├─────────────────────────────────────────────────────────────┤
│  [ ] microk8s                                                │
│  [ ] nextcloud                                               │
│  [ ] docker                                                  │
│  [ ] wekan                                                   │
│  ...                                                         │
└─────────────────────────────────────────────────────────────┘
```
- **NO seleccionar nada** - instalaremos todo manualmente después
- Seleccionar **[ Done ]**

#### 12.3.5 Instalación en Progreso

Después de la última pantalla, el sistema comenzará a instalarse:
- Descargará paquetes de internet
- Instalará el sistema base
- Configurará el bootloader

```
┌─────────────────────────────────────────────────────────────┐
│  Installing system                                           │
├─────────────────────────────────────────────────────────────┤
│  ████████████████░░░░░░░░░░░░░░  45%                        │
│                                                              │
│  configuring apt...                                          │
└─────────────────────────────────────────────────────────────┘
```

Tiempo estimado: 10-20 minutos (depende de velocidad de internet y disco)

#### 12.3.6 Finalizar Instalación

1. Esperar a que termine (~10-20 min)
2. Cuando diga "Installation complete" → seleccionar **[ Reboot Now ]**
3. **IMPORTANTE:** Retirar la USB antes de que reinicie (cuando lo indique o cuando veas la pantalla oscura)

#### 12.3.7 Primer Inicio

Después de reiniciar aparecerá pantalla negra con texto:

```
finanzas-server login: servidor
Password: (tu contraseña, no se ve al escribir)
```

> **Nota:** La contraseña no muestra caracteres al escribir, es normal. Solo escríbela y presiona Enter.

**Primeros comandos a ejecutar:**
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Ver IP asignada (para conectar por SSH)
ip a
```

#### 12.3.8 Conectar por SSH (Recomendado)

Es más cómodo trabajar desde otro PC, permite copiar/pegar comandos:

**Desde Linux/Mac:**
```bash
ssh servidor@192.168.1.184  # Reemplazar con la IP de tu servidor
```

**Desde Windows (PowerShell o Git Bash):**
```bash
ssh servidor@192.168.1.184
```

**Desde Windows (PuTTY):**
1. Descargar PuTTY: https://putty.org
2. Host Name: `192.168.1.184` (tu IP)
3. Port: `22`
4. Click en "Open"

#### 12.3.9 Solución de Problemas de Instalación

| Problema | Causa | Solución |
|----------|-------|----------|
| Error "boot in uefi mode only..." | USB creada con GPT+UEFI en PC sin UEFI | Recrear USB con **MBR + BIOS** en Rufus |
| "GRUB loading" y se queda colgado | Ubuntu 24.04 incompatible con hardware antiguo | Usar **Ubuntu 22.04 LTS** en su lugar |
| Kernel Panic al iniciar | RAM incompatible (DDR3 mezclada con DDR3L) | Usar solo módulos del mismo tipo |
| No arranca desde USB | Secure Boot activo | Desactivar "Secure Boot" en BIOS |
| No detecta el disco duro | Modo SATA incorrecto | Cambiar de RAID a AHCI en BIOS |
| Pantalla se apaga/negro | Ahorro de energía | Presionar cualquier tecla (es normal) |
| Olvidé la IP del servidor | - | Ejecutar `ip a` directamente en el servidor |
| No conecta SSH | Diferentes redes | Verificar que ambos PC están en la misma red WiFi/cable |

**Sobre compatibilidad de RAM (DDR3 vs DDR3L):**
```
┌─────────────────────────────────────────────────────────────┐
│  ⚠️ NO MEZCLAR:                                             │
│                                                              │
│  DDR3   = 1.5V  (estándar)                                  │
│  DDR3L  = 1.35V (low voltage)                               │
│                                                              │
│  Mezclarlos causa: Kernel Panic, reinicios aleatorios,      │
│  errores de memoria, instalación fallida                     │
│                                                              │
│  Solución: Usar SOLO un tipo de RAM                         │
└─────────────────────────────────────────────────────────────┘
```

#### 12.3.10 Configuración de WiFi (Opcional)

Si prefieres usar WiFi en lugar de cable ethernet:

**1. Instalar NetworkManager (requiere conexión temporal por ethernet):**
```bash
sudo apt install network-manager -y
```

**2. Ver redes WiFi disponibles:**
```bash
sudo nmcli dev wifi list
```

**3. Conectar a WiFi:**
```bash
# Crear conexión WiFi (reemplazar SSID y contraseña)
sudo nmcli connection add type wifi con-name "MiWifi" ifname wlp2s0b1 ssid "NOMBRE_DE_TU_RED" wifi-sec.key-mgmt wpa-psk wifi-sec.psk "TU_CONTRASEÑA"

# Activar conexión
sudo nmcli connection up "MiWifi"
```

> **Nota:** El nombre de interfaz (`wlp2s0b1`) puede variar. Ver con `ip a`.

**4. Configurar DNS (si hay problemas de resolución):**
```bash
sudo nmcli connection modify "MiWifi" ipv4.dns "8.8.8.8 8.8.4.4"
sudo nmcli connection down "MiWifi" && sudo nmcli connection up "MiWifi"
```

**5. Verificar conexión:**
```bash
ping -c 3 google.com
```

**Conmutación automática Ethernet/WiFi:**
- NetworkManager prioriza ethernet automáticamente
- Al desconectar el cable, cambia a WiFi
- Al reconectar el cable, vuelve a ethernet
- No requiere configuración adicional

**Solución de problemas WiFi:**

| Error | Causa | Solución |
|-------|-------|----------|
| "Secrets were required, but not provided" | Password o SSID incorrecto | Usar `nmcli connection add` con todos los parámetros |
| "Temporary failure in name resolution" | DNS no configurado | Agregar DNS con `nmcli connection modify` |
| Interfaz en estado DOWN | Interfaz no activada | `sudo ip link set wlp2s0b1 up` |

#### 12.3.11 Configuración del Teclado

**Cambio temporal (solo sesión actual):**
```bash
sudo loadkeys es
```

**Cambio permanente:**
```bash
sudo dpkg-reconfigure keyboard-configuration
```
Seleccionar:
1. Generic 105-key PC
2. Spanish (o Spanish - Latin American)
3. Aceptar valores por defecto

Aplicar cambios:
```bash
sudo setupcon
```

---

### 12.4 Stack de Software

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
| Ubuntu Server | 22.04 LTS | Sistema operativo (usar 24.04 solo en hardware moderno) |
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

#### 12.8.2 Backup automático de base de datos (GitHub)

El backup se sube automáticamente a un repositorio privado de GitHub, así si el disco del servidor se daña, los datos están seguros en la nube.

**Configuración inicial (una sola vez):**
```bash
# 1. Crear repositorio privado en GitHub (ej: finanzas-backups)

# 2. Clonar el repositorio en el servidor
cd ~
git clone https://github.com/TU_USUARIO/finanzas-backups.git

# 3. Configurar git en el servidor
git config --global user.email "tu-email@ejemplo.com"
git config --global user.name "Tu Nombre"

# 4. Guardar credenciales (para que no pida password cada vez)
git config --global credential.helper store
# La primera vez que hagas push te pedirá usuario y token de GitHub
# Después de eso, las credenciales quedan guardadas
```

**Script de backup: `~/backup-db.sh`**
```bash
#!/bin/bash
FECHA=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR=~/finanzas-backups
DB_PATH=/var/www/finanzas/database/database.sqlite

cd $BACKUP_DIR
cp $DB_PATH database_$FECHA.sqlite
cp $DB_PATH database_latest.sqlite

git add .
git commit -m "Backup $FECHA"
git push origin main

# Mantener solo los últimos 30 backups locales
ls -t database_2*.sqlite | tail -n +31 | xargs -r rm

echo "✅ Backup completado: $FECHA"
```

```bash
# Dar permisos de ejecución
chmod +x ~/backup-db.sh

# Programar backup diario a las 3am
crontab -e
# Añadir esta línea:
0 3 * * * /home/david/backup-db.sh >> /home/david/backup.log 2>&1
```

**Verificar que funciona:**
```bash
# Ejecutar manualmente
~/backup-db.sh

# Verificar en GitHub que aparece el archivo
```

**Restaurar backup (si el servidor se daña):**
```bash
# En el nuevo servidor:
git clone https://github.com/TU_USUARIO/finanzas-backups.git
cp finanzas-backups/database_latest.sqlite /var/www/finanzas/database/database.sqlite
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

### 12.9 Datos del Servidor Actual

| Dato | Valor |
|------|-------|
| **Usuario** | `david` |
| **Hostname** | `homeserver` |
| **IP Local** | `192.168.1.182` |
| **Dominio DuckDNS** | `finanzas-david.duckdns.org` |
| **Conexión SSH** | `ssh david@192.168.1.182` |
| **Router** | TP-Link Archer C7 v5.0 |

---

### 12.10 Checklist de Despliegue

#### Pre-instalación
- [x] Descargar Ubuntu Server 22.04 LTS (o 24.04 para hardware moderno)
- [x] Crear USB booteable (MBR + BIOS para HP G42)
- [x] Anotar IP del router (para port forwarding) → `192.168.1.1`
- [x] Decidir IP estática para el servidor → `192.168.1.182`
- [x] Crear cuenta en DuckDNS y subdominio → `finanzas-david.duckdns.org`

#### Instalación del Sistema
- [x] Instalar Ubuntu Server (sin GUI)
- [x] Configurar WiFi (ver sección 12.3.10)
- [x] Configurar IP estática → `192.168.1.182`
- [x] Actualizar sistema (`apt update && apt upgrade`)
- [x] Configurar zona horaria → America/Bogota (por defecto en instalación)

#### Instalación del Stack ✅ (verificado 2025-12-28)
- [x] Instalar Nginx → `nginx/1.18.0`
- [x] Instalar PHP 8.3 + extensiones → `PHP 8.3.29`
- [x] Instalar Composer → `2.9.2`
- [x] Instalar Node.js 20 → `v20.19.6`
- [x] Instalar NPM → `10.8.2`
- [x] Instalar Git → `2.34.1`
- [x] Configurar UFW (firewall) → Activo (OpenSSH + Nginx Full)
- [x] Instalar Fail2ban → Activo y corriendo

#### Configuración de Red
- [x] Configurar port forwarding en router (80, 443) → TP-Link Archer C7
- [x] Configurar DuckDNS → Script en `~/duckdns/duck.sh` + crontab cada 5 min
- [x] ~~Verificar acceso desde internet~~ → ISP bloquea puertos 80/443, usar Cloudflare Tunnel

#### Despliegue de la Aplicación
- [x] Clonar/copiar código a `/var/www/finanzas`
- [x] Instalar dependencias (composer, npm) ✅ `composer install --no-dev && npm install && npm run build`
- [x] Configurar `.env` de producción
- [x] Ejecutar migraciones ✅ 14 migraciones ya ejecutadas
- [x] Configurar Nginx para el dominio ✅ `/etc/nginx/sites-available/finanzas`
- [x] Habilitar sitio Nginx y reiniciar ✅
- [x] Arreglar permisos (`chown david:www-data`, `chmod 775 storage`)
- [x] Cambiar SESSION_DRIVER a file (corregir error 500)

#### Acceso desde Internet (Cloudflare Tunnel) ✅
- [x] Comprar dominio → `davidhub.space` en Hostinger ($4,900 COP/año)
- [x] Agregar dominio a Cloudflare (plan Free)
- [x] Cambiar nameservers en Hostinger → `chuck.ns.cloudflare.com`, `gwen.ns.cloudflare.com`
- [x] Dominio activo en Cloudflare ✅
- [x] Instalar cloudflared en servidor
- [x] Autenticar cloudflared (`cloudflared tunnel login`)
- [x] Crear túnel → `cloudflared tunnel create finanzas` (ID: 490bf84b-45b4-47af-bc64-f750b6372f88)
- [x] Configurar ruta DNS → `cloudflared tunnel route dns finanzas finanzas.davidhub.space`
- [x] Crear config.yml en `~/.cloudflared/` y `/etc/cloudflared/`
- [x] Ejecutar túnel como servicio (systemd) → `sudo cloudflared service install`
- [x] Actualizar APP_URL a `https://finanzas.davidhub.space`
- [x] **App accesible desde internet** → https://finanzas.davidhub.space ✅

#### Configuración del Portátil
- [x] Deshabilitar suspensión al cerrar tapa → `/etc/systemd/logind.conf` (HandleLidSwitch=ignore)
- [ ] Configurar arranque tras corte de luz (BIOS) → Opcional, hacer manualmente en BIOS
- [x] Instalar sensores de temperatura → `lm-sensors` (Core 0: 31°C, Core 2: 39°C)

#### Post-despliegue ✅
- [x] Crear script de deploy → `/var/www/finanzas/deploy.sh`
- [x] Configurar backup automático → GitHub (`finanzas-backups` repo) + crontab 3am
- [x] Probar acceso desde móvil (red móvil, no WiFi) ✅
- [x] Documentar proceso de Cloudflare Tunnel ✅

---

### 12.11 Cloudflare Tunnel - Guía Completa

Cloudflare Tunnel permite exponer tu servidor a internet **sin abrir puertos en el router**. Ideal cuando el ISP bloquea puertos 80/443.

#### ¿Por qué Cloudflare Tunnel?
- ISP bloquea puertos 80 y 443 (común en Colombia/Latinoamérica)
- No requiere IP pública fija
- SSL/HTTPS automático y gratuito
- Protección DDoS incluida
- El túnel sale desde tu servidor → Cloudflare (conexión saliente, no entrante)

#### Requisitos
1. Un dominio propio (puede ser barato, ej: `.space` por ~$5.000 COP/año en Hostinger)
2. Cuenta gratuita en Cloudflare
3. Servidor con acceso a internet

#### Paso 1: Comprar dominio y agregar a Cloudflare
```
1. Comprar dominio en cualquier registrador (Hostinger, Namecheap, GoDaddy, etc.)
2. Crear cuenta en https://dash.cloudflare.com (plan Free)
3. Agregar dominio a Cloudflare → te dará 2 nameservers
4. Cambiar nameservers en el registrador por los de Cloudflare
5. Esperar activación (~5-30 minutos)
```

#### Paso 2: Instalar cloudflared en el servidor
```bash
# Descargar e instalar cloudflared
curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared.deb

# Verificar instalación
cloudflared --version
```

#### Paso 3: Autenticar con Cloudflare
```bash
cloudflared tunnel login
# Se abre URL en el navegador → seleccionar el dominio → Authorize
# Esto crea ~/.cloudflared/cert.pem
```

#### Paso 4: Crear el túnel
```bash
# Crear túnel (guarda el ID que te da)
cloudflared tunnel create finanzas
# Output: Created tunnel finanzas with id XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX

# Configurar DNS (subdominio que quieras usar)
cloudflared tunnel route dns finanzas finanzas.tudominio.com
```

#### Paso 5: Crear archivo de configuración
```bash
nano ~/.cloudflared/config.yml
```

Contenido:
```yaml
tunnel: XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX  # Tu ID del túnel
credentials-file: /home/TU_USUARIO/.cloudflared/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX.json

ingress:
  - hostname: finanzas.tudominio.com
    service: http://localhost:80
  - service: http_status:404
```

#### Paso 6: Probar el túnel
```bash
cloudflared tunnel run finanzas
# Debería conectar y mostrar logs
# Probar acceso en https://finanzas.tudominio.com
```

#### Paso 7: Configurar como servicio (arranque automático)
```bash
# Copiar archivos a /etc/cloudflared/
sudo mkdir -p /etc/cloudflared
sudo cp ~/.cloudflared/config.yml /etc/cloudflared/
sudo cp ~/.cloudflared/*.json /etc/cloudflared/

# Actualizar rutas en config.yml de /etc/cloudflared/
sudo nano /etc/cloudflared/config.yml
# Cambiar credentials-file a: /etc/cloudflared/XXXXX.json

# Instalar servicio
sudo cloudflared service install

# Verificar que está corriendo
sudo systemctl status cloudflared
```

#### Paso 8: Actualizar APP_URL en Laravel
```bash
# Editar .env
nano /var/www/finanzas/.env
# Cambiar: APP_URL=https://finanzas.tudominio.com

# Limpiar caché
cd /var/www/finanzas
php artisan config:clear
php artisan cache:clear
```

#### Comandos útiles de cloudflared
```bash
# Ver túneles existentes
cloudflared tunnel list

# Ver estado del servicio
sudo systemctl status cloudflared

# Ver logs del túnel
sudo journalctl -u cloudflared -f

# Reiniciar túnel
sudo systemctl restart cloudflared

# Eliminar un túnel (si necesitas recrearlo)
cloudflared tunnel delete nombre-del-tunel
```

#### Datos del túnel actual
| Dato | Valor |
|------|-------|
| **Túnel ID** | `490bf84b-45b4-47af-bc64-f750b6372f88` |
| **Nombre** | `finanzas` |
| **Dominio** | `davidhub.space` |
| **Subdominio** | `finanzas.davidhub.space` |
| **URL de la app** | `https://finanzas.davidhub.space` |
| **Registrador** | Hostinger |
| **Costo dominio** | $4,900 COP/año |
| **Nameservers** | `chuck.ns.cloudflare.com`, `gwen.ns.cloudflare.com` |

---

### 12.12 Resolución de Problemas Comunes

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

---

## 13. Glosario y Resumen del Despliegue

### Herramientas Instaladas

| Herramienta | ¿Qué es? | ¿Para qué la usamos? |
|-------------|----------|----------------------|
| **Ubuntu Server** | Sistema operativo Linux sin interfaz gráfica | Base del servidor, consume poca RAM |
| **Nginx** | Servidor web | Recibe peticiones HTTP/HTTPS y las envía a PHP |
| **PHP-FPM** | Procesador de PHP | Ejecuta el código Laravel |
| **Composer** | Gestor de dependencias PHP | Instala librerías de Laravel |
| **Node.js/NPM** | Runtime JavaScript | Compila los assets de Vue (CSS, JS) |
| **Git** | Control de versiones | Clona y actualiza el código desde GitHub |
| **UFW** | Firewall | Bloquea puertos no autorizados (solo permite 22, 80, 443) |
| **Fail2ban** | Protección anti-ataques | Bloquea IPs que intentan acceso por fuerza bruta |
| **Certbot** | Cliente SSL | Genera certificados HTTPS gratuitos de Let's Encrypt |
| **DuckDNS** | DNS dinámico | Asocia un dominio gratuito a tu IP pública |
| **Cloudflare** | CDN y DNS | Gestiona el dominio, SSL automático, protección DDoS |
| **cloudflared** | Cliente de Cloudflare Tunnel | Crea túnel seguro servidor→Cloudflare (evita abrir puertos) |
| **Crontab** | Programador de tareas | Ejecuta scripts automáticamente (backups, DuckDNS) |
| **systemd** | Gestor de servicios | Mantiene servicios corriendo y los reinicia si fallan |
| **lm-sensors** | Monitor de hardware | Muestra temperaturas del CPU y otros sensores |

### Procesos Ejecutados

| Proceso | ¿Qué hace? | ¿Por qué? |
|---------|------------|-----------|
| `apt update && upgrade` | Actualiza paquetes del sistema | Seguridad y estabilidad |
| `composer install --no-dev` | Instala dependencias PHP de producción | Laravel necesita sus librerías |
| `npm install && npm run build` | Compila Vue/CSS/JS | Genera archivos optimizados para producción |
| `php artisan migrate` | Crea tablas en la base de datos | La app necesita estructura de datos |
| `php artisan config:cache` | Cachea configuración | Mejora rendimiento en producción |
| `chown www-data:www-data` | Cambia propietario de archivos | Nginx/PHP necesitan acceso a los archivos |
| `chmod 775 storage` | Ajusta permisos | Laravel necesita escribir logs y caché |
| Configurar Nginx | Crea virtual host | Conecta el dominio con la carpeta de la app |
| Port forwarding en router | Redirige puertos 80/443 | Permite acceso desde internet |
| Crontab DuckDNS | Actualiza IP cada 5 min | Mantiene el dominio apuntando a tu IP |

### Flujo de una Petición (con Cloudflare Tunnel)

```
Usuario escribe finanzas.davidhub.space
              ↓
    DNS de Cloudflare resuelve el dominio
              ↓
    Cloudflare recibe la petición (HTTPS automático)
              ↓
    Cloudflare envía por el túnel al servidor
              ↓
    cloudflared (en el servidor) recibe y pasa a Nginx
              ↓
    Nginx procesa y envía a PHP-FPM
              ↓
    PHP-FPM ejecuta Laravel
              ↓
    Laravel consulta SQLite y devuelve respuesta
              ↓
    Respuesta viaja de vuelta por el túnel
              ↓
    Usuario ve la página
```

**Ventaja del túnel:** No necesitas abrir puertos en el router. El servidor inicia la conexión hacia Cloudflare (saliente), no al revés.

### Archivos Clave

| Archivo/Directorio | Propósito |
|--------------------|-----------|
| `/var/www/finanzas/` | Código de la aplicación |
| `/var/www/finanzas/.env` | Configuración (BD, claves, APP_URL, etc.) |
| `/var/www/finanzas/database/database.sqlite` | Base de datos SQLite |
| `/var/www/finanzas/deploy.sh` | Script para actualizar la app |
| `/etc/nginx/sites-available/finanzas` | Configuración de Nginx |
| `/etc/cloudflared/config.yml` | Configuración del túnel Cloudflare |
| `/etc/cloudflared/*.json` | Credenciales del túnel |
| `~/backup-db.sh` | Script de backup a GitHub |
| `~/finanzas-backups/` | Repositorio local de backups |
| `~/duckdns/duck.sh` | Script que actualiza DuckDNS (legacy) |
| `/etc/systemd/logind.conf` | Configuración para no suspender al cerrar tapa |

### Comandos Útiles de Mantenimiento

```bash
# Conectar al servidor
ssh david@192.168.1.182

# Ver estado de servicios
sudo systemctl status nginx php8.3-fpm cloudflared fail2ban

# Actualizar la aplicación (método rápido)
cd /var/www/finanzas && ./deploy.sh

# Actualizar manualmente (si deploy.sh no existe)
cd /var/www/finanzas && git pull && composer install --no-dev && npm run build

# Ver logs de errores de Laravel
tail -f /var/www/finanzas/storage/logs/laravel.log

# Ver logs del túnel Cloudflare
sudo journalctl -u cloudflared -f

# Reiniciar servicios si hay problemas
sudo systemctl restart nginx php8.3-fpm cloudflared

# Ejecutar backup manualmente
~/backup-db.sh

# Ver tareas programadas (crontab)
crontab -l
```

### Glosario de Términos

| Término | Significado |
|---------|-------------|
| **SSH** | Secure Shell - conexión remota segura al servidor |
| **DNS** | Domain Name System - traduce nombres de dominio a IPs |
| **Nameservers** | Servidores que responden consultas DNS de tu dominio |
| **Túnel** | Conexión encriptada que pasa tráfico a través de Cloudflare |
| **SSL/HTTPS** | Cifrado de la conexión (candadito verde en el navegador) |
| **Crontab** | Archivo que define tareas programadas en Linux |
| **systemd** | Sistema que gestiona servicios en Linux moderno |
| **FPM** | FastCGI Process Manager - ejecuta PHP eficientemente |
| **Token** | Clave secreta para autenticación (GitHub, API, etc.) |
| **Repositorio** | Carpeta versionada con Git (local o en GitHub) |
| **ISP** | Internet Service Provider - tu proveedor de internet |
| **Port Forwarding** | Redirigir puertos del router a un dispositivo interno |
| **CDN** | Content Delivery Network - red que distribuye contenido |

### Resumen en Una Frase

> **Convertimos un portátil viejo en un servidor web que ejecuta una aplicación Laravel accesible desde internet mediante Cloudflare Tunnel (sin abrir puertos), con dominio propio, backup automático a GitHub, y protegido con firewall.**

### URLs Importantes

| Recurso | URL |
|---------|-----|
| **La App** | https://finanzas.davidhub.space |
| **Dashboard Cloudflare** | https://dash.cloudflare.com |
| **Repo de la App** | https://github.com/TU_USUARIO/finanzas |
| **Repo de Backups** | https://github.com/ROBOCOP3PK/finanzas-backups |
| **Hostinger (dominio)** | https://hpanel.hostinger.com |
