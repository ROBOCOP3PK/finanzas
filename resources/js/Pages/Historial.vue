<template>
    <div class="p-4 max-w-full overflow-hidden">
        <div class="flex justify-end gap-2 mb-4">
            <Button variant="secondary" size="sm" @click="abrirModalCompartir">
                <ShareIcon class="w-4 h-4 mr-1" />
                Compartir
            </Button>
            <Button variant="secondary" size="sm" @click="abrirModalExportar">
                Exportar
            </Button>
        </div>

        <!-- Modal Exportar -->
        <div v-if="mostrarModalExportar" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <Card class="w-full max-w-md">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exportar Gastos</h2>

                <div class="space-y-4">
                    <!-- Checkbox exportar todos -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="filtrosExport.exportar_todos"
                            class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Exportar todos los registros (desde siempre)
                        </span>
                    </label>

                    <!-- Rango de fechas (deshabilitado si exportar_todos está activo) -->
                    <div :class="{ 'opacity-50 pointer-events-none': filtrosExport.exportar_todos }">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">O selecciona un rango de fechas:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0">
                            <Input
                                v-model="filtrosExport.desde"
                                type="date"
                                label="Desde"
                                :disabled="filtrosExport.exportar_todos"
                            />
                            <Input
                                v-model="filtrosExport.hasta"
                                type="date"
                                label="Hasta"
                                :disabled="filtrosExport.exportar_todos"
                            />
                        </div>
                    </div>

                    <!-- Filtros adicionales opcionales -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <Select
                            v-model="filtrosExport.tipo"
                            label="Tipo"
                            :options="tiposOptions"
                        />
                        <Select
                            v-model="filtrosExport.categoria_id"
                            label="Categoría"
                            :options="categoriasOptions"
                        />
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <Button variant="ghost" class="flex-1" @click="cerrarModalExportar">
                        Cancelar
                    </Button>
                    <Button
                        class="flex-1"
                        @click="exportar"
                        :disabled="exportando || (!filtrosExport.exportar_todos && !filtrosExport.desde && !filtrosExport.hasta)"
                    >
                        {{ exportando ? 'Exportando...' : 'Descargar CSV' }}
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Modal Compartir -->
        <div v-if="mostrarModalCompartir" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <Card class="w-full max-w-md">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Compartir Gastos</h2>

                <div class="space-y-4">
                    <!-- Opciones de rango -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="opcionCompartir"
                                value="actuales"
                                class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Gastos mostrados en pantalla ({{ gastosStore.gastos.length }})
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="opcionCompartir"
                                value="filtrados"
                                class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Todos los gastos con filtros actuales
                            </span>
                        </label>
                    </div>

                    <!-- Botones de compartir -->
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            @click="compartirWhatsApp"
                            :disabled="generandoCompartir"
                            class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                        >
                            <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center mb-2">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Texto formateado</span>
                        </button>
                        <button
                            @click="generarPDF"
                            :disabled="generandoCompartir"
                            class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        >
                            <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center mb-2">
                                <DocumentTextIcon class="w-6 h-6 text-white" />
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">PDF</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Documento</span>
                        </button>
                    </div>

                    <p v-if="generandoCompartir" class="text-center text-sm text-gray-500 dark:text-gray-400">
                        Generando...
                    </p>
                </div>

                <div class="flex gap-2 mt-6">
                    <Button variant="ghost" class="w-full" @click="cerrarModalCompartir">
                        Cerrar
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Filtros -->
        <Card class="mb-4">
            <div class="space-y-3 min-w-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0">
                    <Input
                        v-model="filtros.desde"
                        type="date"
                        label="Desde"
                        @change="aplicarFiltros"
                    />
                    <Input
                        v-model="filtros.hasta"
                        type="date"
                        label="Hasta"
                        @change="aplicarFiltros"
                    />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-w-0">
                    <Select
                        v-model="filtros.tipo"
                        label="Tipo"
                        :options="tiposOptions"
                        @change="aplicarFiltros"
                    />
                    <Select
                        v-model="filtros.categoria_id"
                        label="Categoría"
                        :options="categoriasOptions"
                        @change="aplicarFiltros"
                    />
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <Button variant="ghost" size="sm" @click="limpiarFiltros">
                    Limpiar filtros
                </Button>
            </div>
        </Card>

        <!-- Lista de gastos -->
        <div v-if="gastosStore.loading" class="text-center py-8">
            <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto"></div>
        </div>

        <div v-else-if="gastosStore.gastos.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No hay gastos que coincidan con los filtros
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div
                v-for="gasto in gastosStore.gastos"
                :key="gasto.id"
                @click="editarGasto(gasto.id)"
                class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-4"
            >
                <GastoItem :gasto="gasto" />
            </div>
        </div>

        <!-- Paginación -->
        <div v-if="gastosStore.meta.last_page > 1" class="flex justify-center gap-2 mt-4">
            <Button
                variant="secondary"
                size="sm"
                :disabled="gastosStore.meta.current_page === 1"
                @click="cargarPagina(gastosStore.meta.current_page - 1)"
            >
                Anterior
            </Button>
            <span class="py-2 px-3 text-sm text-gray-600 dark:text-gray-400">
                {{ gastosStore.meta.current_page }} / {{ gastosStore.meta.last_page }}
            </span>
            <Button
                variant="secondary"
                size="sm"
                :disabled="gastosStore.meta.current_page === gastosStore.meta.last_page"
                @click="cargarPagina(gastosStore.meta.current_page + 1)"
            >
                Siguiente
            </Button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ShareIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import Card from '../Components/UI/Card.vue';
import Input from '../Components/UI/Input.vue';
import Select from '../Components/UI/Select.vue';
import Button from '../Components/UI/Button.vue';
import GastoItem from '../Components/Gastos/GastoItem.vue';
import { useGastosStore } from '../Stores/gastos';
import { useCategoriasStore } from '../Stores/categorias';
import { useConfigStore } from '../Stores/config';
import { useCurrency } from '../Composables/useCurrency';

const router = useRouter();
const gastosStore = useGastosStore();
const categoriasStore = useCategoriasStore();
const configStore = useConfigStore();
const { formatCurrency } = useCurrency();

const filtros = ref({
    desde: '',
    hasta: '',
    tipo: '',
    categoria_id: ''
});

// Estado para modal de exportación
const mostrarModalExportar = ref(false);
const exportando = ref(false);
const filtrosExport = ref({
    desde: '',
    hasta: '',
    tipo: '',
    categoria_id: '',
    exportar_todos: false
});

onMounted(async () => {
    await Promise.all([
        gastosStore.cargarGastos(),
        categoriasStore.cargarCategorias(true),
        configStore.cargarConfiguracion()
    ]);
});

const tiposOptions = computed(() => [
    { value: '', label: 'Todos' },
    ...configStore.tiposGasto
]);

const categoriasOptions = computed(() => [
    { value: '', label: 'Todas' },
    ...categoriasStore.activas.map(c => ({ value: c.id, label: c.nombre }))
]);

const aplicarFiltros = () => {
    gastosStore.setFiltros(filtros.value);
    gastosStore.cargarGastos(1);
};

const limpiarFiltros = () => {
    filtros.value = { desde: '', hasta: '', tipo: '', categoria_id: '' };
    gastosStore.limpiarFiltros();
    gastosStore.cargarGastos(1);
};

const cargarPagina = (page) => {
    gastosStore.cargarGastos(page);
};

const editarGasto = (id) => {
    router.push(`/gastos/${id}/editar`);
};

// Funciones de exportación
const abrirModalExportar = () => {
    // Inicializar con los filtros actuales de la vista
    filtrosExport.value = {
        desde: filtros.value.desde || '',
        hasta: filtros.value.hasta || '',
        tipo: filtros.value.tipo || '',
        categoria_id: filtros.value.categoria_id || '',
        exportar_todos: false
    };
    mostrarModalExportar.value = true;
};

const cerrarModalExportar = () => {
    mostrarModalExportar.value = false;
};

const exportar = async () => {
    exportando.value = true;
    try {
        await gastosStore.exportarGastos(filtrosExport.value);
        cerrarModalExportar();
    } catch (error) {
        console.error('Error exportando:', error);
        alert('Error al exportar los gastos');
    } finally {
        exportando.value = false;
    }
};

// Funciones de compartir
const mostrarModalCompartir = ref(false);
const opcionCompartir = ref('actuales');
const generandoCompartir = ref(false);

const abrirModalCompartir = () => {
    opcionCompartir.value = 'actuales';
    mostrarModalCompartir.value = true;
};

const cerrarModalCompartir = () => {
    mostrarModalCompartir.value = false;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatDateShort = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short'
    });
};

const obtenerGastosParaCompartir = async () => {
    if (opcionCompartir.value === 'actuales') {
        return gastosStore.gastos;
    } else {
        // Cargar todos los gastos con los filtros actuales
        const response = await gastosStore.obtenerTodosGastos(filtros.value);
        return response;
    }
};

const generarTextoWhatsApp = (gastos) => {
    if (gastos.length === 0) return 'No hay gastos para compartir.';

    // Calcular totales
    const total = gastos.reduce((sum, g) => sum + parseFloat(g.valor), 0);
    const porTipo = {};
    gastos.forEach(g => {
        const tipoLabel = configStore.getNombreTipo(g.tipo);
        porTipo[tipoLabel] = (porTipo[tipoLabel] || 0) + parseFloat(g.valor);
    });

    // Determinar rango de fechas
    const fechas = gastos.map(g => new Date(g.fecha)).sort((a, b) => a - b);
    const fechaInicio = formatDate(fechas[0]);
    const fechaFin = formatDate(fechas[fechas.length - 1]);

    let texto = `📊 *RESUMEN DE GASTOS*\n`;
    texto += `📅 ${fechaInicio} - ${fechaFin}\n`;
    texto += `━━━━━━━━━━━━━━━━━━\n\n`;

    // Resumen por tipo
    texto += `💰 *TOTALES POR TIPO:*\n`;
    Object.entries(porTipo).forEach(([tipo, monto]) => {
        texto += `  • ${tipo}: ${formatCurrency(monto)}\n`;
    });
    texto += `\n📌 *TOTAL GENERAL: ${formatCurrency(total)}*\n`;
    texto += `━━━━━━━━━━━━━━━━━━\n\n`;

    // Detalle de gastos (agrupados por fecha)
    texto += `📋 *DETALLE DE GASTOS:*\n\n`;

    const gastosPorFecha = {};
    gastos.forEach(g => {
        const fecha = g.fecha;
        if (!gastosPorFecha[fecha]) {
            gastosPorFecha[fecha] = [];
        }
        gastosPorFecha[fecha].push(g);
    });

    Object.entries(gastosPorFecha)
        .sort(([a], [b]) => new Date(b) - new Date(a))
        .forEach(([fecha, gastosDelDia]) => {
            texto += `📅 *${formatDate(fecha)}*\n`;
            gastosDelDia.forEach(g => {
                const categoria = g.categoria?.nombre || 'Sin categoría';
                const concepto = g.concepto || categoria;
                const tipoLabel = configStore.getNombreTipo(g.tipo);
                texto += `  ${concepto}\n`;
                texto += `  ${formatCurrency(g.valor)} (${tipoLabel})\n`;
                if (g.medio_pago?.nombre) {
                    texto += `  💳 ${g.medio_pago.nombre}\n`;
                }
                texto += `\n`;
            });
        });

    texto += `━━━━━━━━━━━━━━━━━━\n`;
    texto += `_${gastos.length} movimiento(s)_`;

    return texto;
};

const compartirWhatsApp = async () => {
    generandoCompartir.value = true;
    try {
        const gastos = await obtenerGastosParaCompartir();
        const texto = generarTextoWhatsApp(gastos);

        // Usar Web Share API si está disponible, sino abrir WhatsApp Web
        if (navigator.share) {
            await navigator.share({
                text: texto
            });
        } else {
            // Fallback a WhatsApp Web
            const url = `https://wa.me/?text=${encodeURIComponent(texto)}`;
            window.open(url, '_blank');
        }

        cerrarModalCompartir();
    } catch (error) {
        console.error('Error compartiendo:', error);
        if (error.name !== 'AbortError') {
            alert('Error al compartir los gastos');
        }
    } finally {
        generandoCompartir.value = false;
    }
};

const generarPDF = async () => {
    generandoCompartir.value = true;
    try {
        const gastos = await obtenerGastosParaCompartir();

        // Calcular totales
        const total = gastos.reduce((sum, g) => sum + parseFloat(g.valor), 0);
        const porTipo = {};
        gastos.forEach(g => {
            const tipoLabel = configStore.getNombreTipo(g.tipo);
            porTipo[tipoLabel] = (porTipo[tipoLabel] || 0) + parseFloat(g.valor);
        });

        // Determinar rango de fechas
        const fechas = gastos.map(g => new Date(g.fecha)).sort((a, b) => a - b);
        const fechaInicio = formatDate(fechas[0]);
        const fechaFin = formatDate(fechas[fechas.length - 1]);

        // Crear contenido HTML para el PDF
        let html = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Resumen de Gastos</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; color: #333; }
                    h1 { color: #4F46E5; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
                    h2 { color: #6366F1; margin-top: 25px; }
                    .header-info { background: #F3F4F6; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                    .total-box { background: #4F46E5; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; }
                    .total-box h3 { margin: 0; }
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    th { background: #4F46E5; color: white; padding: 12px; text-align: left; }
                    td { padding: 10px; border-bottom: 1px solid #E5E7EB; }
                    tr:nth-child(even) { background: #F9FAFB; }
                    .tipo-personal { color: #3B82F6; }
                    .tipo-pareja { color: #8B5CF6; }
                    .tipo-compartido { color: #10B981; }
                    .monto { text-align: right; font-weight: bold; }
                    .resumen-tipo { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #E5E7EB; }
                    .footer { margin-top: 30px; text-align: center; color: #9CA3AF; font-size: 12px; }
                </style>
            </head>
            <body>
                <h1>📊 Resumen de Gastos</h1>
                <div class="header-info">
                    <strong>Período:</strong> ${fechaInicio} - ${fechaFin}<br>
                    <strong>Total de movimientos:</strong> ${gastos.length}
                </div>

                <h2>💰 Totales por Tipo</h2>
                <div>
        `;

        Object.entries(porTipo).forEach(([tipo, monto]) => {
            html += `<div class="resumen-tipo"><span>${tipo}</span><span>${formatCurrency(monto)}</span></div>`;
        });

        html += `
                </div>

                <div class="total-box">
                    <h3>Total General: ${formatCurrency(total)}</h3>
                </div>

                <h2>📋 Detalle de Gastos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Categoría</th>
                            <th>Tipo</th>
                            <th>Medio Pago</th>
                            <th class="monto">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        gastos.forEach(g => {
            const tipoClass = `tipo-${g.tipo}`;
            html += `
                <tr>
                    <td>${formatDateShort(g.fecha)}</td>
                    <td>${g.concepto || '-'}</td>
                    <td>${g.categoria?.nombre || '-'}</td>
                    <td class="${tipoClass}">${configStore.getNombreTipo(g.tipo)}</td>
                    <td>${g.medio_pago?.nombre || '-'}</td>
                    <td class="monto">${formatCurrency(g.valor)}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>

                <div class="footer">
                    Generado el ${new Date().toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                </div>
            </body>
            </html>
        `;

        // Crear ventana de impresión
        const printWindow = window.open('', '_blank');
        printWindow.document.write(html);
        printWindow.document.close();

        // Esperar a que cargue y luego imprimir/guardar como PDF
        printWindow.onload = () => {
            printWindow.print();
        };

        cerrarModalCompartir();
    } catch (error) {
        console.error('Error generando PDF:', error);
        alert('Error al generar el PDF');
    } finally {
        generandoCompartir.value = false;
    }
};
</script>
