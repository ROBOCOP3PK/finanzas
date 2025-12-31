<template>
    <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50 safe-x">
        <div class="flex justify-around items-center h-16">
            <router-link
                v-for="item in visibleNavItems"
                :key="item.to"
                :to="item.to"
                class="flex flex-col items-center justify-center flex-1 h-full py-2 transition-colors touch-target relative"
                :class="[
                    isActive(item.to)
                        ? 'text-primary dark:text-indigo-400'
                        : 'text-gray-500 dark:text-gray-400 active:text-gray-700 dark:active:text-gray-300'
                ]"
            >
                <component :is="item.icon" class="w-6 h-6" />
                <span class="text-xs mt-1 font-medium">{{ item.label }}</span>
                <!-- Badge para notificaciones -->
                <span
                    v-if="item.badge && item.badge > 0"
                    class="absolute top-1 right-1/4 bg-red-500 text-white text-xs rounded-full min-w-4 h-4 flex items-center justify-center font-medium px-1"
                >
                    {{ item.badge > 9 ? '9+' : item.badge }}
                </span>
            </router-link>
        </div>
        <!-- Safe area spacer for home indicator -->
        <div class="safe-bottom bg-white dark:bg-gray-800"></div>
    </nav>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { HomeIcon, PlusCircleIcon, ClockIcon, Cog6ToothIcon, UsersIcon, BellIcon } from '@heroicons/vue/24/outline';
import { useDataShareStore } from '../../Stores/dataShare';
import { useShareNotificationsStore } from '../../Stores/shareNotifications';
import { useServiciosStore } from '../../Stores/servicios';

const route = useRoute();
const dataShareStore = useDataShareStore();
const notificationsStore = useShareNotificationsStore();
const serviciosStore = useServiciosStore();

onMounted(async () => {
    await Promise.all([
        dataShareStore.fetchSharedWithMe(),
        dataShareStore.fetchPendingExpenses(),
        notificationsStore.fetchUnreadCount(),
        serviciosStore.cargarAlertas()
    ]);
});

const hasSharedAccess = computed(() => dataShareStore.activeShares.length > 0);

// Contador total de notificaciones pendientes
const totalNotificaciones = computed(() => {
    let total = 0;
    // Notificaciones sin leer
    total += notificationsStore.unreadCount || 0;
    // Invitaciones pendientes
    total += dataShareStore.pendingInvitations?.length || 0;
    // Gastos pendientes de aprobar
    total += dataShareStore.pendingExpensesCount || 0;
    // Servicios pendientes
    total += serviciosStore.serviciosPendientesCount || 0;
    return total;
});

const navItems = computed(() => [
    { to: '/gastos/nuevo', label: 'Nuevo', icon: PlusCircleIcon },
    { to: '/dashboard', label: 'Resumen', icon: HomeIcon },
    { to: '/historial', label: 'Historial', icon: ClockIcon },
    ...(hasSharedAccess.value ? [{ to: '/shared-data', label: 'Compartido', icon: UsersIcon }] : []),
    { to: '/notificaciones', label: 'Alertas', icon: BellIcon, badge: totalNotificaciones.value },
    { to: '/configuracion', label: 'Config', icon: Cog6ToothIcon },
]);

const visibleNavItems = computed(() => navItems.value);

const isActive = (path) => {
    return route.path.startsWith(path);
};
</script>
