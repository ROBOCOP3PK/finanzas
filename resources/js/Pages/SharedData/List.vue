<template>
    <AppLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                Datos Compartidos
            </h1>

            <!-- Loading -->
            <div v-if="loading" class="flex justify-center py-8">
                <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full"></div>
            </div>

            <!-- Sin acceso -->
            <div v-else-if="activeShares.length === 0" class="text-center py-12">
                <UsersIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Sin datos compartidos
                </h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Cuando alguien comparta sus datos financieros contigo, apareceran aqui.
                </p>
            </div>

            <!-- Lista de comparticiones activas -->
            <div v-else class="space-y-3">
                <router-link
                    v-for="share in activeShares"
                    :key="share.id"
                    :to="`/shared-data/${share.id}`"
                    class="block bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <UserIcon class="w-6 h-6 text-primary" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                {{ share.owner?.name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ share.owner?.email }}
                            </p>
                        </div>
                        <ChevronRightIcon class="w-5 h-5 text-gray-400" />
                    </div>
                </router-link>
            </div>

            <!-- Invitaciones pendientes -->
            <div v-if="pendingInvitations.length > 0" class="mt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                    Invitaciones Pendientes
                </h2>
                <div class="space-y-3">
                    <div
                        v-for="invite in pendingInvitations"
                        :key="invite.id"
                        class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 border border-amber-200 dark:border-amber-800"
                    >
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                                <EnvelopeIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    {{ invite.owner?.name }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    Quiere compartir sus datos financieros contigo
                                </p>
                                <div class="flex gap-2">
                                    <button
                                        @click="acceptInvitation(invite.id)"
                                        :disabled="processingId === invite.id"
                                        class="px-4 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 disabled:opacity-50"
                                    >
                                        Aceptar
                                    </button>
                                    <button
                                        @click="rejectInvitation(invite.id)"
                                        :disabled="processingId === invite.id"
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
                                    >
                                        Rechazar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { UsersIcon, UserIcon, ChevronRightIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';
import AppLayout from '../../Components/Layout/AppLayout.vue';
import Toast from '../../Components/UI/Toast.vue';
import { useDataShareStore } from '../../Stores/dataShare';

const dataShareStore = useDataShareStore();

const loading = ref(true);
const processingId = ref(null);
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const activeShares = computed(() => dataShareStore.activeShares);
const pendingInvitations = computed(() => dataShareStore.pendingInvitations);

onMounted(async () => {
    await dataShareStore.fetchSharedWithMe();
    loading.value = false;
});

const acceptInvitation = async (id) => {
    processingId.value = id;
    const result = await dataShareStore.acceptInvitation(id);
    processingId.value = null;

    if (result.success) {
        toastMessage.value = 'Invitacion aceptada';
        toastType.value = 'success';
    } else {
        toastMessage.value = result.error || 'Error al aceptar';
        toastType.value = 'error';
    }
    showToast.value = true;
};

const rejectInvitation = async (id) => {
    processingId.value = id;
    const result = await dataShareStore.rejectInvitation(id);
    processingId.value = null;

    if (result.success) {
        toastMessage.value = 'Invitacion rechazada';
        toastType.value = 'success';
    } else {
        toastMessage.value = result.error || 'Error al rechazar';
        toastType.value = 'error';
    }
    showToast.value = true;
};
</script>
