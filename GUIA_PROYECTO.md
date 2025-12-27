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

### 2.6 Categorías de Gasto
Las categorías son **administrables** desde la aplicación:
- El usuario puede **añadir**, **editar** y **eliminar** categorías
- Cada categoría tiene: nombre, icono (opcional), color y estado (activo/inactivo)
- No se puede eliminar una categoría que tenga gastos asociados (solo desactivar)
- La categoría es **opcional** al registrar un gasto
- Categorías por defecto (creadas con seeder):
  1. Alimentación
  2. Transporte
  3. Servicios
  4. Entretenimiento
  5. Salud
  6. Otros

### 2.7 Conceptos Frecuentes
Sistema para acelerar el registro diario:
- Se guardan automáticamente los conceptos más usados
- **Autocompletado** al escribir en el campo concepto
- Lista de **favoritos** que el usuario puede marcar manualmente
- Al seleccionar un favorito, puede autocompletar medio de pago y tipo
- Administrable: el usuario puede eliminar conceptos de la lista

### 2.8 Plantillas Rápidas
Combinaciones predefinidas para registro en **2-3 taps**:
- El usuario crea plantillas con: nombre, concepto, medio de pago, tipo, categoría y valor (opcional)
- Ejemplo: "Almuerzo" → Concepto: "Almuerzo trabajo", Efectivo, Casa, Alimentación, $15.000
- Acceso rápido desde el **dashboard** con botones destacados
- Al usar una plantilla: solo confirmar fecha y valor (si no está predefinido)
- Máximo 6 plantillas visibles en dashboard (las más usadas)

### 2.9 Gastos Recurrentes
Para gastos que se repiten mensualmente:
- El usuario configura: concepto, medio de pago, tipo, categoría, valor, día del mes
- Se registran **automáticamente** cada mes en la fecha configurada
- Ejemplos: Netflix, Spotify, arriendo, servicios públicos
- El usuario puede pausar o eliminar gastos recurrentes
- Notificación visual cuando hay gastos recurrentes pendientes de confirmar

### 2.10 Cálculo del Saldo Pendiente
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
│   │   │   ├── CategoriaController.php
│   │   │   ├── ConceptoFrecuenteController.php
│   │   │   ├── PlantillaController.php
│   │   │   ├── GastoRecurrenteController.php
│   │   │   ├── ConfiguracionController.php
│   │   │   └── DashboardController.php
│   │   └── Requests/
│   │       ├── GastoRequest.php
│   │       ├── AbonoRequest.php
│   │       ├── MedioPagoRequest.php
│   │       ├── CategoriaRequest.php
│   │       ├── PlantillaRequest.php
│   │       └── GastoRecurrenteRequest.php
│   └── Models/
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
│   │   ├── 2024_01_01_000001_create_medios_pago_table.php
│   │   ├── 2024_01_01_000002_create_categorias_table.php
│   │   ├── 2024_01_01_000003_create_gastos_table.php
│   │   ├── 2024_01_01_000004_create_abonos_table.php
│   │   ├── 2024_01_01_000005_create_conceptos_frecuentes_table.php
│   │   ├── 2024_01_01_000006_create_plantillas_table.php
│   │   ├── 2024_01_01_000007_create_gastos_recurrentes_table.php
│   │   └── 2024_01_01_000008_create_configuraciones_table.php
│   └── seeders/
│       ├── MedioPagoSeeder.php
│       ├── CategoriaSeeder.php
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
│   │   │   ├── Categorias/
│   │   │   │   ├── CategoriaForm.vue
│   │   │   │   ├── CategoriaList.vue
│   │   │   │   └── CategoriaItem.vue
│   │   │   ├── Plantillas/
│   │   │   │   ├── PlantillaForm.vue
│   │   │   │   ├── PlantillaList.vue
│   │   │   │   └── PlantillaQuickButtons.vue
│   │   │   ├── GastosRecurrentes/
│   │   │   │   ├── GastoRecurrenteForm.vue
│   │   │   │   ├── GastoRecurrenteList.vue
│   │   │   │   └── GastoRecurrenteItem.vue
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
│   │   │   ├── categorias.js
│   │   │   ├── conceptosFrecuentes.js
│   │   │   ├── plantillas.js
│   │   │   ├── gastosRecurrentes.js
│   │   │   ├── theme.js
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
┌─────────────────────┐     ┌─────────────────────┐     ┌─────────────────────┐
│    configuraciones  │     │    medios_pago      │     │     categorias      │
├─────────────────────┤     ├─────────────────────┤     ├─────────────────────┤
│ id                  │     │ id                  │     │ id                  │
│ clave (unique)      │     │ nombre              │     │ nombre              │
│ valor               │     │ icono (nullable)    │     │ icono (nullable)    │
│ created_at          │     │ activo              │     │ color               │
│ updated_at          │     │ orden               │     │ activo              │
└─────────────────────┘     │ created_at          │     │ orden               │
                            │ updated_at          │     │ created_at          │
                            └─────────────────────┘     │ updated_at          │
                                      ▲                 └─────────────────────┘
                                      │                           ▲
                                      │                           │
┌─────────────────────────────────────┴───────────────────────────┴─────────────┐
│                                   gastos                                       │
├───────────────────────────────────────────────────────────────────────────────┤
│ id | fecha | medio_pago_id (FK) | categoria_id (FK, nullable) | concepto |   │
│ valor | tipo | created_at | updated_at                                        │
└───────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────┐     ┌─────────────────────────────────────────────────┐
│       abonos        │     │              conceptos_frecuentes               │
├─────────────────────┤     ├─────────────────────────────────────────────────┤
│ id                  │     │ id | concepto | medio_pago_id (FK, nullable) |  │
│ fecha               │     │ tipo | uso_count | es_favorito | created_at |   │
│ valor               │     │ updated_at                                      │
│ nota (nullable)     │     └─────────────────────────────────────────────────┘
│ created_at          │
│ updated_at          │     ┌─────────────────────────────────────────────────┐
└─────────────────────┘     │                   plantillas                    │
                            ├─────────────────────────────────────────────────┤
                            │ id | nombre | concepto | medio_pago_id (FK) |   │
                            │ categoria_id (FK, nullable) | tipo | valor |    │
                            │ uso_count | activo | orden | created_at |       │
                            │ updated_at                                      │
                            └─────────────────────────────────────────────────┘

                            ┌─────────────────────────────────────────────────┐
                            │              gastos_recurrentes                 │
                            ├─────────────────────────────────────────────────┤
                            │ id | concepto | medio_pago_id (FK) |            │
                            │ categoria_id (FK, nullable) | tipo | valor |    │
                            │ dia_mes | activo | ultimo_registro |            │
                            │ created_at | updated_at                         │
                            └─────────────────────────────────────────────────┘
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

### 4.3 Tabla: `categorias`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(100) | NOT NULL | Nombre de la categoría |
| icono | VARCHAR(50) | NULLABLE | Nombre del icono |
| color | VARCHAR(7) | NOT NULL, DEFAULT '#6B7280' | Color hex para UI |
| activo | BOOLEAN | NOT NULL, DEFAULT TRUE | Si está disponible |
| orden | INTEGER | NOT NULL, DEFAULT 0 | Orden de aparición |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_categorias_activo` en columna `activo`
- `idx_categorias_orden` en columna `orden`

**Datos iniciales (Seeder):**
| nombre | icono | color | activo | orden |
|--------|-------|-------|--------|-------|
| Alimentación | utensils | #10B981 | true | 1 |
| Transporte | car | #3B82F6 | true | 2 |
| Servicios | zap | #F59E0B | true | 3 |
| Entretenimiento | film | #8B5CF6 | true | 4 |
| Salud | heart | #EF4444 | true | 5 |
| Otros | folder | #6B7280 | true | 6 |

### 4.4 Tabla: `gastos`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| fecha | DATE | NOT NULL | Fecha del gasto |
| medio_pago_id | BIGINT | NOT NULL, FK → medios_pago.id | Referencia al medio de pago |
| categoria_id | BIGINT | NULLABLE, FK → categorias.id | Referencia a la categoría (opcional) |
| concepto | VARCHAR(255) | NOT NULL | Descripción del gasto |
| valor | DECIMAL(12,2) | NOT NULL, UNSIGNED | Monto del gasto |
| tipo | VARCHAR(20) | NOT NULL | Enum: persona_1, persona_2, casa |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_gastos_fecha` en columna `fecha`
- `idx_gastos_tipo` en columna `tipo`
- `idx_gastos_medio_pago_id` en columna `medio_pago_id`
- `idx_gastos_categoria_id` en columna `categoria_id`

### 4.5 Tabla: `abonos`
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

### 4.6 Tabla: `conceptos_frecuentes`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| concepto | VARCHAR(255) | NOT NULL | Texto del concepto |
| medio_pago_id | BIGINT | NULLABLE, FK → medios_pago.id | Medio de pago asociado (opcional) |
| tipo | VARCHAR(20) | NULLABLE | Tipo asociado (opcional) |
| uso_count | INTEGER | NOT NULL, DEFAULT 1 | Contador de usos |
| es_favorito | BOOLEAN | NOT NULL, DEFAULT FALSE | Marcado como favorito |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_conceptos_frecuentes_concepto` en columna `concepto`
- `idx_conceptos_frecuentes_uso_count` en columna `uso_count`
- `idx_conceptos_frecuentes_favorito` en columna `es_favorito`

### 4.7 Tabla: `plantillas`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(50) | NOT NULL | Nombre corto para el botón |
| concepto | VARCHAR(255) | NOT NULL | Concepto predefinido |
| medio_pago_id | BIGINT | NOT NULL, FK → medios_pago.id | Medio de pago |
| categoria_id | BIGINT | NULLABLE, FK → categorias.id | Categoría (opcional) |
| tipo | VARCHAR(20) | NOT NULL | Tipo: persona_1, persona_2, casa |
| valor | DECIMAL(12,2) | NULLABLE | Valor predefinido (opcional) |
| uso_count | INTEGER | NOT NULL, DEFAULT 0 | Contador de usos |
| activo | BOOLEAN | NOT NULL, DEFAULT TRUE | Si está activa |
| orden | INTEGER | NOT NULL, DEFAULT 0 | Orden de aparición |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_plantillas_activo` en columna `activo`
- `idx_plantillas_orden` en columna `orden`
- `idx_plantillas_uso_count` en columna `uso_count`

### 4.8 Tabla: `gastos_recurrentes`
| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Identificador único |
| concepto | VARCHAR(255) | NOT NULL | Concepto del gasto |
| medio_pago_id | BIGINT | NOT NULL, FK → medios_pago.id | Medio de pago |
| categoria_id | BIGINT | NULLABLE, FK → categorias.id | Categoría (opcional) |
| tipo | VARCHAR(20) | NOT NULL | Tipo: persona_1, persona_2, casa |
| valor | DECIMAL(12,2) | NOT NULL | Valor del gasto |
| dia_mes | INTEGER | NOT NULL | Día del mes (1-31) |
| activo | BOOLEAN | NOT NULL, DEFAULT TRUE | Si está activo |
| ultimo_registro | DATE | NULLABLE | Fecha del último registro automático |
| created_at | TIMESTAMP | | Fecha de creación |
| updated_at | TIMESTAMP | | Fecha de actualización |

**Índices:**
- `idx_gastos_recurrentes_activo` en columna `activo`
- `idx_gastos_recurrentes_dia_mes` en columna `dia_mes`

### 4.9 Tabla: `configuraciones`
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
| tema | system | Tema: light, dark, system |

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

### 5.2 Modelo: Categoria
```php
// app/Models/Categoria.php

class Categoria extends Model
{
    protected $fillable = [
        'nombre',
        'icono',
        'color',
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

    public function puedeEliminarse()
    {
        return $this->gastos()->count() === 0;
    }
}
```

### 5.3 Modelo: Gasto
```php
// app/Models/Gasto.php

class Gasto extends Model
{
    protected $fillable = [
        'fecha',
        'medio_pago_id',
        'categoria_id',
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

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
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

    public function scopeCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }
}
```

### 5.4 Modelo: Abono
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

### 5.5 Modelo: ConceptoFrecuente
```php
// app/Models/ConceptoFrecuente.php

class ConceptoFrecuente extends Model
{
    protected $table = 'conceptos_frecuentes';

    protected $fillable = [
        'concepto',
        'medio_pago_id',
        'tipo',
        'uso_count',
        'es_favorito'
    ];

    protected $casts = [
        'uso_count' => 'integer',
        'es_favorito' => 'boolean'
    ];

    // Relaciones
    public function medioPago()
    {
        return $this->belongsTo(MedioPago::class);
    }

    // Scopes
    public function scopeFavoritos($query)
    {
        return $query->where('es_favorito', true);
    }

    public function scopeMasUsados($query, $limite = 10)
    {
        return $query->orderByDesc('uso_count')->limit($limite);
    }

    // Incrementar uso
    public function incrementarUso()
    {
        $this->increment('uso_count');
    }

    // Buscar o crear concepto
    public static function registrarUso($concepto, $medioPagoId = null, $tipo = null)
    {
        $registro = self::firstOrCreate(
            ['concepto' => $concepto],
            ['medio_pago_id' => $medioPagoId, 'tipo' => $tipo]
        );
        $registro->incrementarUso();
        return $registro;
    }
}
```

### 5.6 Modelo: Plantilla
```php
// app/Models/Plantilla.php

class Plantilla extends Model
{
    protected $fillable = [
        'nombre',
        'concepto',
        'medio_pago_id',
        'categoria_id',
        'tipo',
        'valor',
        'uso_count',
        'activo',
        'orden'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'uso_count' => 'integer',
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    // Relaciones
    public function medioPago()
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden');
    }

    public function scopeMasUsadas($query, $limite = 6)
    {
        return $query->orderByDesc('uso_count')->limit($limite);
    }

    // Usar plantilla (crea gasto)
    public function usar($fecha, $valorOverride = null)
    {
        $this->increment('uso_count');

        return Gasto::create([
            'fecha' => $fecha,
            'concepto' => $this->concepto,
            'medio_pago_id' => $this->medio_pago_id,
            'categoria_id' => $this->categoria_id,
            'tipo' => $this->tipo,
            'valor' => $valorOverride ?? $this->valor
        ]);
    }
}
```

### 5.7 Modelo: GastoRecurrente
```php
// app/Models/GastoRecurrente.php

class GastoRecurrente extends Model
{
    protected $table = 'gastos_recurrentes';

    protected $fillable = [
        'concepto',
        'medio_pago_id',
        'categoria_id',
        'tipo',
        'valor',
        'dia_mes',
        'activo',
        'ultimo_registro'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'dia_mes' => 'integer',
        'activo' => 'boolean',
        'ultimo_registro' => 'date'
    ];

    // Relaciones
    public function medioPago()
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePendientes($query)
    {
        $hoy = now();
        return $query->activos()
            ->where('dia_mes', '<=', $hoy->day)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('ultimo_registro')
                  ->orWhere('ultimo_registro', '<', $hoy->startOfMonth());
            });
    }

    // Registrar gasto recurrente
    public function registrar()
    {
        $gasto = Gasto::create([
            'fecha' => now()->setDay($this->dia_mes),
            'concepto' => $this->concepto,
            'medio_pago_id' => $this->medio_pago_id,
            'categoria_id' => $this->categoria_id,
            'tipo' => $this->tipo,
            'valor' => $this->valor
        ]);

        $this->update(['ultimo_registro' => now()]);

        return $gasto;
    }
}
```

### 5.8 Modelo: Configuracion
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

#### Categorías
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/categorias` | Listar categorías | `?activas=true` (opcional) |
| POST | `/api/categorias` | Crear categoría | `{nombre, icono, color, activo, orden}` |
| GET | `/api/categorias/{id}` | Obtener categoría | - |
| PUT | `/api/categorias/{id}` | Actualizar categoría | `{nombre, icono, color, activo, orden}` |
| DELETE | `/api/categorias/{id}` | Eliminar categoría | Solo si no tiene gastos |
| PUT | `/api/categorias/reordenar` | Reordenar categorías | `{orden: [id1, id2, ...]}` |

#### Gastos
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/gastos` | Listar gastos con filtros | `?desde=&hasta=&tipo=&medio_pago_id=&categoria_id=&page=` |
| POST | `/api/gastos` | Crear nuevo gasto | `{fecha, medio_pago_id, categoria_id, concepto, valor, tipo}` |
| GET | `/api/gastos/{id}` | Obtener gasto específico | - |
| PUT | `/api/gastos/{id}` | Actualizar gasto | `{fecha, medio_pago_id, categoria_id, concepto, valor, tipo}` |
| DELETE | `/api/gastos/{id}` | Eliminar gasto | - |

#### Abonos
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/abonos` | Listar abonos | `?desde=&hasta=&page=` |
| POST | `/api/abonos` | Crear nuevo abono | `{fecha, valor, nota}` |
| GET | `/api/abonos/{id}` | Obtener abono específico | - |
| PUT | `/api/abonos/{id}` | Actualizar abono | `{fecha, valor, nota}` |
| DELETE | `/api/abonos/{id}` | Eliminar abono | - |

#### Conceptos Frecuentes
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/conceptos-frecuentes` | Listar conceptos | `?favoritos=true&limite=10` |
| GET | `/api/conceptos-frecuentes/buscar` | Autocompletado | `?q=texto` |
| PUT | `/api/conceptos-frecuentes/{id}/favorito` | Toggle favorito | `{es_favorito: true/false}` |
| DELETE | `/api/conceptos-frecuentes/{id}` | Eliminar concepto | - |

#### Plantillas
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/plantillas` | Listar plantillas | `?activas=true` |
| GET | `/api/plantillas/rapidas` | Top 6 más usadas | - |
| POST | `/api/plantillas` | Crear plantilla | `{nombre, concepto, medio_pago_id, categoria_id, tipo, valor, orden}` |
| GET | `/api/plantillas/{id}` | Obtener plantilla | - |
| PUT | `/api/plantillas/{id}` | Actualizar plantilla | `{nombre, concepto, medio_pago_id, categoria_id, tipo, valor, activo, orden}` |
| DELETE | `/api/plantillas/{id}` | Eliminar plantilla | - |
| POST | `/api/plantillas/{id}/usar` | Usar plantilla (crea gasto) | `{fecha, valor}` (valor opcional) |
| PUT | `/api/plantillas/reordenar` | Reordenar plantillas | `{orden: [id1, id2, ...]}` |

#### Gastos Recurrentes
| Método | Endpoint | Descripción | Body/Params |
|--------|----------|-------------|-------------|
| GET | `/api/gastos-recurrentes` | Listar gastos recurrentes | `?activos=true` |
| GET | `/api/gastos-recurrentes/pendientes` | Listar pendientes del mes | - |
| POST | `/api/gastos-recurrentes` | Crear gasto recurrente | `{concepto, medio_pago_id, categoria_id, tipo, valor, dia_mes}` |
| GET | `/api/gastos-recurrentes/{id}` | Obtener gasto recurrente | - |
| PUT | `/api/gastos-recurrentes/{id}` | Actualizar gasto recurrente | `{concepto, medio_pago_id, categoria_id, tipo, valor, dia_mes, activo}` |
| DELETE | `/api/gastos-recurrentes/{id}` | Eliminar gasto recurrente | - |
| POST | `/api/gastos-recurrentes/{id}/registrar` | Registrar manualmente | - |
| POST | `/api/gastos-recurrentes/registrar-pendientes` | Registrar todos los pendientes | - |

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
| GET | `/api/exportar/excel` | Exportar a Excel | `?desde=&hasta=&tipo=&medio_pago_id=&categoria_id=` |

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
            'categoria_id' => 'nullable|exists:categorias,id',
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
            'categoria_id.exists' => 'Categoría no válida',
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

### 8.4 CategoriaRequest
```php
// app/Http/Requests/CategoriaRequest.php

class CategoriaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'icono' => 'nullable|string|max:50',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ];
    }
}
```

### 8.5 PlantillaRequest
```php
// app/Http/Requests/PlantillaRequest.php

class PlantillaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:50',
            'concepto' => 'required|string|max:255',
            'medio_pago_id' => 'required|exists:medios_pago,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'tipo' => 'required|in:persona_1,persona_2,casa',
            'valor' => 'nullable|numeric|min:0.01',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ];
    }
}
```

### 8.6 GastoRecurrenteRequest
```php
// app/Http/Requests/GastoRecurrenteRequest.php

class GastoRecurrenteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'concepto' => 'required|string|max:255',
            'medio_pago_id' => 'required|exists:medios_pago,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'tipo' => 'required|in:persona_1,persona_2,casa',
            'valor' => 'required|numeric|min:0.01',
            'dia_mes' => 'required|integer|min:1|max:31',
            'activo' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            'dia_mes.min' => 'El día debe ser entre 1 y 31',
            'dia_mes.max' => 'El día debe ser entre 1 y 31'
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
- PlantillasRapidas: Botones de acceso rápido (máx 6, las más usadas)
  - Al tocar: abre modal para confirmar fecha y valor
  - Registro en 2-3 taps
- AlertaRecurrentes: Banner si hay gastos recurrentes pendientes
  - Muestra cantidad pendiente
  - Botón para registrar todos o ver detalle
- ResumenMes: Gastos del mes por tipo y categoría
- UltimosMovimientos: Lista de últimos 10 movimientos

Funcionalidades:
- Auto-refresh cada 30 segundos (opcional)
- Pull-to-refresh en móvil
- Acceso rápido a nuevo gasto con autocompletado de conceptos
```

#### Gastos/Create.vue
```
Campos del formulario:
- Fecha (date picker, default: hoy)
- Medio de pago (select con iconos)
- Concepto (input text con autocompletado de conceptos frecuentes)
  - Al escribir, muestra sugerencias
  - Al seleccionar favorito, puede autocompletar medio de pago y tipo
- Categoría (select opcional con colores)
- Valor (input number con formato moneda)
- Tipo/¿De quién? (3 botones: Persona1, Persona2, Casa)

Características:
- Validación en tiempo real
- Botón de guardar habilitado solo si es válido
- Feedback visual al guardar (toast/snackbar)
- Después de guardar: limpiar formulario y mostrar opción de ver dashboard
- El concepto se guarda automáticamente en conceptos frecuentes
```

#### Historial.vue
```
Funcionalidades:
- Lista de todos los gastos y abonos
- Filtros:
  - Rango de fechas (date range picker)
  - Tipo (multiselect)
  - Medio de pago (multiselect)
  - Categoría (multiselect con colores)
- Ordenar por fecha (asc/desc)
- Scroll infinito o paginación
- Swipe para editar/eliminar (móvil)
- Botón exportar a Excel
- Cada item muestra: fecha, concepto, valor, tipo, categoría (badge color)
```

#### Configuracion.vue
```
Secciones:

1. Apariencia
   - Toggle de tema: Claro / Oscuro / Sistema
   - Preview del tema seleccionado

2. Personas y Porcentajes
   - Nombre persona 1 (input text)
   - Nombre persona 2 (input text)
   - Porcentaje persona 1 (slider o input 0-100)
   - Porcentaje persona 2 (calculado automáticamente)

3. Medios de Pago (Módulo administrable)
   - Lista de medios de pago existentes (drag & drop para reordenar)
   - Cada item muestra: icono, nombre, estado (activo/inactivo)
   - Botón para añadir nuevo medio de pago
   - Acciones por item: editar, activar/desactivar, eliminar
   - Al eliminar: confirmar si no tiene gastos, o solo desactivar si tiene gastos

4. Categorías (Módulo administrable)
   - Lista de categorías con color e icono
   - Drag & drop para reordenar
   - Botón para añadir nueva categoría
   - Acciones: editar, activar/desactivar, eliminar

5. Plantillas Rápidas
   - Lista de plantillas configuradas
   - Mostrar: nombre, concepto, tipo, valor
   - Drag & drop para reordenar (afecta orden en dashboard)
   - Botón para crear nueva plantilla
   - Acciones: editar, activar/desactivar, eliminar

6. Gastos Recurrentes
   - Lista de gastos recurrentes
   - Mostrar: concepto, valor, día del mes, estado
   - Indicador de próximo registro
   - Botón para crear nuevo gasto recurrente
   - Acciones: editar, pausar/activar, eliminar, registrar ahora

7. Estadísticas y Datos
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

### 11.1 Sistema de Temas (Modo Claro/Oscuro)

El sistema soporta tres modos de tema:
- **light**: Tema claro (fondo blanco)
- **dark**: Tema oscuro (fondo oscuro)
- **system**: Sigue la preferencia del sistema operativo

**Implementación:**
```javascript
// resources/js/Stores/theme.js
import { defineStore } from 'pinia'

export const useThemeStore = defineStore('theme', {
    state: () => ({
        tema: 'system' // 'light' | 'dark' | 'system'
    }),

    getters: {
        temaActivo: (state) => {
            if (state.tema === 'system') {
                return window.matchMedia('(prefers-color-scheme: dark)').matches
                    ? 'dark'
                    : 'light'
            }
            return state.tema
        }
    },

    actions: {
        setTema(tema) {
            this.tema = tema
            localStorage.setItem('tema', tema)
            this.aplicarTema()
        },

        aplicarTema() {
            const html = document.documentElement
            if (this.temaActivo === 'dark') {
                html.classList.add('dark')
            } else {
                html.classList.remove('dark')
            }
        },

        inicializar() {
            this.tema = localStorage.getItem('tema') || 'system'
            this.aplicarTema()

            // Escuchar cambios en preferencia del sistema
            window.matchMedia('(prefers-color-scheme: dark)')
                .addEventListener('change', () => this.aplicarTema())
        }
    }
})
```

### 11.2 Paleta de colores
```css
/* Tema Claro (default) */
:root {
    --primary: #4f46e5;      /* Indigo - acciones principales */
    --primary-dark: #3730a3;
    --success: #10b981;      /* Verde - positivo/abonos */
    --danger: #ef4444;       /* Rojo - negativo/debe */
    --warning: #f59e0b;      /* Amarillo - alertas */

    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
    --bg-tertiary: #f3f4f6;

    --text-primary: #111827;
    --text-secondary: #4b5563;
    --text-muted: #9ca3af;

    --border: #e5e7eb;
    --card-bg: #ffffff;
    --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Tema Oscuro */
:root.dark {
    --primary: #818cf8;      /* Indigo más claro para contraste */
    --primary-dark: #6366f1;
    --success: #34d399;
    --danger: #f87171;
    --warning: #fbbf24;

    --bg-primary: #111827;
    --bg-secondary: #1f2937;
    --bg-tertiary: #374151;

    --text-primary: #f9fafb;
    --text-secondary: #d1d5db;
    --text-muted: #6b7280;

    --border: #374151;
    --card-bg: #1f2937;
    --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}
```

**Uso con Tailwind CSS:**
```javascript
// tailwind.config.js
module.exports = {
    darkMode: 'class',
    // ...resto de configuración
}
```

**Ejemplo en componentes Vue:**
```html
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
    <h1 class="text-primary dark:text-indigo-400">Título</h1>
</div>
```

### 11.3 Navegación móvil (Bottom Navigation)
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

### 11.4 Wireframes

#### Dashboard (móvil)
```
┌─────────────────────────────────┐
│  Finanzas Compartidas    🌙 ≡  │
├─────────────────────────────────┤
│ ┌─────────────────────────────┐ │
│ │   SALDO PENDIENTE           │ │
│ │   Laura                     │ │
│ │   $150.000                  │ │
│ │   ══════════════════        │ │
│ └─────────────────────────────┘ │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ ⚠️ 3 gastos recurrentes     │ │
│ │ pendientes    [Registrar]   │ │
│ └─────────────────────────────┘ │
│                                 │
│ Registro rápido                 │
│ ┌───────┬───────┬───────┐       │
│ │Almuer.│ Gasl. │Mercado│       │
│ ├───────┼───────┼───────┤       │
│ │Netflix│Transp.│ Café  │       │
│ └───────┴───────┴───────┘       │
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
│ │ David • 🎬 Entretenimiento  │ │
│ ├─────────────────────────────┤ │
│ │ 📅 02/12 Mercado   $189.096 │ │
│ │ Casa • 🍽️ Alimentación      │ │
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
│ │ 💳 Davivienda Crédito   ▼  │ │
│ └─────────────────────────────┘ │
│                                 │
│ Concepto                        │
│ ┌─────────────────────────────┐ │
│ │ Almu...                     │ │
│ ├─────────────────────────────┤ │
│ │ ⭐ Almuerzo trabajo         │ │
│ │    Almuerzo restaurante     │ │
│ │    Almuerzo casa            │ │
│ └─────────────────────────────┘ │
│                                 │
│ Categoría (opcional)            │
│ ┌─────────────────────────────┐ │
│ │ 🍽️ Alimentación         ▼  │ │
│ └─────────────────────────────┘ │
│                                 │
│ Valor                           │
│ ┌─────────────────────────────┐ │
│ │ $ 15.000                    │ │
│ └─────────────────────────────┘ │
│                                 │
│ ¿De quién es este gasto?        │
│ ┌─────────┬─────────┬─────────┐ │
│ │  Laura  │  David  │ [Casa]  │ │
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

## 13. Plan de Implementación (10 Fases)

> **Nota:** Cada fase es independiente y testeable. Al completar cada fase, marcar las tareas con [x].

---

### Fase 1: Setup + Base de Datos
**Objetivo:** Proyecto Laravel funcionando con BD configurada

1. [ ] Crear proyecto Laravel 11
2. [ ] Configurar SQLite en .env
3. [ ] Crear migración: `create_medios_pago_table`
4. [ ] Crear migración: `create_categorias_table`
5. [ ] Crear migración: `create_gastos_table`
6. [ ] Crear migración: `create_abonos_table`
7. [ ] Crear migración: `create_conceptos_frecuentes_table`
8. [ ] Crear migración: `create_plantillas_table`
9. [ ] Crear migración: `create_gastos_recurrentes_table`
10. [ ] Crear migración: `create_configuraciones_table`
11. [ ] Crear seeder: `MedioPagoSeeder`
12. [ ] Crear seeder: `CategoriaSeeder`
13. [ ] Crear seeder: `ConfiguracionSeeder`
14. [ ] Ejecutar migraciones y seeders
15. [ ] Verificar tablas creadas en SQLite

**Comando para probar:** `php artisan migrate:fresh --seed`

---

### Fase 2: Modelos Eloquent
**Objetivo:** Todos los modelos con relaciones y scopes

1. [ ] Crear modelo: `MedioPago` (con relación hasMany a Gasto)
2. [ ] Crear modelo: `Categoria` (con relación hasMany a Gasto)
3. [ ] Crear modelo: `Gasto` (con relaciones belongsTo y scopes)
4. [ ] Crear modelo: `Abono` (con scopes)
5. [ ] Crear modelo: `ConceptoFrecuente` (con métodos de autocompletado)
6. [ ] Crear modelo: `Plantilla` (con método usar())
7. [ ] Crear modelo: `GastoRecurrente` (con scope pendientes y método registrar())
8. [ ] Crear modelo: `Configuracion` (con métodos estáticos obtener/establecer)

**Comando para probar:** `php artisan tinker` → probar relaciones

---

### Fase 3: Validaciones (Form Requests)
**Objetivo:** Todas las validaciones centralizadas

1. [ ] Crear request: `GastoRequest`
2. [ ] Crear request: `AbonoRequest`
3. [ ] Crear request: `MedioPagoRequest`
4. [ ] Crear request: `CategoriaRequest`
5. [ ] Crear request: `PlantillaRequest`
6. [ ] Crear request: `GastoRecurrenteRequest`

**Verificar:** Mensajes de error en español

---

### Fase 4: Controladores
**Objetivo:** Toda la lógica de negocio implementada

1. [ ] Crear controlador: `MedioPagoController` (CRUD + reordenar)
2. [ ] Crear controlador: `CategoriaController` (CRUD + reordenar)
3. [ ] Crear controlador: `GastoController` (CRUD + filtros)
4. [ ] Crear controlador: `AbonoController` (CRUD + filtros)
5. [ ] Crear controlador: `ConceptoFrecuenteController` (buscar, favoritos)
6. [ ] Crear controlador: `PlantillaController` (CRUD + usar + reordenar)
7. [ ] Crear controlador: `GastoRecurrenteController` (CRUD + pendientes + registrar)
8. [ ] Crear controlador: `ConfiguracionController` (get/update)
9. [ ] Crear controlador: `DashboardController` (saldo, resumen, últimos)

---

### Fase 5: Rutas API
**Objetivo:** API REST completa y funcional

1. [ ] Definir rutas para medios de pago
2. [ ] Definir rutas para categorías
3. [ ] Definir rutas para gastos
4. [ ] Definir rutas para abonos
5. [ ] Definir rutas para conceptos frecuentes
6. [ ] Definir rutas para plantillas
7. [ ] Definir rutas para gastos recurrentes
8. [ ] Definir rutas para configuración
9. [ ] Definir rutas para dashboard
10. [ ] Probar todos los endpoints con curl/Postman

**Comando para probar:** `php artisan route:list --path=api`

**🎉 CHECKPOINT: Backend completo - Probar API antes de continuar**

---

### Fase 6: Setup Frontend
**Objetivo:** Vue 3 configurado con todas las dependencias

1. [ ] Instalar Vue 3 + Vite en Laravel
2. [ ] Instalar y configurar Tailwind CSS
3. [ ] Configurar darkMode: 'class' en tailwind.config.js
4. [ ] Instalar Pinia
5. [ ] Instalar Vue Router
6. [ ] Configurar axios con baseURL
7. [ ] Crear layout: `AppLayout.vue`
8. [ ] Crear componente: `BottomNav.vue`
9. [ ] Crear componente: `ThemeToggle.vue`
10. [ ] Configurar router con rutas base
11. [ ] Crear vista blade: `app.blade.php`

**Comando para probar:** `npm run dev` → ver layout base

---

### Fase 7: Stores (Pinia)
**Objetivo:** Estado global de la aplicación

1. [ ] Crear store: `theme.js` (tema claro/oscuro/sistema)
2. [ ] Crear store: `config.js` (configuración general)
3. [ ] Crear store: `mediosPago.js`
4. [ ] Crear store: `categorias.js`
5. [ ] Crear store: `gastos.js`
6. [ ] Crear store: `abonos.js`
7. [ ] Crear store: `conceptosFrecuentes.js`
8. [ ] Crear store: `plantillas.js`
9. [ ] Crear store: `gastosRecurrentes.js`
10. [ ] Crear store: `dashboard.js`

---

### Fase 8: Componentes
**Objetivo:** Todos los componentes reutilizables

**UI Base:**
1. [ ] Crear componente: `UI/Button.vue`
2. [ ] Crear componente: `UI/Input.vue`
3. [ ] Crear componente: `UI/Select.vue`
4. [ ] Crear componente: `UI/Modal.vue`
5. [ ] Crear componente: `UI/Card.vue`
6. [ ] Crear componente: `UI/Badge.vue`
7. [ ] Crear componente: `UI/Toast.vue`

**Componentes de módulos:**
8. [ ] Crear componentes: `Gastos/` (GastoForm, GastoList, GastoItem)
9. [ ] Crear componentes: `Abonos/` (AbonoForm, AbonoList)
10. [ ] Crear componentes: `MediosPago/` (Form, List, Item)
11. [ ] Crear componentes: `Categorias/` (Form, List, Item)
12. [ ] Crear componentes: `Plantillas/` (Form, List, QuickButtons)
13. [ ] Crear componentes: `GastosRecurrentes/` (Form, List, Item)
14. [ ] Crear componentes: `Dashboard/` (SaldoCard, ResumenMes, UltimosMovimientos, AlertaRecurrentes)

---

### Fase 9: Páginas
**Objetivo:** Todas las vistas de la aplicación

1. [ ] Crear página: `Dashboard.vue` (con plantillas rápidas y alertas)
2. [ ] Crear página: `Gastos/Index.vue` (listado)
3. [ ] Crear página: `Gastos/Create.vue` (con autocompletado)
4. [ ] Crear página: `Gastos/Edit.vue`
5. [ ] Crear página: `Abonos/Index.vue`
6. [ ] Crear página: `Abonos/Create.vue`
7. [ ] Crear página: `Historial.vue` (con filtros avanzados)
8. [ ] Crear página: `Configuracion.vue` (todas las secciones)
9. [ ] Conectar todo con los stores y API
10. [ ] Implementar navegación completa

**🎉 CHECKPOINT: App completa - Probar flujos antes de continuar**

---

### Fase 10: PWA + Testing + Deploy
**Objetivo:** App lista para producción

**PWA:**
1. [ ] Crear `public/manifest.json`
2. [ ] Crear iconos 192x192 y 512x512
3. [ ] Crear `public/sw.js` (service worker)
4. [ ] Registrar service worker en app.js
5. [ ] Probar instalación en móvil

**Testing:**
6. [ ] Probar flujo completo de gastos
7. [ ] Probar plantillas rápidas
8. [ ] Probar gastos recurrentes
9. [ ] Probar modo oscuro
10. [ ] Probar en diferentes dispositivos

**Deploy (cuando tengas el servidor):**
11. [ ] Configurar servidor Ubuntu
12. [ ] Instalar Nginx + PHP + SQLite
13. [ ] Configurar dominio con DuckDNS
14. [ ] Configurar HTTPS con Certbot
15. [ ] Deploy de la aplicación
16. [ ] Configurar backups automáticos

---

### Resumen de Fases

| Fase | Descripción | Archivos aprox. |
|------|-------------|-----------------|
| 1 | Setup + BD | 12 archivos |
| 2 | Modelos | 8 archivos |
| 3 | Validaciones | 6 archivos |
| 4 | Controladores | 9 archivos |
| 5 | Rutas API | 1 archivo |
| 6 | Setup Frontend | 5 archivos |
| 7 | Stores | 10 archivos |
| 8 | Componentes | ~25 archivos |
| 9 | Páginas | 8 archivos |
| 10 | PWA + Deploy | 5 archivos |

**Total aproximado:** ~90 archivos

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
- Índices en columnas de filtro (fecha, tipo, medio_pago, categoria_id)
- Paginación en listados largos
- Autocompletado con debounce para evitar llamadas excesivas

### Mantenimiento
- Backup diario de database.sqlite
- Logs de Laravel para debugging
- Monitorear espacio en disco

### Experiencia de Usuario
- Modo oscuro respeta preferencia del sistema
- Plantillas rápidas para registro en 2-3 taps
- Autocompletado de conceptos para escritura rápida
- Gastos recurrentes se registran automáticamente
- Interfaz optimizada para uso con una mano

### Futuras mejoras (opcionales)
- Gráficos y estadísticas avanzadas
- Notificaciones de saldo alto
- Múltiples cuentas/períodos
- Exportación a PDF
- Sincronización con Google Sheets
- Widgets para pantalla de inicio
