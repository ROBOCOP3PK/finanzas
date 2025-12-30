import { defineStore } from 'pinia';
import api from '../axios';

export const useShareNotificationsStore = defineStore('shareNotifications', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        pendingExpensesCount: 0,
        totalCount: 0,
        loading: false
    }),

    getters: {
        hasUnread: (state) => state.unreadCount > 0,
        hasPendingExpenses: (state) => state.pendingExpensesCount > 0,
        hasNotifications: (state) => state.totalCount > 0,
        unreadNotifications: (state) => state.notifications.filter(n => !n.read),
        readNotifications: (state) => state.notifications.filter(n => n.read)
    },

    actions: {
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await api.get('/share-notifications');
                this.notifications = response.data.data;
                this.unreadCount = response.data.unread_count;
                return { success: true };
            } catch (error) {
                console.error('Error fetching notifications:', error);
                return { success: false };
            } finally {
                this.loading = false;
            }
        },

        async fetchUnreadCount() {
            try {
                const response = await api.get('/share-notifications/unread-count');
                const data = response.data.data;
                this.unreadCount = data.notifications;
                this.pendingExpensesCount = data.pending_expenses;
                this.totalCount = data.total;
                return { success: true };
            } catch (error) {
                console.error('Error fetching unread count:', error);
                return { success: false };
            }
        },

        async markAsRead(notificationId) {
            try {
                await api.post(`/share-notifications/${notificationId}/read`);
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification && !notification.read) {
                    notification.read = true;
                    notification.read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    this.totalCount = Math.max(0, this.totalCount - 1);
                }
                return { success: true };
            } catch (error) {
                console.error('Error marking as read:', error);
                return { success: false };
            }
        },

        async markAllAsRead() {
            try {
                await api.post('/share-notifications/read-all');
                this.notifications.forEach(n => {
                    n.read = true;
                    n.read_at = new Date().toISOString();
                });
                this.unreadCount = 0;
                return { success: true };
            } catch (error) {
                console.error('Error marking all as read:', error);
                return { success: false };
            }
        },

        // Decrementar contador cuando se aprueba/rechaza un gasto pendiente
        decrementPendingExpenses() {
            this.pendingExpensesCount = Math.max(0, this.pendingExpensesCount - 1);
            this.totalCount = this.unreadCount + this.pendingExpensesCount;
        },

        // Refrescar todos los contadores
        async refresh() {
            await this.fetchUnreadCount();
        }
    }
});
