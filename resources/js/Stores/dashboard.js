import { defineStore } from 'pinia';
import api from '../axios';

export const useDashboardStore = defineStore('dashboard', {
    state: () => ({
        saldoPendiente: 0,
        configuracion: {
            nombre_persona_1: 'Persona 1',
            nombre_persona_2: 'Persona 2',
            porcentaje_persona_1: 50,
            porcentaje_persona_2: 50
        },
        resumenMes: {
            mes: null,
            anio: null,
            total_gastos: 0,
            gastos_persona_1: 0,
            gastos_persona_2: 0,
            gastos_casa: 0,
            total_abonos: 0
        },
        porMedioPago: {},
        ultimosMovimientos: [],
        pendientesRecurrentes: 0,
        loading: false,
        error: null
    }),

    getters: {
        saldoFormateado: (state) => {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(state.saldoPendiente);
        }
    },

    actions: {
        async cargarDashboard() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/dashboard');
                const data = response.data.data;

                this.saldoPendiente = data.saldo_pendiente;
                this.configuracion = data.configuracion;
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
                this.saldoPendiente = response.data.data.saldo_pendiente;
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
