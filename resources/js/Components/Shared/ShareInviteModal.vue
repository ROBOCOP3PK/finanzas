<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>

        <!-- Modal -->
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Compartir mis datos
            </h3>

            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Invita a alguien para que pueda ver tus gastos y crear solicitudes de gasto.
                </p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email del invitado
                    </label>
                    <input
                        v-model="email"
                        type="email"
                        placeholder="persona@ejemplo.com"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        :class="{ 'border-red-500': error }"
                        @keyup.enter="sendInvite"
                    />
                    <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                    <p class="text-sm text-amber-700 dark:text-amber-400 font-medium mb-2">
                        El invitado podra:
                    </p>
                    <ul class="text-sm text-amber-600 dark:text-amber-500 space-y-1 list-disc list-inside">
                        <li>Ver tu dashboard y resumen</li>
                        <li>Ver tu historial de gastos</li>
                        <li>Crear solicitudes de gasto (requieren tu aprobacion)</li>
                    </ul>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button
                    @click="$emit('close')"
                    class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="sendInvite"
                    :disabled="!email || loading"
                    class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <span v-if="loading" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>Enviar Invitacion</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    loading: Boolean,
    error: String
});

const emit = defineEmits(['close', 'invite']);

const email = ref('');

const sendInvite = () => {
    if (email.value && !props.loading) {
        emit('invite', email.value);
    }
};

// Limpiar email cuando se cierra el modal
watch(() => props.show, (newVal) => {
    if (!newVal) {
        email.value = '';
    }
});
</script>
