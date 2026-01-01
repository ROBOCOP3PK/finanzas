<template>
    <div class="p-4 max-w-full overflow-hidden">
        <!-- Loading -->
        <div v-if="cargando" class="text-center py-8">
            <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto"></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-8">
            <ExclamationCircleIcon class="w-16 h-16 mx-auto text-red-400 mb-4" />
            <p class="text-gray-600 dark:text-gray-400">{{ error }}</p>
            <Button class="mt-4" @click="router.push('/notificaciones')">
                Volver a notificaciones
            </Button>
        </div>

        <!-- Contenido -->
        <template v-else-if="solicitud">
            <!-- Header con info del solicitante -->
            <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-800 flex items-center justify-center">
                        <UserIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ solicitud.created_by?.name || 'Usuario' }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Solicita registrar un gasto
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario de revision -->
            <Card title="Revisar solicitud">
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <!-- Fecha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha
                        </label>
                        <input
                            type="date"
                            v-model="form.fecha"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        />
                    </div>

                    <!-- Concepto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Concepto
                        </label>
                        <input
                            type="text"
                            v-model="form.concepto"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Descripcion del gasto"
                        />
                    </div>

                    <!-- Valor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Valor
                        </label>
                        <input
                            type="number"
                            v-model="form.valor"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="0.00"
                        />
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Categoria
                        </label>
                        <select
                            v-model="form.categoria_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option :value="null">Sin categoria</option>
                            <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                                {{ cat.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Medio de pago -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Medio de pago
                        </label>
                        <select
                            v-model="form.medio_pago_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option :value="null">Sin medio de pago</option>
                            <option v-for="mp in mediosPago" :key="mp.id" :value="mp.id">
                                {{ mp.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tipo de gasto
                        </label>
                        <select
                            v-model="form.tipo"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                            <option v-for="tipo in configStore.tiposGasto" :key="tipo.value" :value="tipo.value">
                                {{ tipo.label }}
                            </option>
                        </select>
                    </div>
                </form>
            </Card>

            <!-- Botones de accion -->
            <div class="mt-4 space-y-3">
                <!-- Aprobar -->
                <Button
                    class="w-full"
                    :loading="procesando === 'aprobar'"
                    :disabled="procesando !== null"
                    @click="aprobar"
                >
                    <CheckIcon class="w-5 h-5 mr-2" />
                    {{ hayCambios ? 'Aprobar con cambios' : 'Aprobar' }}
                </Button>

                <!-- Rechazar -->
                <Button
                    variant="danger"
                    class="w-full"
                    :disabled="procesando !== null"
                    @click="mostrarModalRechazo = true"
                >
                    <XMarkIcon class="w-5 h-5 mr-2" />
                    Rechazar
                </Button>

                <!-- Cancelar -->
                <Button
                    variant="secondary"
                    class="w-full"
                    :disabled="procesando !== null"
                    @click="router.push('/notificaciones')"
                >
                    Cancelar
                </Button>
            </div>
        </template>

        <!-- Modal rechazo -->
        <Modal :show="mostrarModalRechazo" title="Rechazar solicitud" @close="mostrarModalRechazo = false">
            <div class="space-y-4">
                <p class="text-gray-600 dark:text-gray-400">
                    Indica la razon del rechazo (opcional):
                </p>
                <textarea
                    v-model="razonRechazo"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                    placeholder="Ej: El valor no es correcto, ya existe este gasto..."
                ></textarea>
            </div>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="mostrarModalRechazo = false">
                        Cancelar
                    </Button>
                    <Button
                        variant="danger"
                        :loading="procesando === 'rechazar'"
                        @click="rechazar"
                    >
                        Rechazar solicitud
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Toast -->
        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { UserIcon, CheckIcon, XMarkIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';
import Card from '../Components/UI/Card.vue';
import Button from '../Components/UI/Button.vue';
import Modal from '../Components/UI/Modal.vue';
import Toast from '../Components/UI/Toast.vue';
import { useDataShareStore } from '../Stores/dataShare';
import { useCategoriasStore } from '../Stores/categorias';
import { useMediosPagoStore } from '../Stores/mediosPago';
import { useConfigStore } from '../Stores/config';
import { useShareNotificationsStore } from '../Stores/shareNotifications';

const router = useRouter();
const route = useRoute();
const dataShareStore = useDataShareStore();
const categoriasStore = useCategoriasStore();
const mediosPagoStore = useMediosPagoStore();
const configStore = useConfigStore();
const notificationsStore = useShareNotificationsStore();

const solicitud = ref(null);
const cargando = ref(true);
const error = ref(null);
const procesando = ref(null);
const mostrarModalRechazo = ref(false);
const razonRechazo = ref('');

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Formulario editable
const form = ref({
    fecha: '',
    concepto: '',
    valor: 0,
    categoria_id: null,
    medio_pago_id: null,
    tipo: 'personal'
});

// Datos originales para comparar
const datosOriginales = ref({});

const categorias = computed(() => categoriasStore.categorias);
const mediosPago = computed(() => mediosPagoStore.mediosPago);

// Detectar si hay cambios respecto a la solicitud original
const hayCambios = computed(() => {
    if (!solicitud.value) return false;

    return form.value.fecha !== datosOriginales.value.fecha ||
           form.value.concepto !== datosOriginales.value.concepto ||
           Number(form.value.valor) !== Number(datosOriginales.value.valor) ||
           form.value.categoria_id !== datosOriginales.value.categoria_id ||
           form.value.medio_pago_id !== datosOriginales.value.medio_pago_id ||
           form.value.tipo !== datosOriginales.value.tipo;
});

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha);
    return date.toISOString().split('T')[0];
};

onMounted(async () => {
    const id = route.params.id;

    try {
        // Cargar datos en paralelo
        await Promise.all([
            categoriasStore.cargarCategorias(),
            mediosPagoStore.cargarMediosPago(),
            configStore.cargarConfiguracion()
        ]);

        const result = await dataShareStore.fetchPendingExpense(id);

        if (!result.success) {
            error.value = result.error || 'No se pudo cargar la solicitud';
            return;
        }

        solicitud.value = result.data;

        // Inicializar formulario con datos de la solicitud
        const fechaFormateada = formatearFecha(solicitud.value.fecha);

        form.value = {
            fecha: fechaFormateada,
            concepto: solicitud.value.concepto,
            valor: solicitud.value.valor,
            categoria_id: solicitud.value.categoria_id,
            medio_pago_id: solicitud.value.medio_pago_id,
            tipo: solicitud.value.tipo
        };

        // Guardar datos originales
        datosOriginales.value = { ...form.value };

    } catch (e) {
        error.value = 'Error al cargar la solicitud';
        console.error(e);
    } finally {
        cargando.value = false;
    }
});

const aprobar = async () => {
    procesando.value = 'aprobar';

    try {
        // Si hay cambios, enviar los datos editados
        const editedData = hayCambios.value ? {
            fecha: form.value.fecha,
            concepto: form.value.concepto,
            valor: form.value.valor,
            categoria_id: form.value.categoria_id,
            medio_pago_id: form.value.medio_pago_id,
            tipo: form.value.tipo
        } : null;

        const result = await dataShareStore.approveExpense(solicitud.value.id, editedData);

        if (result.success) {
            toastMessage.value = result.message || 'Gasto aprobado correctamente';
            toastType.value = 'success';
            showToast.value = true;

            // Actualizar contador de notificaciones
            await notificationsStore.fetchUnreadCount();

            setTimeout(() => {
                router.push('/notificaciones');
            }, 1500);
        } else {
            toastMessage.value = result.error || 'Error al aprobar';
            toastType.value = 'error';
            showToast.value = true;
        }
    } catch (e) {
        toastMessage.value = 'Error al aprobar la solicitud';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        procesando.value = null;
    }
};

const rechazar = async () => {
    procesando.value = 'rechazar';

    try {
        const result = await dataShareStore.rejectExpense(
            solicitud.value.id,
            razonRechazo.value || null
        );

        if (result.success) {
            mostrarModalRechazo.value = false;
            toastMessage.value = 'Solicitud rechazada';
            toastType.value = 'success';
            showToast.value = true;

            // Actualizar contador de notificaciones
            await notificationsStore.fetchUnreadCount();

            setTimeout(() => {
                router.push('/notificaciones');
            }, 1500);
        } else {
            toastMessage.value = result.error || 'Error al rechazar';
            toastType.value = 'error';
            showToast.value = true;
        }
    } catch (e) {
        toastMessage.value = 'Error al rechazar la solicitud';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        procesando.value = null;
    }
};
</script>
