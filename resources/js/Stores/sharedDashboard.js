import { defineStore } from 'pinia';
import api from '../axios';

export const useSharedDashboardStore = defineStore('sharedDashboard', {
    state: () => ({
        // Info del propietario
        owner: null,
        shareId: null,

        // Datos del dashboard
        deudaPersona2: 0,
        gastoMesActual: 0,
        porcentajePersona1: 50,
        porcentajePersona2: 50,
        nombrePersona1: '',
        nombrePersona2: '',
        resumenMes: {
            mes: null,
            anio: null,
            total_gastos: 0,
            gastos_personal: 0,
            gastos_pareja: 0,
            gastos_compartido: 0,
            total_abonos: 0
        },
        porCategoria: [],
        ultimosMovimientos: [],

        // Historial de movimientos (gastos + abonos)
        movimientos: [],
        movimientosMeta: {
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0
        },
        movimientosFiltros: {
            desde: null,
            hasta: null,
            tipo: null,
            categoria_id: null
        },

        // Mantener gastos para compatibilidad con exportación
        gastos: [],
        gastosMeta: {
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0
        },
        gastosFiltros: {
            desde: null,
            hasta: null,
            tipo: null,
            categoria_id: null
        },

        // Datos para formularios
        categorias: [],
        mediosPago: [],

        loading: false,
        loadingMore: false,
        error: null
    }),

    getters: {
        hasData: (state) => state.owner !== null,
        ownerName: (state) => state.owner?.name || '',
        hayMas: (state) => state.movimientosMeta.current_page < state.movimientosMeta.last_page,

        // Tipos de gasto basados en la configuracion del propietario
        tiposGasto: (state) => {
            const tipos = [{ value: 'personal', label: state.nombrePersona1 || 'Persona 1' }];
            if (state.nombrePersona2 && state.nombrePersona2.trim() !== '') {
                tipos.push({ value: 'pareja', label: state.nombrePersona2 });
                const p1 = Math.round(state.porcentajePersona1);
                const p2 = Math.round(state.porcentajePersona2);
                tipos.push({ value: 'compartido', label: `${p1}/${p2}` });
            }
            return tipos;
        },

        // Obtener nombre del tipo
        getNombreTipo: (state) => (tipo) => {
            const p1 = Math.round(state.porcentajePersona1);
            const p2 = Math.round(state.porcentajePersona2);
            const tipos = {
                'personal': state.nombrePersona1 || 'Persona 1',
                'pareja': state.nombrePersona2 || 'Persona 2',
                'compartido': `${p1}/${p2}`
            };
            return tipos[tipo] || tipo;
        }
    },

    actions: {
        setShareId(id) {
            this.shareId = id;
        },

        async cargarDashboard(shareId = null) {
            const id = shareId || this.shareId;
            if (!id) return { success: false, error: 'No share ID' };

            this.loading = true;
            this.error = null;

            try {
                const response = await api.get(`/shared-with-me/${id}/dashboard`);
                const data = response.data.data;

                this.owner = data.owner;
                this.deudaPersona2 = data.deuda_persona_2;
                this.gastoMesActual = data.gasto_mes_actual;
                this.porcentajePersona1 = data.porcentaje_persona_1 || 50;
                this.porcentajePersona2 = data.porcentaje_persona_2 || 50;
                this.nombrePersona1 = data.nombre_persona_1 || data.owner?.name || 'Persona 1';
                this.nombrePersona2 = data.nombre_persona_2 || '';
                this.resumenMes = data.resumen_mes;
                this.porCategoria = data.por_categoria;
                this.ultimosMovimientos = data.ultimos_movimientos;
                this.shareId = id;

                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message || 'Error cargando dashboard';
                console.error('Error loading shared dashboard:', error);
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        async cargarGastos(shareId = null, page = 1) {
            const id = shareId || this.shareId;
            if (!id) return { success: false };

            this.loading = true;

            try {
                const params = { page, ...this.gastosFiltros };

                // Limpiar params vacios
                Object.keys(params).forEach(key => {
                    if (params[key] === null || params[key] === '') {
                        delete params[key];
                    }
                });

                const response = await api.get(`/shared-with-me/${id}/gastos`, { params });
                this.gastos = response.data.data;
                this.gastosMeta = response.data.meta;

                return { success: true };
            } catch (error) {
                console.error('Error loading shared gastos:', error);
                return { success: false };
            } finally {
                this.loading = false;
            }
        },

        async cargarHistorial(shareId = null, page = 1, append = false) {
            const id = shareId || this.shareId;
            if (!id) return { success: false };

            if (append) {
                this.loadingMore = true;
            } else {
                this.loading = true;
            }

            try {
                const params = { page, ...this.movimientosFiltros };

                // Limpiar params vacios
                Object.keys(params).forEach(key => {
                    if (params[key] === null || params[key] === '') {
                        delete params[key];
                    }
                });

                const response = await api.get(`/shared-with-me/${id}/historial`, { params });

                if (append) {
                    // Agregar a los existentes
                    this.movimientos = [...this.movimientos, ...response.data.data];
                } else {
                    // Reemplazar
                    this.movimientos = response.data.data;
                }
                this.movimientosMeta = response.data.meta;

                return { success: true };
            } catch (error) {
                console.error('Error loading shared historial:', error);
                return { success: false };
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },

        async cargarMasHistorial() {
            if (!this.hayMas || this.loadingMore) return;

            const nextPage = this.movimientosMeta.current_page + 1;
            await this.cargarHistorial(this.shareId, nextPage, true);
        },

        setFiltros(filtros) {
            this.gastosFiltros = { ...this.gastosFiltros, ...filtros };
            this.movimientosFiltros = { ...this.movimientosFiltros, ...filtros };
        },

        limpiarFiltros() {
            this.gastosFiltros = {
                desde: null,
                hasta: null,
                tipo: null,
                categoria_id: null
            };
            this.movimientosFiltros = {
                desde: null,
                hasta: null,
                tipo: null,
                categoria_id: null
            };
        },

        async cargarCategorias(shareId = null) {
            const id = shareId || this.shareId;
            if (!id) return { success: false };

            try {
                const response = await api.get(`/shared-with-me/${id}/categorias`);
                this.categorias = response.data.data;
                return { success: true };
            } catch (error) {
                console.error('Error loading categorias:', error);
                return { success: false };
            }
        },

        async cargarMediosPago(shareId = null) {
            const id = shareId || this.shareId;
            if (!id) return { success: false };

            try {
                const response = await api.get(`/shared-with-me/${id}/medios-pago`);
                this.mediosPago = response.data.data;
                return { success: true };
            } catch (error) {
                console.error('Error loading medios pago:', error);
                return { success: false };
            }
        },

        async cargarTodo(shareId) {
            this.shareId = shareId;
            await Promise.all([
                this.cargarDashboard(shareId),
                this.cargarCategorias(shareId),
                this.cargarMediosPago(shareId),
                this.cargarHistorial(shareId)
            ]);
        },

        limpiar() {
            this.owner = null;
            this.shareId = null;
            this.deudaPersona2 = 0;
            this.gastoMesActual = 0;
            this.resumenMes = {};
            this.porCategoria = [];
            this.ultimosMovimientos = [];
            this.gastos = [];
            this.movimientos = [];
            this.categorias = [];
            this.mediosPago = [];
            this.gastosFiltros = {
                desde: null,
                hasta: null,
                tipo: null,
                categoria_id: null
            };
            this.movimientosFiltros = {
                desde: null,
                hasta: null,
                tipo: null,
                categoria_id: null
            };
        }
    }
});
