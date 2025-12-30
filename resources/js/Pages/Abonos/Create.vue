<template>
    <div class="p-4 max-w-full overflow-hidden">
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
