<template>
    <div class="space-y-3">
        <div v-if="expenses.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <CheckCircleIcon class="w-12 h-12 mx-auto mb-2 text-green-500" />
            <p>No hay solicitudes pendientes</p>
        </div>

        <div
            v-for="expense in expenses"
            :key="expense.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700"
        >
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-white">{{ expense.concepto }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ expense.created_by?.name }} - {{ formatDate(expense.fecha) }}
                    </p>
                </div>
                <p class="text-lg font-bold text-gray-900 dark:text-white ml-3">
                    {{ formatCurrency(expense.valor) }}
                </p>
            </div>

            <div class="flex items-center gap-2 text-sm mb-3 flex-wrap">
                <span
                    v-if="expense.categoria"
                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                    :style="{
                        backgroundColor: expense.categoria.color + '20',
                        color: expense.categoria.color
                    }"
                >
                    {{ expense.categoria.nombre }}
                </span>
                <span v-if="expense.medio_pago" class="text-gray-500 dark:text-gray-400">
                    {{ expense.medio_pago.nombre }}
                </span>
                <span
                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                    :class="{
                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': expense.tipo === 'personal',
                        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': expense.tipo === 'pareja',
                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': expense.tipo === 'compartido'
                    }"
                >
                    {{ getTipoLabel(expense.tipo) }}
                </span>
            </div>

            <div class="flex gap-2">
                <button
                    @click="$emit('approve', expense.id)"
                    :disabled="processingId === expense.id"
                    class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50"
                >
                    <CheckIcon class="w-4 h-4" />
                    <span>Aprobar</span>
                </button>
                <button
                    @click="openRejectModal(expense)"
                    :disabled="processingId === expense.id"
                    class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50"
                >
                    <XMarkIcon class="w-4 h-4" />
                    <span>Rechazar</span>
                </button>
            </div>
        </div>

        <!-- Modal Rechazar -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="showRejectModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Rechazar Solicitud
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Rechazar: <strong>{{ selectedExpense?.concepto }}</strong>
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Motivo (opcional)
                    </label>
                    <textarea
                        v-model="rejectReason"
                        rows="3"
                        placeholder="Explica por que rechazas esta solicitud..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                    ></textarea>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="showRejectModal = false"
                        class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="confirmReject"
                        class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                    >
                        Rechazar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { CheckIcon, XMarkIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import { useCurrency } from '../../Composables/useCurrency';

const props = defineProps({
    expenses: {
        type: Array,
        default: () => []
    },
    processingId: Number
});

const emit = defineEmits(['approve', 'reject']);

const { formatCurrency } = useCurrency();

const showRejectModal = ref(false);
const selectedExpense = ref(null);
const rejectReason = ref('');

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short'
    });
};

const getTipoLabel = (tipo) => {
    const labels = {
        personal: 'Personal',
        pareja: 'Usuario 2',
        compartido: 'Compartido'
    };
    return labels[tipo] || tipo;
};

const openRejectModal = (expense) => {
    selectedExpense.value = expense;
    rejectReason.value = '';
    showRejectModal.value = true;
};

const confirmReject = () => {
    emit('reject', selectedExpense.value.id, rejectReason.value);
    showRejectModal.value = false;
    selectedExpense.value = null;
    rejectReason.value = '';
};
</script>
