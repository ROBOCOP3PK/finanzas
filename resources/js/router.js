import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('./Pages/Dashboard.vue')
    },
    {
        path: '/gastos',
        name: 'gastos',
        component: () => import('./Pages/Gastos/Index.vue')
    },
    {
        path: '/gastos/nuevo',
        name: 'gastos.create',
        component: () => import('./Pages/Gastos/Create.vue')
    },
    {
        path: '/gastos/:id/editar',
        name: 'gastos.edit',
        component: () => import('./Pages/Gastos/Edit.vue')
    },
    {
        path: '/abonos',
        name: 'abonos',
        component: () => import('./Pages/Abonos/Index.vue')
    },
    {
        path: '/abonos/nuevo',
        name: 'abonos.create',
        component: () => import('./Pages/Abonos/Create.vue')
    },
    {
        path: '/historial',
        name: 'historial',
        component: () => import('./Pages/Historial.vue')
    },
    {
        path: '/configuracion',
        name: 'configuracion',
        component: () => import('./Pages/Configuracion.vue')
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
