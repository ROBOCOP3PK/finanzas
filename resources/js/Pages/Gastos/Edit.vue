<template>
    <div class="p-4">
        <div class="flex items-center gap-3 mb-6">
            <button @click="$router.back()" class="p-2 -ml-2 text-gray-600 dark:text-gray-400">
                <ArrowLeftIcon class="w-5 h-5" />
            </button>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Editar Gasto</h1>
        </div>

        <div v-if="cargando" class="text-center py-8">
            <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto"></div>
        </div>

        <template v-else>
            <Card>
                <GastoForm
                    :gasto="gasto"
                    :loading="loading"
                    :errors="errors"
                    submitText="Actualizar Gasto"
                    @submit="actualizar"
                />
            </Card>

            <div class="mt-4">
                <Button
                    variant="danger"
                    class="w-full"
                    @click="confirmarEliminar"
                >
                    Eliminar Gasto
                </Button>
            </div>
        </template>

        <!-- Modal confirmar eliminar -->
        <Modal :show="showDeleteModal" title="Eliminar Gasto" @close="showDeleteModal = false">
            <p class="text-gray-600 dark:text-gray-400">
                ¿Estás seguro de que deseas eliminar este gasto? Esta acción no se puede deshacer.
            </p>
            <template #footer>
                <div class="flex gap-2 justify-end">
                    <Button variant="secondary" @click="showDeleteModal = false">Cancelar</Button>
                    <Button variant="danger" @click="eliminar" :loading="eliminando">Eliminar</Button>
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
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import Card from '../../Components/UI/Card.vue';
import Button from '../../Components/UI/Button.vue';
import Modal from '../../Components/UI/Modal.vue';
import Toast from '../../Components/UI/Toast.vue';
import GastoForm from '../../Components/Gastos/GastoForm.vue';
import { useGastosStore } from '../../Stores/gastos';

const router = useRouter();
const route = useRoute();
const gastosStore = useGastosStore();

const gasto = ref(null);
const cargando = ref(true);
const loading = ref(false);
const eliminando = ref(false);
const errors = ref({});
const showDeleteModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

onMounted(async () => {
    try {
        gasto.value = await gastosStore.obtenerGasto(route.params.id);
    } catch (error) {
        toastMessage.value = 'Error al cargar el gasto';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        cargando.value = false;
    }
});

const actualizar = async (data) => {
    loading.value = true;
    errors.value = {};

    try {
        await gastosStore.actualizarGasto(route.params.id, data);
        toastMessage.value = 'Gasto actualizado correctamente';
        toastType.value = 'success';
        showToast.value = true;

        setTimeout(() => {
            router.push('/historial');
        }, 1000);
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            toastMessage.value = 'Error al actualizar el gasto';
            toastType.value = 'error';
            showToast.value = true;
        }
    } finally {
        loading.value = false;
    }
};

const confirmarEliminar = () => {
    showDeleteModal.value = true;
};

const eliminar = async () => {
    eliminando.value = true;

    try {
        await gastosStore.eliminarGasto(route.params.id);
        showDeleteModal.value = false;
        toastMessage.value = 'Gasto eliminado correctamente';
        toastType.value = 'success';
        showToast.value = true;

        setTimeout(() => {
            router.push('/historial');
        }, 1000);
    } catch (error) {
        toastMessage.value = 'Error al eliminar el gasto';
        toastType.value = 'error';
        showToast.value = true;
    } finally {
        eliminando.value = false;
    }
};
</script>
