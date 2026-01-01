import { defineStore } from 'pinia';
import api from '../axios';

export const useDataShareStore = defineStore('dataShare', {
    state: () => ({
        // Como propietario
        myShare: null,
        hasActiveShare: false,

        // Como invitado
        activeShares: [],
        pendingInvitations: [],

        // Gastos pendientes (como propietario)
        pendingExpenses: [],
        pendingExpensesCount: 0,

        // Mis solicitudes (como invitado)
        myRequests: [],
        myRequestsMeta: {},

        loading: false,
        error: null
    }),

    getters: {
        isOwner: (state) => state.hasActiveShare,
        isGuest: (state) => state.activeShares.length > 0,
        hasPendingInvitations: (state) => state.pendingInvitations.length > 0,
        totalPendingCount: (state) => state.pendingExpensesCount,
        pendingExpensesToApprove: (state) => state.pendingExpenses
    },

    actions: {
        // ========================================
        // COMO PROPIETARIO
        // ========================================

        async fetchMyShareStatus() {
            try {
                const response = await api.get('/data-share/status');
                this.myShare = response.data.data.share;
                this.hasActiveShare = response.data.data.has_active_share;
                return { success: true };
            } catch (error) {
                console.error('Error fetching share status:', error);
                return { success: false };
            }
        },

        async inviteUser(email) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/data-share/invite', { email });
                this.myShare = response.data.data;
                this.hasActiveShare = true;
                return { success: true, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al enviar invitacion';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        async revokeAccess() {
            this.loading = true;
            try {
                await api.post('/data-share/revoke');
                this.myShare = null;
                this.hasActiveShare = false;
                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al revocar acceso';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        async fetchPendingExpenses() {
            try {
                const response = await api.get('/pending-expenses/pending');
                this.pendingExpenses = response.data.data;
                this.pendingExpensesCount = response.data.count;
                return { success: true };
            } catch (error) {
                console.error('Error fetching pending expenses:', error);
                return { success: false };
            }
        },

        // Alias para fetchPendingExpenses (usado en AppLayout)
        async fetchPendingExpensesToApprove() {
            return this.fetchPendingExpenses();
        },

        async fetchPendingExpense(id) {
            try {
                const response = await api.get(`/pending-expenses/${id}`);
                return { success: true, data: response.data.data };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Error al obtener solicitud' };
            }
        },

        async approveExpense(id, editedData = null) {
            try {
                const response = await api.post(`/pending-expenses/${id}/approve`, editedData || {});
                this.pendingExpenses = this.pendingExpenses.filter(e => e.id !== id);
                this.pendingExpensesCount = this.pendingExpenses.length;
                return { success: true, gasto: response.data.data, message: response.data.message };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Error al aprobar' };
            }
        },

        async rejectExpense(id, reason = null) {
            try {
                await api.post(`/pending-expenses/${id}/reject`, { reason });
                this.pendingExpenses = this.pendingExpenses.filter(e => e.id !== id);
                this.pendingExpensesCount = this.pendingExpenses.length;
                return { success: true };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Error al rechazar' };
            }
        },

        // ========================================
        // COMO INVITADO
        // ========================================

        async fetchSharedWithMe() {
            try {
                const response = await api.get('/shared-with-me');
                this.activeShares = response.data.data.active;
                this.pendingInvitations = response.data.data.pending;
                return { success: true };
            } catch (error) {
                console.error('Error fetching shared data:', error);
                return { success: false };
            }
        },

        async acceptInvitation(shareId) {
            this.loading = true;
            try {
                const response = await api.post(`/shared-with-me/${shareId}/accept`);
                await this.fetchSharedWithMe();
                return { success: true, data: response.data.data };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Error al aceptar' };
            } finally {
                this.loading = false;
            }
        },

        async rejectInvitation(shareId) {
            this.loading = true;
            try {
                await api.post(`/shared-with-me/${shareId}/reject`);
                await this.fetchSharedWithMe();
                return { success: true };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Error al rechazar' };
            } finally {
                this.loading = false;
            }
        },

        async createPendingExpense(shareId, data) {
            this.loading = true;
            try {
                const response = await api.post(`/pending-expenses/share/${shareId}`, data);
                return { success: true, data: response.data.data, message: response.data.message };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Error al crear solicitud' };
            } finally {
                this.loading = false;
            }
        },

        async fetchMyRequests(page = 1, status = null) {
            try {
                const params = { page };
                if (status) params.status = status;

                const response = await api.get('/pending-expenses/my-requests', { params });
                this.myRequests = response.data.data;
                this.myRequestsMeta = response.data.meta;
                return { success: true };
            } catch (error) {
                console.error('Error fetching my requests:', error);
                return { success: false };
            }
        },

        // ========================================
        // INICIALIZACION
        // ========================================

        async init() {
            await Promise.all([
                this.fetchMyShareStatus(),
                this.fetchSharedWithMe(),
                this.fetchPendingExpenses()
            ]);
        },

        clearError() {
            this.error = null;
        }
    }
});
