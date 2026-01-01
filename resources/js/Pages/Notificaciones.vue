<template>
    <div class="p-4 space-y-4 max-w-full overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Notificaciones</h1>
            <button
                v-if="notificationsStore.hasUnread"
                @click="marcarTodasLeidas"
                class="text-sm text-primary dark:text-indigo-400 hover:underline"
            >
                Marcar todas como leidas
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full"></div>
        </div>

        <!-- Sin notificaciones -->
        <div v-else-if="!tieneNotificaciones" class="text-center py-12">
            <BellIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
            <p class="text-gray-500 dark:text-gray-400">No tienes notificaciones</p>
        </div>

        <!-- Lista de notificaciones -->
        <div v-else class="space-y-3">
            <!-- Invitaciones pendientes -->
            <div v-if="dataShareStore.pendingInvitations.length > 0" class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                    Invitaciones pendientes
                </h2>
                <div
                    v-for="invitation in dataShareStore.pendingInvitations"
                    :key="'inv-' + invitation.id"
                    class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4"
                >
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center flex-shrink-0">
                            <UserPlusIcon class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white">Invitacion para ver datos</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                {{ invitation.owner?.name || 'Un usuario' }} quiere compartir sus datos financieros contigo
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ formatearFecha(invitation.created_at) }}
                            </p>
                            <div class="flex gap-2 mt-3">
                                <button
                                    @click="aceptarInvitacion(invitation.id)"
                                    :disabled="processingId === invitation.id"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Aceptar
                                </button>
                                <button
                                    @click="rechazarInvitacion(invitation.id)"
                                    :disabled="processingId === invitation.id"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50"
                                >
                                    Rechazar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gastos pendientes de aprobar (como propietario) -->
            <div v-if="dataShareStore.pendingExpensesCount > 0" class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                    Solicitudes de gastos
                </h2>
                <div
                    @click="irASolicitudes"
                    class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg p-4 cursor-pointer hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-800 flex items-center justify-center flex-shrink-0">
                            <ClipboardDocumentListIcon class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ dataShareStore.pendingExpensesCount }} solicitud{{ dataShareStore.pendingExpensesCount > 1 ? 'es' : '' }} pendiente{{ dataShareStore.pendingExpensesCount > 1 ? 's' : '' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Tienes gastos esperando tu aprobacion
                            </p>
                        </div>
                        <ChevronRightIcon class="w-5 h-5 text-gray-400" />
                    </div>
                </div>
            </div>

            <!-- Servicios pendientes de pago -->
            <div v-if="serviciosStore.serviciosPendientesCount > 0" class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                    Servicios pendientes
                </h2>
                <div
                    @click="irAServicios"
                    class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 cursor-pointer hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-800 flex items-center justify-center flex-shrink-0">
                            <ExclamationTriangleIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ serviciosStore.serviciosPendientesCount }} servicio{{ serviciosStore.serviciosPendientesCount > 1 ? 's' : '' }} por pagar
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Tienes recibos pendientes de pago
                            </p>
                        </div>
                        <ChevronRightIcon class="w-5 h-5 text-gray-400" />
                    </div>
                </div>
            </div>

            <!-- Conflictos de sincronizacion -->
            <div v-if="offlineStore.hasConflicts" class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                    Conflictos de sincronizacion
                </h2>
                <div
                    v-for="conflict in offlineStore.conflicts"
                    :key="conflict.id"
                    class="bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-800 rounded-lg p-4"
                >
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-800 flex items-center justify-center flex-shrink-0">
                            <ExclamationTriangleIcon class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white">
                                Conflicto en {{ conflict.operation.type === 'gasto' ? 'Gasto' : 'Abono' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                {{ conflict.message }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ formatearFecha(conflict.timestamp) }}
                            </p>
                        </div>
                    </div>

                    <!-- Comparacion de versiones -->
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <!-- Version Local (dispositivo) -->
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                            <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase mb-2">
                                Tu version (dispositivo)
                            </p>
                            <div class="space-y-1 text-sm">
                                <p class="text-gray-900 dark:text-white font-medium truncate">
                                    {{ conflict.operation.data?.concepto || 'Sin concepto' }}
                                </p>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ formatCurrency(conflict.operation.data?.valor || 0) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ conflict.operation.data?.fecha || 'Sin fecha' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                    {{ conflict.operation.data?.tipo || '' }}
                                </p>
                            </div>
                            <button
                                @click="resolverConflicto(conflict.id, 'local')"
                                :disabled="resolvingConflict === conflict.id"
                                class="w-full mt-3 px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium"
                            >
                                Usar esta
                            </button>
                        </div>

                        <!-- Version Servidor -->
                        <div class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-2">
                                Version servidor
                            </p>
                            <div v-if="conflict.serverData" class="space-y-1 text-sm">
                                <p class="text-gray-900 dark:text-white font-medium truncate">
                                    {{ conflict.serverData.concepto || 'Sin concepto' }}
                                </p>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ formatCurrency(conflict.serverData.valor || 0) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ conflict.serverData.fecha || 'Sin fecha' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                    {{ conflict.serverData.tipo || '' }}
                                </p>
                            </div>
                            <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                                El registro fue eliminado o no existe
                            </div>
                            <button
                                @click="resolverConflicto(conflict.id, 'server')"
                                :disabled="resolvingConflict === conflict.id"
                                class="w-full mt-3 px-3 py-2 bg-gray-500 text-white text-sm rounded-lg hover:bg-gray-600 disabled:opacity-50 font-medium"
                            >
                                Usar esta
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operaciones fallidas -->
            <div v-if="offlineStore.hasFailedOperations" class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                    Operaciones pendientes
                </h2>
                <div
                    v-for="failed in offlineStore.failedOperations"
                    :key="failed.id"
                    class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4"
                >
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-800 flex items-center justify-center flex-shrink-0">
                            <XCircleIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ failed.operation.type === 'gasto' ? 'Gasto' : 'Abono' }} no sincronizado
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                {{ failed.operation.data?.concepto || 'Sin concepto' }} - {{ formatCurrency(failed.operation.data?.valor || 0) }}
                            </p>
                            <p class="text-xs text-red-500 dark:text-red-400 mt-1">
                                Error: {{ failed.error }}
                            </p>
                            <div class="flex gap-2 mt-3">
                                <button
                                    @click="reintentarOperacion(failed.id)"
                                    :disabled="retryingOperation === failed.id"
                                    class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Reintentar
                                </button>
                                <button
                                    @click="descartarOperacion(failed.id)"
                                    :disabled="retryingOperation === failed.id"
                                    class="px-3 py-1.5 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-sm rounded-lg hover:bg-red-200 dark:hover:bg-red-800 disabled:opacity-50"
                                >
                                    Descartar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Otras notificaciones -->
            <div v-if="notificationsStore.notifications.length > 0" class="space-y-3">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                    Historial
                </h2>
                <div
                    v-for="notification in notificationsStore.notifications"
                    :key="notification.id"
                    class="relative overflow-hidden rounded-lg"
                >
                    <!-- Fondo rojo de eliminación -->
                    <div class="absolute inset-0 bg-red-500 flex items-center justify-end px-4">
                        <TrashIcon class="w-6 h-6 text-white" />
                    </div>
                    <!-- Contenido deslizable -->
                    <div
                        :class="[
                            'relative p-4 transition-transform duration-200',
                            notification.read
                                ? 'bg-gray-50 dark:bg-gray-800'
                                : 'bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm'
                        ]"
                        :style="{ transform: `translateX(${getSwipeOffset(notification.id)}px)` }"
                        @touchstart="handleTouchStart($event, notification.id)"
                        @touchmove="handleTouchMove($event, notification.id)"
                        @touchend="handleTouchEnd($event, notification.id)"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
                                    getNotificationStyle(notification.type).bg
                                ]"
                            >
                                <component
                                    :is="getNotificationStyle(notification.type).icon"
                                    :class="['w-5 h-5', getNotificationStyle(notification.type).text]"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p :class="['font-medium', notification.read ? 'text-gray-600 dark:text-gray-400' : 'text-gray-900 dark:text-white']">
                                        {{ notification.title }}
                                    </p>
                                    <span v-if="!notification.read" class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0 mt-2"></span>
                                </div>
                                <p :class="['text-sm mt-1', notification.read ? 'text-gray-500 dark:text-gray-500' : 'text-gray-600 dark:text-gray-300']">
                                    {{ notification.message }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                    {{ formatearFecha(notification.created_at) }}
                                </p>
                            </div>
                        </div>
                        <button
                            v-if="!notification.read"
                            @click="marcarLeida(notification.id)"
                            class="mt-3 text-sm text-primary dark:text-indigo-400 hover:underline"
                        >
                            Marcar como leida
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
    BellIcon,
    UserPlusIcon,
    ClipboardDocumentListIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    ChevronRightIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';
import { useShareNotificationsStore } from '../Stores/shareNotifications';
import { useDataShareStore } from '../Stores/dataShare';
import { useServiciosStore } from '../Stores/servicios';
import { useOfflineStore } from '../Stores/offline';
import { useCurrency } from '../Composables/useCurrency';

const router = useRouter();
const notificationsStore = useShareNotificationsStore();
const dataShareStore = useDataShareStore();
const serviciosStore = useServiciosStore();
const offlineStore = useOfflineStore();
const { formatCurrency } = useCurrency();

const loading = ref(true);
const processingId = ref(null);
const resolvingConflict = ref(null);
const retryingOperation = ref(null);

// Estado para swipe
const swipeState = ref({});
const SWIPE_THRESHOLD = 80;

const tieneNotificaciones = computed(() => {
    return dataShareStore.pendingInvitations.length > 0 ||
           dataShareStore.pendingExpensesCount > 0 ||
           serviciosStore.serviciosPendientesCount > 0 ||
           offlineStore.hasConflicts ||
           offlineStore.hasFailedOperations ||
           notificationsStore.notifications.length > 0;
});

const getNotificationStyle = (type) => {
    const styles = {
        'expense_request': {
            bg: 'bg-amber-100 dark:bg-amber-900',
            text: 'text-amber-600 dark:text-amber-400',
            icon: ClipboardDocumentListIcon
        },
        'expense_approved': {
            bg: 'bg-green-100 dark:bg-green-900',
            text: 'text-green-600 dark:text-green-400',
            icon: CheckCircleIcon
        },
        'expense_rejected': {
            bg: 'bg-red-100 dark:bg-red-900',
            text: 'text-red-600 dark:text-red-400',
            icon: XCircleIcon
        },
        'share_invitation': {
            bg: 'bg-indigo-100 dark:bg-indigo-900',
            text: 'text-indigo-600 dark:text-indigo-400',
            icon: UserPlusIcon
        },
        'share_revoked': {
            bg: 'bg-gray-100 dark:bg-gray-700',
            text: 'text-gray-600 dark:text-gray-400',
            icon: XCircleIcon
        }
    };
    return styles[type] || styles['share_invitation'];
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Ahora';
    if (diffMins < 60) return `Hace ${diffMins} min`;
    if (diffHours < 24) return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
    if (diffDays < 7) return `Hace ${diffDays} dia${diffDays > 1 ? 's' : ''}`;

    return date.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
};

const aceptarInvitacion = async (shareId) => {
    processingId.value = shareId;
    const result = await dataShareStore.acceptInvitation(shareId);
    processingId.value = null;

    if (result.success) {
        await notificationsStore.fetchUnreadCount();
    }
};

const rechazarInvitacion = async (shareId) => {
    processingId.value = shareId;
    const result = await dataShareStore.rejectInvitation(shareId);
    processingId.value = null;

    if (result.success) {
        await notificationsStore.fetchUnreadCount();
    }
};

const marcarLeida = async (notificationId) => {
    await notificationsStore.markAsRead(notificationId);
};

const marcarTodasLeidas = async () => {
    await notificationsStore.markAllAsRead();
};

const irASolicitudes = () => {
    router.push('/configuracion');
};

const irAServicios = () => {
    router.push('/gastos/nuevo?seccion=servicios');
};

// Funciones de conflictos y operaciones offline
const resolverConflicto = async (conflictId, choice) => {
    resolvingConflict.value = conflictId;
    try {
        await offlineStore.resolveConflict(conflictId, choice);
    } finally {
        resolvingConflict.value = null;
    }
};

const reintentarOperacion = async (failedId) => {
    retryingOperation.value = failedId;
    try {
        await offlineStore.retryFailedOperation(failedId);
    } finally {
        retryingOperation.value = null;
    }
};

const descartarOperacion = (failedId) => {
    if (confirm('¿Seguro que quieres descartar esta operacion? Los datos se perderan.')) {
        offlineStore.discardFailedOperation(failedId);
    }
};

// Funciones de swipe
const getSwipeOffset = (notificationId) => {
    return swipeState.value[notificationId]?.offset || 0;
};

const handleTouchStart = (event, notificationId) => {
    const touch = event.touches[0];
    swipeState.value[notificationId] = {
        startX: touch.clientX,
        startY: touch.clientY,
        offset: swipeState.value[notificationId]?.offset || 0,
        swiping: false
    };
};

const handleTouchMove = (event, notificationId) => {
    const state = swipeState.value[notificationId];
    if (!state) return;

    const touch = event.touches[0];
    const deltaX = touch.clientX - state.startX;
    const deltaY = touch.clientY - state.startY;

    // Solo procesar si el movimiento horizontal es mayor que el vertical
    if (!state.swiping && Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 10) {
        state.swiping = true;
    }

    if (state.swiping) {
        event.preventDefault();
        // Solo permitir deslizar hacia la izquierda (valores negativos)
        const newOffset = Math.min(0, deltaX);
        state.offset = Math.max(newOffset, -120);
    }
};

const handleTouchEnd = async (event, notificationId) => {
    const state = swipeState.value[notificationId];
    if (!state) return;

    if (state.offset < -SWIPE_THRESHOLD) {
        // Eliminar notificación
        await eliminarNotificacion(notificationId);
    } else {
        // Restaurar posición
        state.offset = 0;
    }

    state.swiping = false;
};

const eliminarNotificacion = async (notificationId) => {
    // Animación de salida
    swipeState.value[notificationId].offset = -500;

    // Esperar animación
    await new Promise(resolve => setTimeout(resolve, 200));

    // Eliminar del store
    await notificationsStore.deleteNotification(notificationId);

    // Limpiar estado
    delete swipeState.value[notificationId];
};

onMounted(async () => {
    loading.value = true;
    await Promise.all([
        notificationsStore.fetchNotifications(),
        dataShareStore.fetchSharedWithMe(),
        dataShareStore.fetchPendingExpenses(),
        serviciosStore.cargarAlertas()
    ]);
    loading.value = false;
});
</script>
