<template>
    <!-- Banner de sin conexion -->
    <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-full"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-full"
    >
        <div
            v-if="!offlineStore.isOnline"
            class="fixed top-0 left-0 right-0 z-50 bg-amber-500 text-white px-4 py-2 text-center text-sm font-medium shadow-lg"
        >
            <div class="flex items-center justify-center gap-2">
                <WifiIcon class="w-4 h-4" />
                <span>Sin conexion - Los cambios se guardaran localmente</span>
            </div>
        </div>
    </transition>

    <!-- Badge de operaciones pendientes -->
    <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 scale-75"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-75"
    >
        <div
            v-if="offlineStore.hasPendingOperations && offlineStore.isOnline"
            class="fixed top-4 right-4 z-50"
        >
            <div
                @click="handleSync"
                class="flex items-center gap-2 bg-indigo-600 text-white px-3 py-2 rounded-full shadow-lg cursor-pointer hover:bg-indigo-700 transition-colors"
            >
                <ArrowPathIcon
                    class="w-4 h-4"
                    :class="{ 'animate-spin': offlineStore.isSyncing }"
                />
                <span class="text-sm font-medium">
                    {{ offlineStore.isSyncing ? 'Sincronizando...' : `${offlineStore.pendingCount} pendiente${offlineStore.pendingCount > 1 ? 's' : ''}` }}
                </span>
            </div>
        </div>
    </transition>

    <!-- Toast de sincronizacion exitosa -->
    <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="showSyncSuccess"
            class="fixed bottom-20 left-4 right-4 z-50 flex justify-center"
        >
            <div class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
                <CheckCircleIcon class="w-5 h-5" />
                <span class="text-sm font-medium">Sincronizado correctamente</span>
            </div>
        </div>
    </transition>

    <!-- Toast de guardado offline -->
    <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="showOfflineSaved"
            class="fixed bottom-20 left-4 right-4 z-50 flex justify-center"
        >
            <div class="bg-amber-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
                <CloudArrowUpIcon class="w-5 h-5" />
                <span class="text-sm font-medium">Guardado localmente</span>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { WifiIcon, ArrowPathIcon, CheckCircleIcon, CloudArrowUpIcon } from '@heroicons/vue/24/outline';
import { useOfflineStore } from '../../Stores/offline';

const offlineStore = useOfflineStore();

const showSyncSuccess = ref(false);
const showOfflineSaved = ref(false);
let previousPendingCount = 0;

// Manejar clic en sincronizar
const handleSync = async () => {
    if (!offlineStore.isSyncing) {
        await offlineStore.forceSync();
    }
};

// Escuchar cuando se guarda algo offline
const handleOfflineSaved = () => {
    showOfflineSaved.value = true;
    setTimeout(() => {
        showOfflineSaved.value = false;
    }, 3000);
};

// Observar cambios en pendingCount para mostrar toast de sincronizacion
watch(() => offlineStore.pendingCount, (newCount, oldCount) => {
    // Si bajó a 0 y había pendientes, mostrar exito
    if (newCount === 0 && oldCount > 0 && offlineStore.isOnline) {
        showSyncSuccess.value = true;
        setTimeout(() => {
            showSyncSuccess.value = false;
        }, 3000);
    }
});

// Observar cuando vuelve la conexion
watch(() => offlineStore.isOnline, (isOnline, wasOnline) => {
    if (isOnline && !wasOnline) {
        // Conexion restaurada - intentar sincronizar
        offlineStore.syncPendingOperations();
    }
});

onMounted(() => {
    window.addEventListener('offline-operation-saved', handleOfflineSaved);
    previousPendingCount = offlineStore.pendingCount;
});

onUnmounted(() => {
    window.removeEventListener('offline-operation-saved', handleOfflineSaved);
});
</script>
