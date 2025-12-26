# Finanzas Compartidas - Guía de Implementación

## 1. Descripción General

**Nombre del proyecto:** Finanzas Compartidas
**Tecnologías:** Laravel 11 + Vue 3 + Tailwind CSS + SQLite
**Tipo:** PWA (Progressive Web App)
**Propósito:** Gestionar gastos compartidos entre dos personas, calculando automáticamente el saldo pendiente considerando gastos individuales y gastos de casa con porcentajes configurables.

---

## 2. Reglas de Negocio

### 2.1 Personas
- El sistema maneja exactamente **2 personas**
- Los nombres son configurables desde la aplicación
- Una persona es el "deudor" (generalmente debe dinero)
- La otra persona es el "pagador" (quien cubre la mayoría de gastos)

### 2.2 Tipos de Gasto
| Tipo | Descripción | Cálculo |
|------|-------------|---------|
| **Persona 1** | Gasto exclusivo de persona 1 | 100% a persona 1 |
| **Persona 2** | Gasto exclusivo de persona 2 | 100% a persona 2 |
| **Casa** | Gasto compartido del hogar | Se divide según porcentajes configurados |

### 2.3 División de Gastos de Casa
- Por defecto: **40% Persona 1** / **60% Persona 2**
- Los porcentajes son configurables
- Siempre deben sumar 100%

### 2.4 Medios de Pago
Los medios de pago son **administrables** desde un módulo de configuración:
- El usuario puede **añadir**, **editar** y **eliminar** medios de pago
- Cada medio de pago tiene: nombre, icono (opcional) y estado (activo/inactivo)
- No se puede eliminar un medio de pago que tenga gastos asociados (solo desactivar)
- Medios de pago por defecto (creados con seeder):
  1. Davivienda Crédito
  2. Daviplata
  3. Nequi
  4. Efectivo

### 2.5 Abonos
- Solo la **Persona 1** realiza abonos (es quien debe)
- Los abonos reducen el saldo pendiente
- Se registra fecha, valor y nota opcional

### 2.6 Cálculo del Saldo Pendiente
```
Saldo Persona 1 =
    + Suma de gastos tipo "persona_1"
    + Suma de (gastos tipo "casa" * porcentaje_persona_1)
    - Suma de abonos realizados
```

---

## 3. Arquitectura del Sistema

### 3.1 Estructura de Carpetas (Laravel + Vue)
```
finanzas/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── GastoController.php
│   │   │   ├── AbonoController.php
│   │   │   ├── MedioPagoController.php
│   │   │   ├── ConfiguracionController.php
│   │   │   └── DashboardController.php
│   │   └── Requests/
│   │       ├── GastoRequest.php
│   │       ├── AbonoRequest.php
│   │       └── MedioPagoRequest.php
│   └── Models/
│       ├── Gasto.php
│       ├── Abono.php
│       ├── MedioPago.php
│       └── Configuracion.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_medios_pago_table.php
│   │   ├── 2024_01_01_000002_create_gastos_table.php
│   │   ├── 2024_01_01_000003_create_abonos_table.php
│   │   └── 2024_01_01_000004_create_configuraciones_table.php
│   └── seeders/
│       ├── MedioPagoSeeder.php
│       └── ConfiguracionSeeder.php
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   ├── Components/
│   │   │   ├── Layout/
│   │   │   │   ├── AppLayout.vue
│   │   │   │   ├── NavBar.vue
│   │   │   │   └── BottomNav.vue
│   │   │   ├── Gastos/
│   │   │   │   ├── GastoForm.vue
│   │   │   │   ├── GastoList.vue
│   │   │   │   └── GastoItem.vue
│   │   │   ├── Abonos/
│   │   │   │   ├── AbonoForm.vue
│   │   │   │   └── AbonoList.vue
│   │   │   ├── MediosPago/
│   │   │   │   ├── MedioPagoForm.vue
│   │   │   │   ├── MedioPagoList.vue
│   │   │   │   └── MedioPagoItem.vue
│   │   │   ├── Dashboard/
│   │   │   │   ├── SaldoCard.vue
│   │   │   │   ├── ResumenMes.vue
│   │   │   │   └── UltimosMovimientos.vue
│   │   │   └── UI/
│   │   │       ├── Button.vue
│   │   │       ├── Input.vue
│   │   │       ├── Select.vue
│   │   │       └── Modal.vue
│   │   ├── Pages/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Gastos/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   └── Edit.vue
│   │   │   ├── Abonos/
│   │   │   │   ├── Index.vue
│   │   │   │   └── Create.vue
│   │   │   ├── Historial.vue
│   │   │   └── Configuracion.vue
│   │   ├── Stores/
│   │   │   ├── gastos.js
│   │   │   ├── abonos.js
│   │   │   ├── mediosPago.js
│   │   │   └── config.js
│   │   └── router.js
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── public/
│   ├── manifest.json
│   └── sw.js
└── .env
```

---

## 4. Base de Datos

### 4.1 Diagrama Entidad-Relación
```
┌─────────────────────┐
│    configuraciones  │
├─────────────────────┤
│ id                  │
│ clave (unique)      │
│ valor               │
│ created_at          │
│ updated_at          │
└─────────────────────┘

┌─────────────────────┐
│    medios_pago      │
├─────────────────────┤
│ id                  │
│ nombre              │
│ icono (nullable)    │
│ activo              │
│ orden               │
│ created_at          │
│ updated_at          │
└─────────────────────┘

┌─────────────────────┐       ┌─────────────────────┐
│       gastos        │       │    medios_pago      │
├─────────────────────┤       ├─────────────────────┤
│ id                  │       │                     │
│ fecha               │       │                     │
│ medio_pago_id (FK)──┼──────►│ id                  │
│ concepto            │       │                     │
│ valor               │       │                     │
│ tipo                │       │                     │
│ created_at          │       │                     │
│ updated_at          │       │                     │
└─────────────────────┘       └─────────────────────┘

┌─────────────────────┐
│       abonos        │
├─────────────────────┤
│ id                  │
│ fecha               │
│ valor               │
│ nota (nullable)     │
│ created_at          │
│ updated_at          │
└─────────────────────┘
```

### 4.2 Tabla: `medios_pago`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(100) | NOT NULL | Nombre del medio de pago |
| icono | VARCHAR(50) | NULLABLE | Nombre del icono (ej: credit-card, wallet) |
| activo | BOOLEAN | NOT NULL, DEFAULT TRUE | Si está disponible para usar |
| orden | INTEGER | NOT NULL, DEFAULT 0 | Orden de aparición en listas |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_medios_pago_activo` en columna `activo`
- `idx_medios_pago_orden` en columna `orden`

**Datos iniciales (Seeder):**
| nombre | icono | activo | orden |
|--------|-------|--------|-------|
| Davivienda Crédito | credit-card | true | 1 |
| Daviplata | wallet | true | 2 |
| Nequi | wallet | true | 3 |
| Efectivo | banknotes | true | 4 |

### 4.3 Tabla: `gastos`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| fecha | DATE | NOT NULL | Fecha del gasto |
| medio_pago_id | BIGINT | NOT NULL, FK → medios_pago.id | Referencia al medio de pago |
| concepto | VARCHAR(255) | NOT NULL | Descripción del gasto |
| valor | DECIMAL(12,2) | NOT NULL, UNSIGNED | Monto del gasto |
| tipo | VARCHAR(20) | NOT NULL | Enum: persona_1, persona_2, casa |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_gastos_fecha` en columna `fecha`
- `idx_gastos_tipo` en columna `tipo`
- `idx_gastos_medio_pago_id` en columna `medio_pago_id`

### 4.4 Tabla: `abonos`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| fecha | DATE | NOT NULL | Fecha del abono |
| valor | DECIMAL(12,2) | NOT NULL, UNSIGNED | Monto del abono |
| nota | VARCHAR(255) | NULLABLE | Nota opcional |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_abonos_fecha` en columna `fecha`

### 4.5 Tabla: `configuraciones`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| clave | VARCHAR(50) | NOT NULL, UNIQUE | Clave de configuración |
| valor | VARCHAR(255) | NOT NULL | Valor de configuración |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Configuraciones predeterminadas (Seeder):**
| Clave | Valor por defecto | Descripción |
|-------|-------------------|-------------|
| nombre_persona_1 | Persona 1 | Nombre de la persona 1 |
| nombre_persona_2 | Persona 2 | Nombre de la persona 2 |
| porcentaje_persona_1 | 40 | % de gastos casa para persona 1 |
| porcentaje_persona_2 | 60 | % de gastos casa para persona 2 |

---

## 5. Modelos Eloquent

### 5.1 Modelo: MedioPago
```php
// app/Models/MedioPago.php

class MedioPago extends Model
{
    protected $table = 'medios_pago';

    protected $fillable = [
        'nombre',
        'icono',
        'activo',
        'orden'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    // Relaciones
    public function gastos()
    {
        return $this->hasMany(Gasto::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    // Verificar si puede eliminarse
    public function puedeEliminarse()
    {
        return $this->gastos()->count() === 0;
    }
}
```

### 5.2 Modelo: Gasto
```php
// app/Models/Gasto.php

class Gasto extends Model
{
    protected $fillable = [
        'fecha',
        'medio_pago_id',
        'concepto',
        'valor',
        'tipo'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];

    // Constantes para tipos
    const TIPO_PERSONA_1 = 'persona_1';
    const TIPO_PERSONA_2 = 'persona_2';
    const TIPO_CASA = 'casa';

    const TIPOS = [
        self::TIPO_PERSONA_1,
        self::TIPO_PERSONA_2,
        self::TIPO_CASA
    ];

    // Relaciones
    public function medioPago()
    {
        return $this->belongsTo(MedioPago::class);
    }

    // Scopes
    public function scopeFecha($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeMedioPago($query, $medioPagoId)
    {
        return $query->where('medio_pago_id', $medioPagoId);
    }
}
```

### 5.3 Modelo: Abono
```php
// app/Models/Abono.php

class Abono extends Model
{
    protected $fillable = [
        'fecha',
        'valor',
        'nota'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];

    // Scopes
    public function scopeFecha($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }
}
```

### 5.4 Modelo: Configuracion
```php
// app/Models/Configuracion.php

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor'
    ];

    // Método estático para obtener valor
    public static function obtener($clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }

    // Método estático para establecer valor
    public static function establecer($clave, $valor)
    {
        return self::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor]
        );
    }

    // Obtener todas las configuraciones como array
    public static function todas()
    {
        return self::pluck('valor', 'clave')->toArray();
    }
}
```

---

## 6. API REST

### 6.1 Endpoints

#### Medios de Pago
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/medios-pago` | Listar todos los medios de pago | `?activos=true` (opcional, solo activos) |
| POST | `/api/medios-pago` | Crear nuevo medio de pago | `{nombre, icono, activo, orden}` |
| GET | `/api/medios-pago/{id}` | Obtener medio de pago específico | - |
| PUT | `/api/medios-pago/{id}` | Actualizar medio de pago | `{nombre, icono, activo, orden}` |
| DELETE | `/api/medios-pago/{id}` | Eliminar medio de pago | Solo si no tiene gastos |
| PUT | `/api/medios-pago/reordenar` | Reordenar medios de pago | `{orden: [id1, id2, ...]}` |

#### Gastos
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/gastos` | Listar gastos con filtros | `?desde=&hasta=&tipo=&medio_pago_id=&page=` |
| POST | `/api/gastos` | Crear nuevo gasto | `{fecha, medio_pago_id, concepto, valor, tipo}` |
| GET | `/api/gastos/{id}` | Obtener gasto específico | - |
| PUT | `/api/gastos/{id}` | Actualizar gasto | `{fecha, medio_pago_id, concepto, valor, tipo}` |
| DELETE | `/api/gastos/{id}` | Eliminar gasto | - |

#### Abonos
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/abonos` | Listar abonos | `?desde=&hasta=&page=` |
| POST | `/api/abonos` | Crear nuevo abono | `{fecha, valor, nota}` |
| GET | `/api/abonos/{id}` | Obtener abono específico | - |
| PUT | `/api/abonos/{id}` | Actualizar abono | `{fecha, valor, nota}` |
| DELETE | `/api/abonos/{id}` | Eliminar abono | - |

#### Dashboard
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/dashboard` | Datos del dashboard | - |
| GET | `/api/dashboard/saldo` | Solo saldo pendiente | - |
| GET | `/api/dashboard/resumen-mes` | Resumen del mes actual | `?mes=&año=` |

#### Configuración
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/configuracion` | Obtener toda la config | - |
| PUT | `/api/configuracion` | Actualizar configuración | `{clave: valor, ...}` |

#### Exportación
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/exportar/excel` | Exportar a Excel | `?desde=&hasta=&tipo=&medio_pago=` |

### 6.2 Respuestas API

#### Estructura de respuesta exitosa
```json
{
    "success": true,
    "data": { ... },
    "message": "Operación exitosa"
}
```

#### Estructura de respuesta con paginación
```json
{
    "success": true,
    "data": [ ... ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100
    }
}
```

#### Estructura de respuesta de error
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {
        "campo": ["Error específico del campo"]
    }
}
```

### 6.3 Respuesta Dashboard
```json
{
    "success": true,
    "data": {
        "saldo_pendiente": 150000.00,
        "configuracion": {
            "nombre_persona_1": "Laura",
            "nombre_persona_2": "David",
            "porcentaje_persona_1": 40,
            "porcentaje_persona_2": 60
        },
        "resumen_mes": {
            "total_gastos": 500000.00,
            "gastos_persona_1": 120000.00,
            "gastos_persona_2": 200000.00,
            "gastos_casa": 180000.00,
            "total_abonos": 50000.00
        },
        "por_medio_pago": {
            "davivienda_credito": 200000.00,
            "daviplata": 150000.00,
            "nequi": 100000.00,
            "efectivo": 50000.00
        },
        "ultimos_movimientos": [ ... ]
    }
}
```

---

## 7. Controladores

### 7.1 GastoController
```php
// app/Http/Controllers/GastoController.php

class GastoController extends Controller
{
    // GET /api/gastos
    public function index(Request $request)
    {
        // Filtros: desde, hasta, tipo, medio_pago
        // Paginación: 20 por página
        // Ordenar por fecha DESC
    }

    // POST /api/gastos
    public function store(GastoRequest $request)
    {
        // Validar y crear gasto
    }

    // GET /api/gastos/{id}
    public function show(Gasto $gasto)
    {
        // Retornar gasto
    }

    // PUT /api/gastos/{id}
    public function update(GastoRequest $request, Gasto $gasto)
    {
        // Validar y actualizar
    }

    // DELETE /api/gastos/{id}
    public function destroy(Gasto $gasto)
    {
        // Eliminar gasto
    }
}
```

### 7.2 DashboardController
```php
// app/Http/Controllers/DashboardController.php

class DashboardController extends Controller
{
    // GET /api/dashboard
    public function index()
    {
        // Calcular saldo pendiente
        // Resumen del mes
        // Últimos movimientos
    }

    // GET /api/dashboard/saldo
    public function saldo()
    {
        // Solo retorna el saldo calculado
    }

    // Método privado para calcular saldo
    private function calcularSaldoPendiente()
    {
        $config = Configuracion::todas();
        $porcentaje1 = $config['porcentaje_persona_1'] / 100;

        $gastosPersona1 = Gasto::tipo('persona_1')->sum('valor');
        $gastosCasa = Gasto::tipo('casa')->sum('valor');
        $totalAbonos = Abono::sum('valor');

        $saldo = $gastosPersona1 + ($gastosCasa * $porcentaje1) - $totalAbonos;

        return round($saldo, 2);
    }
}
```

---

## 8. Validaciones (Form Requests)

### 8.1 GastoRequest
```php
// app/Http/Requests/GastoRequest.php

class GastoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'fecha' => 'required|date',
            'medio_pago_id' => 'required|exists:medios_pago,id',
            'concepto' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:persona_1,persona_2,casa'
        ];
    }

    public function messages()
    {
        return [
            'fecha.required' => 'La fecha es obligatoria',
            'medio_pago_id.required' => 'Selecciona un medio de pago',
            'medio_pago_id.exists' => 'Medio de pago no válido',
            'concepto.required' => 'El concepto es obligatorio',
            'valor.required' => 'El valor es obligatorio',
            'valor.min' => 'El valor debe ser mayor a 0',
            'tipo.required' => 'Selecciona a quién corresponde el gasto',
            'tipo.in' => 'Tipo de gasto no válido'
        ];
    }
}
```

### 8.2 MedioPagoRequest
```php
// app/Http/Requests/MedioPagoRequest.php

class MedioPagoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'icono' => 'nullable|string|max:50',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres'
        ];
    }
}
```

### 8.3 AbonoRequest
```php
// app/Http/Requests/AbonoRequest.php

class AbonoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'fecha' => 'required|date',
            'valor' => 'required|numeric|min:0.01',
            'nota' => 'nullable|string|max:255'
        ];
    }
}
```

---

## 9. Frontend (Vue 3)

### 9.1 Stores (Pinia)

#### Store de Gastos
```javascript
// resources/js/Stores/gastos.js

import { defineStore } from 'pinia'
import axios from 'axios'

export const useGastosStore = defineStore('gastos', {
    state: () => ({
        gastos: [],
        loading: false,
        error: null,
        meta: {
            current_page: 1,
            last_page: 1,
            total: 0
        },
        filtros: {
            desde: null,
            hasta: null,
            tipo: null,
            medio_pago: null
        }
    }),

    actions: {
        async fetchGastos(page = 1) { ... },
        async crearGasto(data) { ... },
        async actualizarGasto(id, data) { ... },
        async eliminarGasto(id) { ... },
        setFiltros(filtros) { ... }
    }
})
```

#### Store de Medios de Pago
```javascript
// resources/js/Stores/mediosPago.js

import { defineStore } from 'pinia'
import axios from 'axios'

export const useMediosPagoStore = defineStore('mediosPago', {
    state: () => ({
        mediosPago: [],
        loading: false,
        error: null
    }),

    getters: {
        // Solo medios de pago activos (para formularios)
        activos: (state) => state.mediosPago.filter(mp => mp.activo),

        // Todos los medios de pago (para administración)
        todos: (state) => state.mediosPago,

        // Obtener por ID
        porId: (state) => (id) => state.mediosPago.find(mp => mp.id === id)
    },

    actions: {
        async cargarMediosPago(soloActivos = false) {
            this.loading = true
            const params = soloActivos ? { activos: true } : {}
            const response = await axios.get('/api/medios-pago', { params })
            this.mediosPago = response.data.data
            this.loading = false
        },

        async crearMedioPago(data) {
            const response = await axios.post('/api/medios-pago', data)
            this.mediosPago.push(response.data.data)
            return response.data.data
        },

        async actualizarMedioPago(id, data) {
            const response = await axios.put(`/api/medios-pago/${id}`, data)
            const index = this.mediosPago.findIndex(mp => mp.id === id)
            if (index !== -1) {
                this.mediosPago[index] = response.data.data
            }
            return response.data.data
        },

        async eliminarMedioPago(id) {
            await axios.delete(`/api/medios-pago/${id}`)
            this.mediosPago = this.mediosPago.filter(mp => mp.id !== id)
        },

        async reordenar(orden) {
            await axios.put('/api/medios-pago/reordenar', { orden })
            await this.cargarMediosPago()
        }
    }
})
```

#### Store de Configuración
```javascript
// resources/js/Stores/config.js

import { defineStore } from 'pinia'

export const useConfigStore = defineStore('config', {
    state: () => ({
        nombre_persona_1: 'Persona 1',
        nombre_persona_2: 'Persona 2',
        porcentaje_persona_1: 40,
        porcentaje_persona_2: 60,
        loaded: false
    }),

    getters: {
        tiposGasto: (state) => [
            { value: 'persona_1', label: state.nombre_persona_1 },
            { value: 'persona_2', label: state.nombre_persona_2 },
            { value: 'casa', label: 'Casa' }
        ]
    },

    actions: {
        async cargarConfiguracion() { ... },
        async guardarConfiguracion(data) { ... }
    }
})
```

### 9.2 Páginas principales

#### Dashboard.vue
```
Componentes:
- SaldoCard: Muestra saldo pendiente de persona 1 (destacado, grande)
- ResumenMes: Gastos del mes por tipo y medio de pago
- UltimosMovimientos: Lista de últimos 10 movimientos

Funcionalidades:
- Auto-refresh cada 30 segundos (opcional)
- Pull-to-refresh en móvil
```

#### Gastos/Create.vue
```
Campos del formulario:
- Fecha (date picker, default: hoy)
- Medio de pago (select)
- Concepto (input text)
- Valor (input number con formato moneda)
- Tipo/¿De quién? (3 botones: Persona1, Persona2, Casa)

Características:
- Validación en tiempo real
- Botón de guardar habilitado solo si es válido
- Feedback visual al guardar (toast/snackbar)
- Después de guardar: limpiar formulario y mostrar opción de ver dashboard
```

#### Historial.vue
```
Funcionalidades:
- Lista de todos los gastos y abonos
- Filtros:
  - Rango de fechas (date range picker)
  - Tipo (multiselect)
  - Medio de pago (multiselect)
- Ordenar por fecha (asc/desc)
- Scroll infinito o paginación
- Swipe para editar/eliminar (móvil)
- Botón exportar a Excel
```

#### Configuracion.vue
```
Secciones:

1. Personas y Porcentajes
   - Nombre persona 1 (input text)
   - Nombre persona 2 (input text)
   - Porcentaje persona 1 (slider o input 0-100)
   - Porcentaje persona 2 (calculado automáticamente)

2. Medios de Pago (Módulo administrable)
   - Lista de medios de pago existentes (drag & drop para reordenar)
   - Cada item muestra: icono, nombre, estado (activo/inactivo)
   - Botón para añadir nuevo medio de pago
   - Acciones por item: editar, activar/desactivar, eliminar
   - Al eliminar: confirmar si no tiene gastos, o solo desactivar si tiene gastos

3. Estadísticas
   - Total de gastos registrados
   - Total de abonos registrados
   - Opción para respaldar/exportar datos
```

### 9.3 Rutas Vue Router
```javascript
// resources/js/router.js

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('./Pages/Dashboard.vue')
    },
    {
        path: '/gastos',
        name: 'gastos',
        component: () => import('./Pages/Gastos/Index.vue')
    },
    {
        path: '/gastos/nuevo',
        name: 'gastos.create',
        component: () => import('./Pages/Gastos/Create.vue')
    },
    {
        path: '/gastos/:id/editar',
        name: 'gastos.edit',
        component: () => import('./Pages/Gastos/Edit.vue')
    },
    {
        path: '/abonos',
        name: 'abonos',
        component: () => import('./Pages/Abonos/Index.vue')
    },
    {
        path: '/abonos/nuevo',
        name: 'abonos.create',
        component: () => import('./Pages/Abonos/Create.vue')
    },
    {
        path: '/historial',
        name: 'historial',
        component: () => import('./Pages/Historial.vue')
    },
    {
        path: '/configuracion',
        name: 'configuracion',
        component: () => import('./Pages/Configuracion.vue')
    }
]
```

---

## 10. PWA (Progressive Web App)

### 10.1 Manifest (public/manifest.json)
```json
{
    "name": "Finanzas Compartidas",
    "short_name": "Finanzas",
    "description": "Control de gastos compartidos",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#4f46e5",
    "orientation": "portrait",
    "icons": [
        {
            "src": "/icons/icon-192.png",
            "sizes": "192x192",
            "type": "image/png"
        },
        {
            "src": "/icons/icon-512.png",
            "sizes": "512x512",
            "type": "image/png"
        }
    ]
}
```

### 10.2 Service Worker
```javascript
// public/sw.js

// Cache de assets estáticos
// Estrategia: Network first, fallback to cache
// Cachear: CSS, JS, iconos
// No cachear: API calls (siempre fresh)
```

### 10.3 Registro del SW
```javascript
// En app.js
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
}
```

---

## 11. UI/UX Diseño

### 11.1 Paleta de colores
```css
:root {
    --primary: #4f46e5;      /* Indigo - acciones principales */
    --primary-dark: #3730a3;
    --success: #10b981;      /* Verde - positivo/abonos */
    --danger: #ef4444;       /* Rojo - negativo/debe */
    --warning: #f59e0b;      /* Amarillo - alertas */
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-500: #6b7280;
    --gray-900: #111827;
}
```

### 11.2 Navegación móvil (Bottom Navigation)
```
┌─────────────────────────────────────┐
│                                     │
│           [Contenido]               │
│                                     │
├─────────────────────────────────────┤
│  🏠      ➕       📋       ⚙️      │
│ Inicio  Nuevo  Historial  Config   │
└─────────────────────────────────────┘
```

### 11.3 Wireframes

#### Dashboard (móvil)
```
┌─────────────────────────────────┐
│  Finanzas Compartidas      ≡   │
├─────────────────────────────────┤
│ ┌─────────────────────────────┐ │
│ │   SALDO PENDIENTE           │ │
│ │   Laura                     │ │
│ │   $150.000                  │ │
│ │   ══════════════════        │ │
│ └─────────────────────────────┘ │
│                                 │
│ Resumen Diciembre 2024          │
│ ┌──────────┬──────────────────┐ │
│ │ Laura    │      $120.000    │ │
│ │ David    │      $200.000    │ │
│ │ Casa     │      $180.000    │ │
│ └──────────┴──────────────────┘ │
│                                 │
│ Últimos movimientos             │
│ ┌─────────────────────────────┐ │
│ │ 📅 05/12 Spotify    $10.100 │ │
│ │ David • Davivienda          │ │
│ ├─────────────────────────────┤ │
│ │ 📅 02/12 Pasaporte $189.096 │ │
│ │ Laura • Daviplata           │ │
│ └─────────────────────────────┘ │
│                                 │
├─────────────────────────────────┤
│  🏠      ➕       📋       ⚙️   │
└─────────────────────────────────┘
```

#### Formulario nuevo gasto (móvil)
```
┌─────────────────────────────────┐
│  ← Nuevo Gasto                  │
├─────────────────────────────────┤
│                                 │
│ Fecha                           │
│ ┌─────────────────────────────┐ │
│ │ 📅  26/12/2024              │ │
│ └─────────────────────────────┘ │
│                                 │
│ Medio de pago                   │
│ ┌─────────────────────────────┐ │
│ │ Davivienda Crédito      ▼  │ │
│ └─────────────────────────────┘ │
│                                 │
│ Concepto                        │
│ ┌─────────────────────────────┐ │
│ │ Ej: Almuerzo restaurante    │ │
│ └─────────────────────────────┘ │
│                                 │
│ Valor                           │
│ ┌─────────────────────────────┐ │
│ │ $ 0                         │ │
│ └─────────────────────────────┘ │
│                                 │
│ ¿De quién es este gasto?        │
│ ┌─────────┬─────────┬─────────┐ │
│ │  Laura  │  David  │  Casa   │ │
│ └─────────┴─────────┴─────────┘ │
│                                 │
│ ┌─────────────────────────────┐ │
│ │       💾 GUARDAR            │ │
│ └─────────────────────────────┘ │
│                                 │
├─────────────────────────────────┤
│  🏠      ➕       📋       ⚙️   │
└─────────────────────────────────┘
```

---

## 12. Configuración del Servidor (Para después)

### 12.1 Requisitos del servidor (Portátil i5)
- **SO:** Ubuntu Server 22.04 LTS
- **RAM:** 8GB (suficiente)
- **Disco:** SSD 250GB (más que suficiente)
- **Conexión:** WiFi (configurar IP fija en router)

### 12.2 Software a instalar
```bash
# Sistema base
- Nginx
- PHP 8.2 + extensiones (fpm, sqlite, mbstring, xml, curl)
- Composer
- Node.js 20 LTS + npm
- SQLite3
- Certbot (Let's Encrypt)
- DuckDNS client
```

### 12.3 Configuración de red
```
1. Asignar IP fija al portátil en el router (ej: 192.168.1.100)
2. Port forwarding: 80 y 443 → 192.168.1.100
3. Configurar DuckDNS para dominio gratuito (ej: tufinanzas.duckdns.org)
4. Configurar Certbot para HTTPS automático
```

### 12.4 Backups automáticos
```
- Cron job diario para copiar database.sqlite
- Sincronizar con Google Drive o similar
- Retención: últimos 30 días
```

### 12.5 Script de inicio automático
```bash
# Systemd service para que Laravel se inicie al encender
# /etc/systemd/system/finanzas.service
```

---

## 13. Plan de Implementación

### Fase 1: Backend Laravel
1. [ ] Crear proyecto Laravel
2. [ ] Configurar SQLite
3. [ ] Crear migraciones (medios_pago, gastos, abonos, configuraciones)
4. [ ] Crear seeders (medios de pago y configuración inicial)
5. [ ] Crear modelos (MedioPago, Gasto, Abono, Configuracion)
6. [ ] Crear Form Requests (validaciones)
7. [ ] Crear controladores (incluyendo MedioPagoController)
8. [ ] Definir rutas API (incluyendo CRUD de medios de pago)
9. [ ] Probar endpoints con Postman/curl

### Fase 2: Frontend Vue
1. [ ] Configurar Vue 3 + Vite
2. [ ] Instalar Tailwind CSS
3. [ ] Instalar Pinia (stores)
4. [ ] Instalar Vue Router
5. [ ] Crear layout base (AppLayout, BottomNav)
6. [ ] Crear componentes UI reutilizables
7. [ ] Crear store de medios de pago
8. [ ] Crear componentes de medios de pago (MedioPagoForm, MedioPagoList, MedioPagoItem)
9. [ ] Crear páginas (Dashboard, Gastos, Abonos, Historial, Config con gestión de medios de pago)
10. [ ] Conectar con API
11. [ ] Implementar filtros y búsqueda

### Fase 3: PWA
1. [ ] Crear manifest.json
2. [ ] Crear iconos (192px, 512px)
3. [ ] Crear service worker básico
4. [ ] Probar instalación en iOS y Android

### Fase 4: Testing local
1. [ ] Probar flujo completo de gastos
2. [ ] Probar flujo de abonos
3. [ ] Verificar cálculo de saldo
4. [ ] Probar en diferentes dispositivos

### Fase 5: Deploy (Servidor)
1. [ ] Instalar Ubuntu Server en portátil
2. [ ] Configurar red (IP fija, port forwarding)
3. [ ] Instalar stack (Nginx, PHP, etc.)
4. [ ] Configurar DuckDNS
5. [ ] Configurar HTTPS con Certbot
6. [ ] Deploy de la aplicación
7. [ ] Configurar backups automáticos
8. [ ] Probar acceso externo

---

## 14. Comandos útiles

### Desarrollo
```bash
# Crear proyecto
composer create-project laravel/laravel finanzas

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Servidor desarrollo
php artisan serve

# Frontend
npm run dev
npm run build
```

### Producción
```bash
# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
npm run build
```

---

## 15. Notas adicionales

### Seguridad
- No se requiere autenticación (uso personal en red local)
- Considerar agregar PIN simple si se desea
- HTTPS obligatorio para PWA

### Rendimiento
- SQLite es suficiente para este volumen de datos
- Índices en columnas de filtro (fecha, tipo, medio_pago)
- Paginación en listados largos

### Mantenimiento
- Backup diario de database.sqlite
- Logs de Laravel para debugging
- Monitorear espacio en disco

### Futuras mejoras (opcionales)
- Categorías de gasto personalizables
- Gráficos y estadísticas avanzadas
- Notificaciones de saldo alto
- Múltiples cuentas/períodos
- Exportación a PDF
