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
        ultimosMovimientos: [],
        pendientesRecurrentes: 0,
        loading: false,
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
                this.ultimosMovimientos = data.ultimos_movimientos;
                this.pendientesRecurrentes = data.pendientes_recurrentes;
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
        }
    }
});
