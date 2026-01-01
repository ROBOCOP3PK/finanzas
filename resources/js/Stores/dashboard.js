import { defineStore } from 'pinia';
import api from '../axios';

export const useDashboardStore = defineStore('dashboard', {
    state: () => ({
        deudaPersona2: 0,
        gastoMesActual: 0,
        porcentajePersona2: 40,
        personaSecundaria: null,
        resumenMes: {
            mes: null,
            anio: null,
            total_gastos: 0,
            gastos_personal: 0,
            gastos_pareja: 0,
            gastos_compartido: 0,
            total_abonos: 0
        },
        porMedioPago: {},
        porCategoria: [],
        porCategoriaMes: null,
        porCategoriaAnio: null,
        gastosPorCategoriaCache: {},
        ultimosMovimientos: [],
        pendientesRecurrentes: 0,
        loading: false,
        loadingCategorias: false,
        error: null
    }),

    getters: {},

    actions: {
        async cargarDashboard() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/dashboard');
                const data = response.data.data;

                this.deudaPersona2 = data.deuda_persona_2;
                this.gastoMesActual = data.gasto_mes_actual;
                this.porcentajePersona2 = data.porcentaje_persona_2;
                this.personaSecundaria = data.persona_secundaria;
                this.resumenMes = data.resumen_mes;
                this.porMedioPago = data.por_medio_pago;
                this.porCategoria = data.por_categoria || [];
                this.porCategoriaMes = data.resumen_mes?.mes || new Date().getMonth() + 1;
                this.porCategoriaAnio = data.resumen_mes?.anio || new Date().getFullYear();
                this.ultimosMovimientos = data.ultimos_movimientos;
                this.pendientesRecurrentes = data.pendientes_recurrentes;
                this.gastosPorCategoriaCache = {};
            } catch (error) {
                this.error = 'Error cargando dashboard';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async cargarSaldo() {
            try {
                const response = await api.get('/dashboard/saldo');
                this.deudaPersona2 = response.data.data.deuda_persona_2;
                this.gastoMesActual = response.data.data.gasto_mes_actual;
            } catch (error) {
                console.error('Error cargando saldo:', error);
            }
        },

        async cargarResumenMes(mes = null, anio = null) {
            try {
                const params = {};
                if (mes) params.mes = mes;
                if (anio) params.anio = anio;

                const response = await api.get('/dashboard/resumen-mes', { params });
                this.resumenMes = response.data.data;
            } catch (error) {
                console.error('Error cargando resumen:', error);
            }
        },

        async cargarPorCategoria(mes, anio) {
            this.loadingCategorias = true;
            try {
                const response = await api.get('/dashboard/por-categoria', {
                    params: { mes, anio }
                });
                const data = response.data.data;
                this.porCategoria = data.categorias || [];
                this.porCategoriaMes = data.mes;
                this.porCategoriaAnio = data.anio;
                this.gastosPorCategoriaCache = {};
            } catch (error) {
                console.error('Error cargando categorías:', error);
            } finally {
                this.loadingCategorias = false;
            }
        },

        async cargarGastosCategoria(categoriaId, mes, anio) {
            const cacheKey = `${categoriaId}-${mes}-${anio}`;
            if (this.gastosPorCategoriaCache[cacheKey]) {
                return this.gastosPorCategoriaCache[cacheKey];
            }

            try {
                const response = await api.get(`/dashboard/categoria/${categoriaId}/gastos`, {
                    params: { mes, anio }
                });
                this.gastosPorCategoriaCache[cacheKey] = response.data.data;
                return response.data.data;
            } catch (error) {
                console.error('Error cargando gastos de categoría:', error);
                return [];
            }
        },

        cambiarMesCategorias(direccion) {
            let mes = this.porCategoriaMes;
            let anio = this.porCategoriaAnio;

            if (direccion === 'anterior') {
                mes--;
                if (mes < 1) {
                    mes = 12;
                    anio--;
                }
            } else {
                mes++;
                if (mes > 12) {
                    mes = 1;
                    anio++;
                }
            }

            this.cargarPorCategoria(mes, anio);
        }
    }
});
