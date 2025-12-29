import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './Stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('./Pages/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('./Pages/Register.vue'),
        meta: { guest: true }
    },
    {
        path: '/',
        redirect: '/gastos/nuevo'
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('./Pages/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/gastos',
        name: 'gastos',
        component: () => import('./Pages/Gastos/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/gastos/nuevo',
        name: 'gastos.create',
        component: () => import('./Pages/Gastos/Create.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/gastos/:id/editar',
        name: 'gastos.edit',
        component: () => import('./Pages/Gastos/Edit.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/abonos',
        name: 'abonos',
        component: () => import('./Pages/Abonos/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/abonos/nuevo',
        name: 'abonos.create',
        component: () => import('./Pages/Abonos/Create.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/historial',
        name: 'historial',
        component: () => import('./Pages/Historial.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/configuracion',
        name: 'configuracion',
        component: () => import('./Pages/Configuracion.vue'),
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    // Ruta requiere autenticación
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
        return;
    }

    // Ruta solo para invitados (login) y ya está autenticado
    if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
        return;
    }

    next();
});

export default router;
