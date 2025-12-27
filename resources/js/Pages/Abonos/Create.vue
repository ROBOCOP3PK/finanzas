<template>
    <div class="p-4">
        <div class="flex items-center gap-3 mb-6">
            <button @click="$router.back()" class="p-2 -ml-2 text-gray-600 dark:text-gray-400">
                <ArrowLeftIcon class="w-5 h-5" />
            </button>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Nuevo Abono</h1>
        </div>

        <Card>
            <AbonoForm
                :loading="loading"
                :errors="errors"
                submitText="Guardar Abono"
                @submit="guardar"
            />
        </Card>

        <Toast
            :show="showToast"
            :message="toastMessage"
            :type="toastType"
            @close="showToast = false"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import Card from '../../Components/UI/Card.vue';
import Toast from '../../Components/UI/Toast.vue';
import AbonoForm from '../../Components/Abonos/AbonoForm.vue';
import { useAbonosStore } from '../../Stores/abonos';

const router = useRouter();
const abonosStore = useAbonosStore();

const loading = ref(false);
const errors = ref({});
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const guardar = async (data) => {
    loading.value = true;
    errors.value = {};

    try {
        await abonosStore.crearAbono(data);
        toastMessage.value = 'Abono guardado correctamente';
        toastType.value = 'success';
        showToast.value = true;

        setTimeout(() => {
            router.push('/');
        }, 1000);
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            toastMessage.value = 'Error al guardar el abono';
            toastType.value = 'error';
            showToast.value = true;
        }
    } finally {
        loading.value = false;
    }
};
</script>
