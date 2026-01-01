<template>
    <div class="p-4 space-y-3 max-w-full overflow-hidden">
        <!-- Seccion: Parametros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('apariencia')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                        <PaintBrushIcon class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Parametros</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.apariencia ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.apariencia" class="px-4 pb-4 space-y-4 border-t border-gray-100 dark:border-gray-700">
                <!-- Tema -->
                <div class="pt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tema</label>
                    <div class="flex gap-2">
                        <button
                            v-for="tema in temas"
                            :key="tema.value"
                            @click="cambiarTema(tema.value)"
                            :class="[
                                'flex-1 py-2 px-3 rounded-lg font-medium text-sm transition-colors',
                                themeStore.tema === tema.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            ]"
                        >
                            {{ tema.label }}
                        </button>
                    </div>
                </div>
                <!-- Divisa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Divisa</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button
                            v-for="div in configStore.divisasDisponibles"
                            :key="div.value"
                            @click="cambiarDivisa(div.value)"
                            :class="[
                                'py-2 px-3 rounded-lg font-medium text-sm transition-colors',
                                configStore.divisa === div.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            ]"
                        >
                            {{ div.value }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Selecciona la moneda para mostrar los valores
                    </p>
                </div>
                <!-- Formato de Divisa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Formato de divisa</label>
                    <div class="flex gap-2">
                        <button
                            v-for="formato in configStore.formatosDivisaDisponibles"
                            :key="formato.value"
                            @click="cambiarFormatoDivisa(formato.value)"
                            :class="[
                                'flex-1 py-2 px-3 rounded-lg font-medium text-sm transition-colors',
                                configStore.formato_divisa === formato.value
                                    ? 'bg-primary text-white dark:bg-indigo-500'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                            ]"
                        >
                            {{ formato.label }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Selecciona el separador de miles para los valores
                    </p>
                </div>
            </div>
        </div>

        <!-- Seccion: Categorias -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('categorias')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                        <TagIcon class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Categorias</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.categorias ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.categorias" class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                <div class="pt-3 flex justify-end">
                    <Button size="sm" @click="abrirModalCategoria()">
                        <PlusIcon class="w-4 h-4 mr-1" />
                        Nueva
                    </Button>
                </div>
                <div class="mt-3 space-y-2">
                    <div
                        v-for="cat in categoriasStore.categorias"
                        :key="cat.id"
                        class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center"
                                :style="{ backgroundColor: cat.color + '20' }"
                            >
                                <i v-if="cat.icono" :class="cat.icono" :style="{ color: cat.color }"></i>
                                <span v-else class="w-3 h-3 rounded" :style="{ backgroundColor: cat.color }"></span>
                            </div>
                            <span :class="['text-gray-900 dark:text-white', !cat.activo && 'opacity-50']">
                                {{ cat.nombre }}
                            </span>
                            <span v-if="!cat.activo" class="text-xs text-gray-400">(inactiva)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="abrirModalCategoria(cat)"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="confirmarEliminarCategoria(cat)"
                                class="text-red-500 hover:text-red-600"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <p v-if="categoriasStore.categorias.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                        No hay categorias configuradas
                    </p>
                </div>
            </div>
        </div>

        <!-- Seccion: Medios de Pago -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('mediosPago')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center">
                        <CreditCardIcon class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Medios de Pago</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.mediosPago ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.mediosPago" class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                <div class="pt-3 flex justify-end">
                    <Button size="sm" @click="abrirModalMedioPago()">
                        <PlusIcon class="w-4 h-4 mr-1" />
                        Nuevo
                    </Button>
                </div>
                <div class="mt-3 space-y-2">
                    <div
                        v-for="mp in mediosPagoStore.mediosPago"
                        :key="mp.id"
                        class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
                    >
                        <div class="flex items-center gap-3">
                            <span :class="['w-2 h-2 rounded-full', mp.activo ? 'bg-green-500' : 'bg-gray-400']"></span>
                            <span :class="['text-gray-900 dark:text-white', !mp.activo && 'opacity-50']">
                                {{ mp.nombre }}
                            </span>
                            <span v-if="!mp.activo" class="text-xs text-gray-400">(inactivo)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="abrirModalMedioPago(mp)"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="confirmarEliminarMedioPago(mp)"
                                class="text-red-500 hover:text-red-600"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <p v-if="mediosPagoStore.mediosPago.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                        No hay medios de pago configurados
                    </p>
                </div>
            </div>
        </div>

        <!-- Seccion: Servicios -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('servicios')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-cyan-100 dark:bg-cyan-900 flex items-center justify-center">
                        <i class="pi pi-file text-cyan-600 dark:text-cyan-400"></i>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Servicios</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.servicios ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.servicios" class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                <!-- Dia de restablecimiento -->
                <div class="pt-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Dia de restablecimiento mensual
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            v-model.number="diaRestablecimiento"
                            min="1"
                            max="31"
                            class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        />
                        <Button size="sm" @click="guardarDiaRestablecimiento" :loading="guardandoDia">
                            Guardar
                        </Button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Dia del mes en que se reinician los estados de pago de servicios
                    </p>
                </div>

                <div class="flex justify-end">
                    <Button size="sm" @click="abrirModalServicio()">
                        <PlusIcon class="w-4 h-4 mr-1" />
                        Nuevo
                    </Button>
                </div>
                <div class="mt-3 space-y-2">
                    <div
                        v-for="serv in serviciosStore.servicios"
                        :key="serv.id"
                        :class="[
                            'flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors',
                            longPressServiceId === serv.id ? 'bg-indigo-50 dark:bg-indigo-900/30' : ''
                        ]"
                        @touchstart="handleServiceTouchStart(serv)"
                        @touchend="handleServiceTouchEnd"
                        @touchcancel="handleServiceTouchEnd"
                        @mousedown="handleServiceTouchStart(serv)"
                        @mouseup="handleServiceTouchEnd"
                        @mouseleave="handleServiceTouchEnd"
                    >
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                :style="{ backgroundColor: serv.color + '20' }"
                            >
                                <i v-if="serv.icono" :class="serv.icono" :style="{ color: serv.color }"></i>
                                <i v-else class="pi pi-file" :style="{ color: serv.color }"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span :class="['text-gray-900 dark:text-white truncate', !serv.activo && 'opacity-50']">
                                        {{ serv.nombre }}
                                    </span>
                                    <span v-if="serv.categoria" class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                                        ({{ serv.categoria.nombre }})
                                    </span>
                                    <span v-if="!serv.activo" class="text-xs text-gray-400 flex-shrink-0">(inactivo)</span>
                                    <span
                                        v-if="serv.frecuencia_meses > 1"
                                        class="text-xs px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 flex-shrink-0"
                                    >
                                        cada {{ serv.frecuencia_meses }} meses
                                    </span>
                                </div>
                                <span v-if="serv.referencia" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Ref: {{ serv.referencia }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="abrirModalServicio(serv)"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="confirmarEliminarServicio(serv)"
                                class="text-red-500 hover:text-red-600"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <p v-if="serviciosStore.servicios.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                        No hay servicios configurados
                    </p>
                </div>
            </div>
        </div>

        <!-- Seccion: Gastos Compartidos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('compartidos')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                        <UsersIcon class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Gastos Compartidos</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.compartidos ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.compartidos" class="px-4 pb-4 space-y-4 border-t border-gray-100 dark:border-gray-700">
                <div class="pt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Configura como se dividen los gastos compartidos entre tu y otra persona.
                    </p>

                    <!-- Nombres -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tu nombre
                            </label>
                            <input
                                type="text"
                                v-model="formCompartidos.nombre_persona_1"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Persona 1"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nombre usuario 2 <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <input
                                type="text"
                                v-model="formCompartidos.nombre_persona_2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="Dejar vacio para uso individual"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Si no tienes un segundo usuario, deja este campo vacio
                            </p>
                        </div>
                    </div>

                    <!-- Porcentajes (solo si hay usuario 2) -->
                    <div v-if="formCompartidos.nombre_persona_2 && formCompartidos.nombre_persona_2.trim()" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Porcentajes de division
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ formCompartidos.nombre_persona_1 || 'Persona 1' }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formCompartidos.porcentaje_persona_1 }}%</span>
                                </div>
                                <input
                                    type="range"
                                    v-model.number="formCompartidos.porcentaje_persona_1"
                                    @input="ajustarPorcentaje1"
                                    min="0"
                                    max="100"
                                    step="5"
                                    class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary"
                                />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ formCompartidos.nombre_persona_2 || 'Usuario 2' }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formCompartidos.porcentaje_persona_2 }}%</span>
                                </div>
                                <input
                                    type="range"
                                    v-model.number="formCompartidos.porcentaje_persona_2"
                                    @input="ajustarPorcentaje2"
                                    min="0"
                                    max="100"
                                    step="5"
                                    class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary"
                                />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                            Total: {{ formCompartidos.porcentaje_persona_1 + formCompartidos.porcentaje_persona_2 }}%
                            <span v-if="formCompartidos.porcentaje_persona_1 + formCompartidos.porcentaje_persona_2 !== 100" class="text-red-500">
                                (debe sumar 100%)
                            </span>
                        </p>
                    </div>

                    <!-- Vista previa (solo si hay usuario 2) -->
                    <div v-if="formCompartidos.nombre_persona_2 && formCompartidos.nombre_persona_2.trim()" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Vista previa:</p>
                        <p class="text-sm text-gray-900 dark:text-white">
                            Si registras un gasto compartido de <strong>$100.000</strong>:
                        </p>
                        <ul class="text-sm mt-1 space-y-1">
                            <li class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ formCompartidos.nombre_persona_1 || 'Persona 1' }}</span> asume
                                <span class="font-medium text-primary">${{ (100000 * formCompartidos.porcentaje_persona_1 / 100).toLocaleString() }}</span>
                            </li>
                            <li class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ formCompartidos.nombre_persona_2 || 'Usuario 2' }}</span> debe
                                <span class="font-medium text-purple-600 dark:text-purple-400">${{ (100000 * formCompartidos.porcentaje_persona_2 / 100).toLocaleString() }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Mensaje cuando es uso individual -->
                    <div v-if="!formCompartidos.nombre_persona_2 || !formCompartidos.nombre_persona_2.trim()" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Modo individual: Todos los gastos seran registrados solo para ti.
                        </p>
                    </div>

                    <Button
                        @click="guardarGastosCompartidos"
                        :loading="guardandoCompartidos"
                        :disabled="tieneUsuario2Form && formCompartidos.porcentaje_persona_1 + formCompartidos.porcentaje_persona_2 !== 100"
                        class="w-full"
                    >
                        Guardar configuracion
                    </Button>
                </div>
            </div>
        </div>

        <!-- Seccion: Compartir Datos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('compartirDatos')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                        <ShareIcon class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Compartir Datos</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.compartirDatos ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.compartirDatos" class="px-4 pb-4 space-y-4 border-t border-gray-100 dark:border-gray-700">
                <div class="pt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Comparte tus datos financieros con otra persona. Podra ver tu dashboard, historial y crear solicitudes de gastos.
                    </p>

                    <!-- Estado actual de comparticion -->
                    <div v-if="dataShareStore.loading" class="flex justify-center py-4">
                        <div class="animate-spin w-6 h-6 border-2 border-primary border-t-transparent rounded-full"></div>
                    </div>

                    <template v-else>
                        <!-- Ya tiene alguien con acceso -->
                        <div v-if="dataShareStore.myShare" class="space-y-3">
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                                        <UserIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ dataShareStore.myShare.guest?.name || dataShareStore.myShare.guest_email }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <span v-if="dataShareStore.myShare.status === 'pending'" class="text-amber-600 dark:text-amber-400">
                                                Invitacion pendiente
                                            </span>
                                            <span v-else-if="dataShareStore.myShare.status === 'accepted'" class="text-green-600 dark:text-green-400">
                                                Acceso activo
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <Button
                                variant="danger"
                                size="sm"
                                @click="revocarAcceso"
                                :loading="revocandoAcceso"
                                class="w-full"
                            >
                                Revocar acceso
                            </Button>
                        </div>

                        <!-- No tiene a nadie, mostrar formulario de invitacion -->
                        <div v-else class="space-y-3">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    No has compartido tus datos con nadie. Invita a una persona por email.
                                </p>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <input
                                        v-model="emailInvitacion"
                                        type="email"
                                        placeholder="email@ejemplo.com"
                                        class="flex-1 min-w-0 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                                    />
                                    <Button
                                        @click="invitarPersona"
                                        :loading="enviandoInvitacion"
                                        :disabled="!emailInvitacion"
                                        class="shrink-0 w-full sm:w-auto"
                                    >
                                        Invitar
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Seccion: Cuenta -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <button
                @click="toggleSeccion('cuenta')"
                class="w-full flex items-center justify-between p-4 text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                        <UserIcon class="w-4 h-4 text-red-600 dark:text-red-400" />
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Cuenta</span>
                </div>
                <ChevronDownIcon
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform duration-200',
                        seccionesAbiertas.cuenta ? 'rotate-180' : ''
                    ]"
                />
            </button>
            <div v-show="seccionesAbiertas.cuenta" class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700">
                <div class="mt-4 space-y-4">
                    <!-- Info de cuenta -->
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                                <UserIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ authStore.user?.name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ authStore.user?.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cerrar sesion -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="font-medium text-gray-800 dark:text-gray-200">Cerrar sesion</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Cierra tu sesion actual en este dispositivo.
                        </p>
                        <Button
                            variant="secondary"
                            size="sm"
                            class="mt-3"
                            @click="cerrarSesion"
                            :loading="cerrandoSesion"
                        >
                            <ArrowRightOnRectangleIcon class="w-4 h-4 mr-1" />
                            Cerrar sesion
                        </Button>
                    </div>

                    <!-- Restablecer datos -->
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <h4 class="font-medium text-red-800 dark:text-red-300">Restablecer datos</h4>
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                            Elimina todos tus gastos, abonos, categorias, medios de pago y plantillas.
                            Se restauraran las categorias y medios de pago por defecto.
                        </p>
                        <Button
                            variant="danger"
                            size="sm"
                            class="mt-3"
                            @click="showModalRestablecer = true"
                        >
                            <ArrowPathIcon class="w-4 h-4 mr-1" />
                            Restablecer todo
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Categoria -->
        <Modal :show="showModalCategoria" :title="categoriaEditando ? 'Editar Categoria' : 'Nueva Categoria'" @close="cerrarModalCategoria">
            <div class="space-y-4">
                <Input
                    v-model="formCategoria.nombre"
                    label="Nombre"
                    placeholder="Ej: Comida, Transporte..."
                    :error="erroresCategoria.nombre"
                />
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Icono
                    </label>
                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <button
                            v-for="icono in iconosDisponibles"
                            :key="icono"
                            type="button"
                            @click="formCategoria.icono = icono"
                            :class="[
                                'w-9 h-9 flex items-center justify-center rounded-lg border-2 transition-all',
                                formCategoria.icono === icono
                                    ? 'border-primary bg-primary/10 dark:bg-primary/20'
                                    : 'border-transparent hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <i :class="['text-lg', icono]" :style="{ color: formCategoria.color }"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Color
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="color in coloresDisponibles"
                            :key="color"
                            type="button"
                            @click="formCategoria.color = color"
                            :class="[
                                'w-8 h-8 rounded-full border-2 transition-all',
                                formCategoria.color === color ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'
                            ]"
                            :style="{ backgroundColor: color }"
                        ></button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="categoriaActiva"
                        v-model="formCategoria.activo"
                        class="rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <label for="categoriaActiva" class="text-sm text-gray-700 dark:text-gray-300">
                        Categoria activa
                    </label>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="cerrarModalCategoria">Cancelar</Button>
                    <Button @click="guardarCategoria" :loading="guardandoCategoria">
                        {{ categoriaEditando ? 'Actualizar' : 'Crear' }}
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Confirmar Eliminar Categoria -->
        <Modal :show="showModalEliminar" title="Eliminar Categoria" @close="showModalEliminar = false">
            <p class="text-gray-600 dark:text-gray-400">
                Estas seguro de eliminar la categoria <strong>{{ categoriaEliminar?.nombre }}</strong>?
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                Solo se puede eliminar si no tiene gastos asociados.
            </p>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showModalEliminar = false">Cancelar</Button>
                    <Button variant="danger" @click="eliminarCategoria" :loading="eliminandoCategoria">
                        Eliminar
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Medio de Pago -->
        <Modal :show="showModalMedioPago" :title="medioPagoEditando ? 'Editar Medio de Pago' : 'Nuevo Medio de Pago'" @close="cerrarModalMedioPago">
            <div class="space-y-4">
                <Input
                    v-model="formMedioPago.nombre"
                    label="Nombre"
                    placeholder="Ej: Efectivo, Tarjeta Debito..."
                    :error="erroresMedioPago.nombre"
                />
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="medioPagoActivo"
                        v-model="formMedioPago.activo"
                        class="rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <label for="medioPagoActivo" class="text-sm text-gray-700 dark:text-gray-300">
                        Medio de pago activo
                    </label>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="cerrarModalMedioPago">Cancelar</Button>
                    <Button @click="guardarMedioPago" :loading="guardandoMedioPago">
                        {{ medioPagoEditando ? 'Actualizar' : 'Crear' }}
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Confirmar Eliminar Medio de Pago -->
        <Modal :show="showModalEliminarMedioPago" title="Eliminar Medio de Pago" @close="showModalEliminarMedioPago = false">
            <p class="text-gray-600 dark:text-gray-400">
                Estas seguro de eliminar el medio de pago <strong>{{ medioPagoEliminar?.nombre }}</strong>?
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                Solo se puede eliminar si no tiene gastos asociados.
            </p>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showModalEliminarMedioPago = false">Cancelar</Button>
                    <Button variant="danger" @click="eliminarMedioPago" :loading="eliminandoMedioPago">
                        Eliminar
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Servicio -->
        <Modal :show="showModalServicio" :title="servicioEditando ? 'Editar Servicio' : 'Nuevo Servicio'" @close="cerrarModalServicio">
            <div class="space-y-4">
                <Input
                    v-model="formServicio.nombre"
                    label="Nombre"
                    placeholder="Ej: Luz, Agua, Gas..."
                    :error="erroresServicio.nombre"
                />
                <Select
                    v-model="formServicio.categoria_id"
                    label="Categoria asociada"
                    :options="categoriasOptions"
                    placeholder="Selecciona una categoria"
                />
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Valor estimado (opcional)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            {{ divisaInfo.simbolo }}
                        </span>
                        <input
                            type="text"
                            :value="valorEstimadoFormateado"
                            @input="onValorEstimadoInput"
                            placeholder="0"
                            inputmode="decimal"
                            class="w-full pl-8 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        />
                        <button
                            v-if="valorEstimadoFormateado"
                            type="button"
                            @click="limpiarValorEstimado"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <XCircleIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <Input
                    v-model="formServicio.referencia"
                    label="Referencia de pago (opcional)"
                    placeholder="Ej: 123456789"
                />
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Frecuencia de pago
                    </label>
                    <select
                        v-model="formServicio.frecuencia_meses"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary"
                    >
                        <option
                            v-for="opcion in frecuenciasOptions"
                            :key="opcion.value"
                            :value="opcion.value"
                        >
                            {{ opcion.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Icono
                    </label>
                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <button
                            v-for="icono in iconosServicios"
                            :key="icono"
                            type="button"
                            @click="formServicio.icono = icono"
                            :class="[
                                'w-9 h-9 flex items-center justify-center rounded-lg border-2 transition-all',
                                formServicio.icono === icono
                                    ? 'border-primary bg-primary/10 dark:bg-primary/20'
                                    : 'border-transparent hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <i :class="['text-lg', icono]" :style="{ color: formServicio.color }"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Color
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="color in coloresDisponibles"
                            :key="color"
                            type="button"
                            @click="formServicio.color = color"
                            :class="[
                                'w-8 h-8 rounded-full border-2 transition-all',
                                formServicio.color === color ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'
                            ]"
                            :style="{ backgroundColor: color }"
                        ></button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="servicioActivo"
                        v-model="formServicio.activo"
                        class="rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <label for="servicioActivo" class="text-sm text-gray-700 dark:text-gray-300">
                        Servicio activo
                    </label>
                </div>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="cerrarModalServicio">Cancelar</Button>
                    <Button @click="guardarServicio" :loading="guardandoServicio">
                        {{ servicioEditando ? 'Actualizar' : 'Crear' }}
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Confirmar Eliminar Servicio -->
        <Modal :show="showModalEliminarServicio" title="Eliminar Servicio" @close="showModalEliminarServicio = false">
            <p class="text-gray-600 dark:text-gray-400">
                Estas seguro de eliminar el servicio <strong>{{ servicioEliminar?.nombre }}</strong>?
            </p>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showModalEliminarServicio = false">Cancelar</Button>
                    <Button variant="danger" @click="eliminarServicio" :loading="eliminandoServicio">
                        Eliminar
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Confirmar Restablecer Datos -->
        <Modal :show="showModalRestablecer" title="Restablecer todos los datos" @close="showModalRestablecer = false">
            <div class="space-y-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full">
                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <p class="text-center text-gray-600 dark:text-gray-400">
                    Esta accion eliminara <strong>permanentemente</strong> todos tus datos:
                </p>
                <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 list-disc list-inside">
                    <li>Todos los gastos registrados</li>
                    <li>Todos los abonos</li>
                    <li>Todas las categorias personalizadas</li>
                    <li>Todos los medios de pago personalizados</li>
                    <li>Todas las plantillas</li>
                    <li>Todos los gastos recurrentes</li>
                </ul>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                    Se restauraran las categorias y medios de pago por defecto.
                </p>
                <p class="text-sm font-medium text-red-600 dark:text-red-400 text-center">
                    Esta accion no se puede deshacer.
                </p>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showModalRestablecer = false">Cancelar</Button>
                    <Button variant="danger" @click="restablecerDatos" :loading="restableciendo">
                        Si, restablecer todo
                    </Button>
                </div>
            </template>
        </Modal>

        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ChevronDownIcon,
    PaintBrushIcon,
    TagIcon,
    CreditCardIcon,
    UserIcon,
    UsersIcon,
    ArrowPathIcon,
    ArrowRightOnRectangleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    ShareIcon,
    ClipboardDocumentIcon
} from '@heroicons/vue/24/outline';
import Card from '../Components/UI/Card.vue';
import Input from '../Components/UI/Input.vue';
import Button from '../Components/UI/Button.vue';
import Modal from '../Components/UI/Modal.vue';
import Toast from '../Components/UI/Toast.vue';
import { useThemeStore } from '../Stores/theme';
import { useMediosPagoStore } from '../Stores/mediosPago';
import { useCategoriasStore } from '../Stores/categorias';
import { useConfigStore } from '../Stores/config';
import { useServiciosStore } from '../Stores/servicios';
import { useAuthStore } from '../Stores/auth';
import { useDataShareStore } from '../Stores/dataShare';
import Select from '../Components/UI/Select.vue';
import axios from 'axios';
import { useCurrency } from '../Composables/useCurrency';

const router = useRouter();
const route = useRoute();
const themeStore = useThemeStore();
const mediosPagoStore = useMediosPagoStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();
const serviciosStore = useServiciosStore();
const authStore = useAuthStore();
const dataShareStore = useDataShareStore();
const { formatInputValue, parseFormattedValue, divisaInfo } = useCurrency();

// Secciones del acordeon
const seccionesAbiertas = reactive({
    apariencia: false,
    categorias: false,
    mediosPago: false,
    servicios: false,
    compartidos: false,
    compartirDatos: false,
    cuenta: false
});

const toggleSeccion = (seccion) => {
    seccionesAbiertas[seccion] = !seccionesAbiertas[seccion];
};

const temas = [
    { value: 'light', label: 'Claro' },
    { value: 'dark', label: 'Oscuro' },
    { value: 'system', label: 'Sistema' }
];

const coloresDisponibles = [
    '#EF4444', // red
    '#F97316', // orange
    '#F59E0B', // amber
    '#EAB308', // yellow
    '#84CC16', // lime
    '#22C55E', // green
    '#10B981', // emerald
    '#14B8A6', // teal
    '#06B6D4', // cyan
    '#0EA5E9', // sky
    '#3B82F6', // blue
    '#6366F1', // indigo
    '#8B5CF6', // violet
    '#A855F7', // purple
    '#D946EF', // fuchsia
    '#EC4899', // pink
];

// Iconos de PrimeIcons para categorias de gastos
const iconosDisponibles = [
    'pi pi-shopping-cart',
    'pi pi-shopping-bag',
    'pi pi-car',
    'pi pi-home',
    'pi pi-bolt',
    'pi pi-phone',
    'pi pi-wifi',
    'pi pi-heart',
    'pi pi-gift',
    'pi pi-ticket',
    'pi pi-wallet',
    'pi pi-credit-card',
    'pi pi-money-bill',
    'pi pi-building',
    'pi pi-briefcase',
    'pi pi-book',
    'pi pi-graduation-cap',
    'pi pi-users',
    'pi pi-user',
    'pi pi-wrench',
    'pi pi-cog',
    'pi pi-desktop',
    'pi pi-mobile',
    'pi pi-tablet',
    'pi pi-camera',
    'pi pi-video',
    'pi pi-headphones',
    'pi pi-microphone',
    'pi pi-sun',
    'pi pi-cloud',
    'pi pi-star',
    'pi pi-flag',
    'pi pi-map-marker',
    'pi pi-globe',
    'pi pi-compass',
    'pi pi-send',
    'pi pi-inbox',
    'pi pi-tag',
    'pi pi-tags',
    'pi pi-percentage',
    'pi pi-chart-bar',
    'pi pi-chart-line',
    'pi pi-clock',
    'pi pi-calendar',
    'pi pi-trophy',
    'pi pi-sparkles',
    'pi pi-face-smile',
    'pi pi-palette',
    'pi pi-scissors',
    'pi pi-hammer',
];

// Toast
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const mostrarToast = (mensaje, tipo = 'success') => {
    toastMessage.value = mensaje;
    toastType.value = tipo;
    showToast.value = true;
};

// Categoria Modal
const showModalCategoria = ref(false);
const categoriaEditando = ref(null);
const guardandoCategoria = ref(false);
const erroresCategoria = reactive({});

const formCategoria = reactive({
    nombre: '',
    icono: 'pi pi-tag',
    color: '#3B82F6',
    activo: true
});

const abrirModalCategoria = (categoria = null) => {
    categoriaEditando.value = categoria;
    if (categoria) {
        formCategoria.nombre = categoria.nombre;
        formCategoria.icono = categoria.icono || 'pi pi-tag';
        formCategoria.color = categoria.color;
        formCategoria.activo = categoria.activo;
    } else {
        formCategoria.nombre = '';
        formCategoria.icono = 'pi pi-tag';
        formCategoria.color = '#3B82F6';
        formCategoria.activo = true;
    }
    Object.keys(erroresCategoria).forEach(key => delete erroresCategoria[key]);
    showModalCategoria.value = true;
};

const cerrarModalCategoria = () => {
    showModalCategoria.value = false;
    categoriaEditando.value = null;
};

const guardarCategoria = async () => {
    Object.keys(erroresCategoria).forEach(key => delete erroresCategoria[key]);

    if (!formCategoria.nombre.trim()) {
        erroresCategoria.nombre = 'El nombre es requerido';
        return;
    }

    guardandoCategoria.value = true;
    try {
        if (categoriaEditando.value) {
            await categoriasStore.actualizarCategoria(categoriaEditando.value.id, {
                nombre: formCategoria.nombre,
                icono: formCategoria.icono,
                color: formCategoria.color,
                activo: formCategoria.activo
            });
            mostrarToast('Categoria actualizada');
        } else {
            await categoriasStore.crearCategoria({
                nombre: formCategoria.nombre,
                icono: formCategoria.icono,
                color: formCategoria.color,
                activo: formCategoria.activo
            });
            mostrarToast('Categoria creada');
        }
        cerrarModalCategoria();
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al guardar';
        mostrarToast(mensaje, 'error');
    } finally {
        guardandoCategoria.value = false;
    }
};

// Eliminar Categoria
const showModalEliminar = ref(false);
const categoriaEliminar = ref(null);
const eliminandoCategoria = ref(false);

const confirmarEliminarCategoria = (categoria) => {
    categoriaEliminar.value = categoria;
    showModalEliminar.value = true;
};

const eliminarCategoria = async () => {
    if (!categoriaEliminar.value) return;

    eliminandoCategoria.value = true;
    try {
        await categoriasStore.eliminarCategoria(categoriaEliminar.value.id);
        mostrarToast('Categoria eliminada');
        showModalEliminar.value = false;
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al eliminar';
        mostrarToast(mensaje, 'error');
    } finally {
        eliminandoCategoria.value = false;
    }
};

// Medios de Pago
const showModalMedioPago = ref(false);
const medioPagoEditando = ref(null);
const guardandoMedioPago = ref(false);
const erroresMedioPago = reactive({});

const formMedioPago = reactive({
    nombre: '',
    activo: true
});

const abrirModalMedioPago = (medioPago = null) => {
    medioPagoEditando.value = medioPago;
    if (medioPago) {
        formMedioPago.nombre = medioPago.nombre;
        formMedioPago.activo = medioPago.activo;
    } else {
        formMedioPago.nombre = '';
        formMedioPago.activo = true;
    }
    Object.keys(erroresMedioPago).forEach(key => delete erroresMedioPago[key]);
    showModalMedioPago.value = true;
};

const cerrarModalMedioPago = () => {
    showModalMedioPago.value = false;
    medioPagoEditando.value = null;
};

const guardarMedioPago = async () => {
    Object.keys(erroresMedioPago).forEach(key => delete erroresMedioPago[key]);

    if (!formMedioPago.nombre.trim()) {
        erroresMedioPago.nombre = 'El nombre es requerido';
        return;
    }

    guardandoMedioPago.value = true;
    try {
        if (medioPagoEditando.value) {
            await mediosPagoStore.actualizarMedioPago(medioPagoEditando.value.id, {
                nombre: formMedioPago.nombre,
                activo: formMedioPago.activo
            });
            mostrarToast('Medio de pago actualizado');
        } else {
            await mediosPagoStore.crearMedioPago({
                nombre: formMedioPago.nombre,
                activo: formMedioPago.activo
            });
            mostrarToast('Medio de pago creado');
        }
        cerrarModalMedioPago();
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al guardar';
        mostrarToast(mensaje, 'error');
    } finally {
        guardandoMedioPago.value = false;
    }
};

// Eliminar Medio de Pago
const showModalEliminarMedioPago = ref(false);
const medioPagoEliminar = ref(null);
const eliminandoMedioPago = ref(false);

const confirmarEliminarMedioPago = (medioPago) => {
    medioPagoEliminar.value = medioPago;
    showModalEliminarMedioPago.value = true;
};

const eliminarMedioPago = async () => {
    if (!medioPagoEliminar.value) return;

    eliminandoMedioPago.value = true;
    try {
        await mediosPagoStore.eliminarMedioPago(medioPagoEliminar.value.id);
        mostrarToast('Medio de pago eliminado');
        showModalEliminarMedioPago.value = false;
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al eliminar';
        mostrarToast(mensaje, 'error');
    } finally {
        eliminandoMedioPago.value = false;
    }
};

// Theme
const cambiarTema = (tema) => {
    themeStore.setTema(tema);
};

// Divisa
const cambiarDivisa = async (divisa) => {
    try {
        await configStore.actualizarDivisa(divisa);
        mostrarToast('Divisa actualizada');
    } catch (error) {
        mostrarToast('Error al actualizar divisa', 'error');
    }
};

// Formato de Divisa
const cambiarFormatoDivisa = async (formato) => {
    try {
        await configStore.actualizarFormatoDivisa(formato);
        mostrarToast('Formato de divisa actualizado');
    } catch (error) {
        mostrarToast('Error al actualizar formato', 'error');
    }
};

// Servicios
const showModalServicio = ref(false);
const servicioEditando = ref(null);
const guardandoServicio = ref(false);
const erroresServicio = reactive({});
const diaRestablecimiento = ref(1);
const guardandoDia = ref(false);

// Long-press para copiar referencia
const longPressTimer = ref(null);
const longPressServiceId = ref(null);
const LONG_PRESS_DURATION = 2000;

const handleServiceTouchStart = (servicio) => {
    if (!servicio.referencia) return;

    longPressServiceId.value = servicio.id;
    longPressTimer.value = setTimeout(() => {
        copiarReferencia(servicio);
    }, LONG_PRESS_DURATION);
};

const handleServiceTouchEnd = () => {
    if (longPressTimer.value) {
        clearTimeout(longPressTimer.value);
        longPressTimer.value = null;
    }
    longPressServiceId.value = null;
};

const copiarReferencia = async (servicio) => {
    try {
        await navigator.clipboard.writeText(servicio.referencia);

        // Vibrar si esta disponible
        if (navigator.vibrate) {
            navigator.vibrate(100);
        }

        mostrarToast(`Referencia copiada: ${servicio.referencia}`);
    } catch (error) {
        mostrarToast('Error al copiar referencia', 'error');
    }
    longPressServiceId.value = null;
};

const iconosServicios = [
    'pi pi-bolt',
    'pi pi-sun',
    'pi pi-cloud',
    'pi pi-wifi',
    'pi pi-phone',
    'pi pi-mobile',
    'pi pi-home',
    'pi pi-building',
    'pi pi-car',
    'pi pi-send',
    'pi pi-inbox',
    'pi pi-credit-card',
    'pi pi-wallet',
    'pi pi-file',
    'pi pi-file-edit',
    'pi pi-cog',
    'pi pi-wrench',
    'pi pi-heart',
    'pi pi-shield',
    'pi pi-lock',
];

const formServicio = reactive({
    nombre: '',
    categoria_id: '',
    icono: 'pi pi-file',
    color: '#06B6D4',
    valor_estimado: null,
    referencia: '',
    frecuencia_meses: 1,
    activo: true
});

const frecuenciasOptions = [
    { value: 1, label: 'Mensual' },
    { value: 2, label: 'Bimestral (cada 2 meses)' },
    { value: 3, label: 'Trimestral (cada 3 meses)' },
    { value: 4, label: 'Cuatrimestral (cada 4 meses)' },
    { value: 6, label: 'Semestral (cada 6 meses)' },
    { value: 12, label: 'Anual' }
];

// Valor estimado formateado
const valorEstimadoFormateado = ref('');

const onValorEstimadoInput = (event) => {
    const inputValue = event.target.value;
    valorEstimadoFormateado.value = formatInputValue(inputValue);
    formServicio.valor_estimado = parseFormattedValue(inputValue);
};

const limpiarValorEstimado = () => {
    valorEstimadoFormateado.value = '';
    formServicio.valor_estimado = null;
};

const categoriasOptions = computed(() => [
    { value: '', label: 'Sin categoria' },
    ...categoriasStore.activas.map(c => ({ value: c.id, label: c.nombre }))
]);

const abrirModalServicio = (servicio = null) => {
    servicioEditando.value = servicio;
    if (servicio) {
        formServicio.nombre = servicio.nombre;
        formServicio.categoria_id = servicio.categoria_id || '';
        formServicio.icono = servicio.icono || 'pi pi-file';
        formServicio.color = servicio.color || '#06B6D4';
        formServicio.valor_estimado = servicio.valor_estimado;
        formServicio.referencia = servicio.referencia || '';
        formServicio.frecuencia_meses = servicio.frecuencia_meses || 1;
        formServicio.activo = servicio.activo;
        valorEstimadoFormateado.value = servicio.valor_estimado ? formatInputValue(servicio.valor_estimado) : '';
    } else {
        formServicio.nombre = '';
        formServicio.categoria_id = '';
        formServicio.icono = 'pi pi-file';
        formServicio.color = '#06B6D4';
        formServicio.valor_estimado = null;
        formServicio.referencia = '';
        formServicio.frecuencia_meses = 1;
        formServicio.activo = true;
        valorEstimadoFormateado.value = '';
    }
    Object.keys(erroresServicio).forEach(key => delete erroresServicio[key]);
    showModalServicio.value = true;
};

const cerrarModalServicio = () => {
    showModalServicio.value = false;
    servicioEditando.value = null;
};

const guardarServicio = async () => {
    Object.keys(erroresServicio).forEach(key => delete erroresServicio[key]);

    if (!formServicio.nombre.trim()) {
        erroresServicio.nombre = 'El nombre es requerido';
        return;
    }

    guardandoServicio.value = true;
    try {
        const data = {
            nombre: formServicio.nombre,
            categoria_id: formServicio.categoria_id || null,
            icono: formServicio.icono,
            color: formServicio.color,
            valor_estimado: formServicio.valor_estimado || null,
            referencia: formServicio.referencia || null,
            frecuencia_meses: formServicio.frecuencia_meses,
            activo: formServicio.activo
        };

        if (servicioEditando.value) {
            await serviciosStore.actualizarServicio(servicioEditando.value.id, data);
            mostrarToast('Servicio actualizado');
        } else {
            await serviciosStore.crearServicio(data);
            mostrarToast('Servicio creado');
        }
        cerrarModalServicio();
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al guardar';
        mostrarToast(mensaje, 'error');
    } finally {
        guardandoServicio.value = false;
    }
};

// Eliminar Servicio
const showModalEliminarServicio = ref(false);
const servicioEliminar = ref(null);
const eliminandoServicio = ref(false);

const confirmarEliminarServicio = (servicio) => {
    servicioEliminar.value = servicio;
    showModalEliminarServicio.value = true;
};

const eliminarServicio = async () => {
    if (!servicioEliminar.value) return;

    eliminandoServicio.value = true;
    try {
        await serviciosStore.eliminarServicio(servicioEliminar.value.id);
        mostrarToast('Servicio eliminado');
        showModalEliminarServicio.value = false;
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al eliminar';
        mostrarToast(mensaje, 'error');
    } finally {
        eliminandoServicio.value = false;
    }
};

const guardarDiaRestablecimiento = async () => {
    if (diaRestablecimiento.value < 1 || diaRestablecimiento.value > 31) {
        mostrarToast('El dia debe estar entre 1 y 31', 'error');
        return;
    }

    guardandoDia.value = true;
    try {
        await axios.put('/api/configuracion', {
            dia_restablecimiento_servicios: diaRestablecimiento.value
        });
        // Actualizar el usuario en el store y localStorage
        if (authStore.user) {
            authStore.user.dia_restablecimiento_servicios = diaRestablecimiento.value;
            localStorage.setItem('finanzas_auth_user', JSON.stringify(authStore.user));
        }
        mostrarToast('Dia de restablecimiento actualizado');
    } catch (error) {
        mostrarToast('Error al actualizar', 'error');
    } finally {
        guardandoDia.value = false;
    }
};

// Gastos Compartidos
const formCompartidos = reactive({
    nombre_persona_1: 'Persona 1',
    nombre_persona_2: '',
    porcentaje_persona_1: 50,
    porcentaje_persona_2: 50
});
const guardandoCompartidos = ref(false);

const tieneUsuario2Form = computed(() => {
    return formCompartidos.nombre_persona_2 && formCompartidos.nombre_persona_2.trim() !== '';
});

const ajustarPorcentaje1 = () => {
    formCompartidos.porcentaje_persona_2 = 100 - formCompartidos.porcentaje_persona_1;
};

const ajustarPorcentaje2 = () => {
    formCompartidos.porcentaje_persona_1 = 100 - formCompartidos.porcentaje_persona_2;
};

const guardarGastosCompartidos = async () => {
    // Solo validar porcentajes si hay usuario 2
    if (tieneUsuario2Form.value && formCompartidos.porcentaje_persona_1 + formCompartidos.porcentaje_persona_2 !== 100) {
        mostrarToast('Los porcentajes deben sumar 100%', 'error');
        return;
    }

    guardandoCompartidos.value = true;
    try {
        await configStore.actualizarGastosCompartidos({
            nombre_persona_1: formCompartidos.nombre_persona_1,
            nombre_persona_2: formCompartidos.nombre_persona_2,
            porcentaje_persona_1: formCompartidos.porcentaje_persona_1,
            porcentaje_persona_2: formCompartidos.porcentaje_persona_2
        });
        mostrarToast('Configuracion de gastos compartidos actualizada');
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al guardar';
        mostrarToast(mensaje, 'error');
    } finally {
        guardandoCompartidos.value = false;
    }
};

// Cerrar sesion
const cerrandoSesion = ref(false);

const cerrarSesion = async () => {
    cerrandoSesion.value = true;
    try {
        await authStore.logout();
        router.push('/login');
    } catch (error) {
        mostrarToast('Error al cerrar sesion', 'error');
    } finally {
        cerrandoSesion.value = false;
    }
};

// Compartir datos
const emailInvitacion = ref('');
const enviandoInvitacion = ref(false);
const revocandoAcceso = ref(false);

const invitarPersona = async () => {
    if (!emailInvitacion.value) return;

    enviandoInvitacion.value = true;
    const result = await dataShareStore.inviteUser(emailInvitacion.value);
    enviandoInvitacion.value = false;

    if (result.success) {
        emailInvitacion.value = '';
        mostrarToast('Invitacion enviada');
    } else {
        mostrarToast(result.error || 'Error al enviar invitacion', 'error');
    }
};

const revocarAcceso = async () => {
    revocandoAcceso.value = true;
    const result = await dataShareStore.revokeAccess();
    revocandoAcceso.value = false;

    if (result.success) {
        mostrarToast('Acceso revocado');
    } else {
        mostrarToast(result.error || 'Error al revocar acceso', 'error');
    }
};

// Restablecer datos
const showModalRestablecer = ref(false);
const restableciendo = ref(false);

const restablecerDatos = async () => {
    restableciendo.value = true;
    try {
        await axios.post('/api/auth/reset-user-data');

        // Recargar los stores con los nuevos datos por defecto
        await Promise.all([
            mediosPagoStore.cargarMediosPago(),
            categoriasStore.cargarCategorias(),
            serviciosStore.cargarServicios(),
            configStore.cargarConfiguracion()
        ]);

        showModalRestablecer.value = false;
        mostrarToast('Todos los datos han sido restablecidos');
    } catch (error) {
        const mensaje = error.response?.data?.message || 'Error al restablecer datos';
        mostrarToast(mensaje, 'error');
    } finally {
        restableciendo.value = false;
    }
};

// Cargar datos
onMounted(async () => {
    await Promise.all([
        mediosPagoStore.cargarMediosPago(),
        categoriasStore.cargarCategorias(),
        serviciosStore.cargarServicios(),
        configStore.cargarConfiguracion(),
        dataShareStore.fetchMyShareStatus()
    ]);

    // Cargar dia de restablecimiento del usuario
    if (authStore.user?.dia_restablecimiento_servicios) {
        diaRestablecimiento.value = authStore.user.dia_restablecimiento_servicios;
    }

    // Cargar configuracion de gastos compartidos
    formCompartidos.nombre_persona_1 = configStore.nombre_persona_1;
    formCompartidos.nombre_persona_2 = configStore.nombre_persona_2;
    formCompartidos.porcentaje_persona_1 = configStore.porcentaje_persona_1;
    formCompartidos.porcentaje_persona_2 = configStore.porcentaje_persona_2;

    // Abrir seccion si viene por query param
    const seccion = route.query.seccion;
    if (seccion && seccionesAbiertas.hasOwnProperty(seccion)) {
        seccionesAbiertas[seccion] = true;
    }
});
</script>
