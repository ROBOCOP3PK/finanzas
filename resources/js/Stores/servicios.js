import { defineStore } from 'pinia';
import api from '../axios';

export const useServiciosStore = defineStore('servicios', {
    state: () => ({
        servicios: [],
        alertas: null,
        pendientesDetalle: [],
        loading: false,
        error: null
    }),

    getters: {
        activos: (state) => state.servicios.filter(s => s.activo),
        pagados: (state) => state.servicios.filter(s => s.pagado),
        pendientes: (state) => state.servicios.filter(s => s.activo && !s.pagado),
        porId: (state) => (id) => state.servicios.find(s => s.id === id),
        tieneAlertas: (state) => state.alertas?.mostrar_alerta ?? false,
        serviciosPendientesCount: (state) => state.alertas?.servicios_pendientes ?? 0
    },

    actions: {
        async cargarServicios(mes = null, anio = null) {
            this.loading = true;
            this.error = null;
            try {
                const params = {};
                if (mes) params.mes = mes;
                if (anio) params.anio = anio;
                const response = await api.get('/servicios', { params });
                this.servicios = response.data.data;
            } catch (error) {
                this.error = 'Error cargando servicios';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        async cargarAlertas() {
            try {
                const response = await api.get('/servicios/alertas');
                this.alertas = response.data.data;
            } catch (error) {
                console.error('Error cargando alertas:', error);
            }
        },

        async cargarPendientesDetalle() {
            try {
                const response = await api.get('/servicios/pendientes');
                this.pendientesDetalle = response.data.data;
            } catch (error) {
                console.error('Error cargando pendientes:', error);
            }
        },

        limpiarAlertas() {
            this.alertas = null;
            this.pendientesDetalle = [];
        },

        async crearServicio(data) {
            const response = await api.post('/servicios', data);
            this.servicios.push(response.data.data);
            return response.data.data;
        },

        async actualizarServicio(id, data) {
            const response = await api.put(`/servicios/${id}`, data);
            const index = this.servicios.findIndex(s => s.id === id);
            if (index !== -1) {
                this.servicios[index] = response.data.data;
            }
            return response.data.data;
        },

        async eliminarServicio(id) {
            await api.delete(`/servicios/${id}`);
            this.servicios = this.servicios.filter(s => s.id !== id);
        },

        async marcarPagado(servicioId, datos = {}) {
            const response = await api.post(`/servicios/${servicioId}/pagar`, {
                fecha: datos.fecha || new Date().toISOString().split('T')[0],
                valor: datos.valor,
                medio_pago_id: datos.medio_pago_id,
                tipo: datos.tipo || 'personal',
                crear_gasto: datos.crear_gasto !== false
            });

            // Actualizar estado local
            const index = this.servicios.findIndex(s => s.id === servicioId);
            if (index !== -1) {
                this.servicios[index].pagado = true;
                this.servicios[index].pago_mes = response.data.data.pago;
            }

            await this.cargarAlertas();
            return response.data.data;
        },

        async desmarcarPagado(servicioId) {
            await api.delete(`/servicios/${servicioId}/pagar`);

            // Actualizar estado local
            const index = this.servicios.findIndex(s => s.id === servicioId);
            if (index !== -1) {
                this.servicios[index].pagado = false;
                this.servicios[index].pago_mes = null;
            }

            await this.cargarAlertas();
        },

        async reordenar(orden) {
            await api.put('/servicios/reordenar', { orden });
            await this.cargarServicios();
        }
    }
});
