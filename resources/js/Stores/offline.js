import { defineStore } from 'pinia';
import offlineDb from '../Services/offlineDb';
import api from '../axios';

export const useOfflineStore = defineStore('offline', {
    state: () => ({
        isOnline: navigator.onLine,
        pendingCount: 0,
        isSyncing: false,
        lastSyncTime: null,
        conflicts: [], // Conflictos pendientes de resolver
        failedOperations: [] // Operaciones que fallaron (para reintentar)
    }),

    getters: {
        hasPendingOperations: (state) => state.pendingCount > 0,
        hasConflicts: (state) => state.conflicts.length > 0,
        hasFailedOperations: (state) => state.failedOperations.length > 0,
        totalPendingIssues: (state) => state.conflicts.length + state.failedOperations.length,
        statusText: (state) => {
            if (!state.isOnline) return 'Sin conexion';
            if (state.isSyncing) return 'Sincronizando...';
            if (state.pendingCount > 0) return `${state.pendingCount} pendiente${state.pendingCount > 1 ? 's' : ''}`;
            return 'Conectado';
        }
    },

    actions: {
        /**
         * Inicializa los listeners de conexión
         */
        init() {
            // Escuchar cambios de conexión
            window.addEventListener('online', this.handleOnline.bind(this));
            window.addEventListener('offline', this.handleOffline.bind(this));

            // Cargar datos iniciales
            this.loadPendingCount();
            this.loadConflicts();
            this.loadFailedOperations();

            // Intentar sincronizar si hay conexión
            if (this.isOnline) {
                this.syncPendingOperations();
            }
        },

        /**
         * Limpia los listeners
         */
        cleanup() {
            window.removeEventListener('online', this.handleOnline);
            window.removeEventListener('offline', this.handleOffline);
        },

        /**
         * Handler cuando vuelve la conexión
         */
        handleOnline() {
            this.isOnline = true;
            console.log('Conexion restaurada');
            this.syncPendingOperations();
        },

        /**
         * Handler cuando se pierde la conexión
         */
        handleOffline() {
            this.isOnline = false;
            console.log('Sin conexion');
        },

        /**
         * Carga el conteo de operaciones pendientes
         */
        async loadPendingCount() {
            try {
                this.pendingCount = await offlineDb.getPendingCount();
            } catch (error) {
                console.error('Error cargando conteo pendiente:', error);
            }
        },

        /**
         * Carga conflictos guardados en localStorage
         */
        loadConflicts() {
            try {
                const saved = localStorage.getItem('finanzas_conflicts');
                this.conflicts = saved ? JSON.parse(saved) : [];
            } catch (error) {
                this.conflicts = [];
            }
        },

        /**
         * Guarda conflictos en localStorage
         */
        saveConflicts() {
            localStorage.setItem('finanzas_conflicts', JSON.stringify(this.conflicts));
        },

        /**
         * Carga operaciones fallidas de localStorage
         */
        loadFailedOperations() {
            try {
                const saved = localStorage.getItem('finanzas_failed_ops');
                this.failedOperations = saved ? JSON.parse(saved) : [];
            } catch (error) {
                this.failedOperations = [];
            }
        },

        /**
         * Guarda operaciones fallidas en localStorage
         */
        saveFailedOperations() {
            localStorage.setItem('finanzas_failed_ops', JSON.stringify(this.failedOperations));
        },

        /**
         * Guarda una operación para sincronizar después
         */
        async saveForLater(operation) {
            try {
                const saved = await offlineDb.addPendingOperation(operation);
                this.pendingCount++;
                return saved;
            } catch (error) {
                console.error('Error guardando operación offline:', error);
                throw error;
            }
        },

        /**
         * Sincroniza todas las operaciones pendientes
         * NUNCA descarta operaciones - si falla, las guarda para reintentar
         */
        async syncPendingOperations() {
            if (this.isSyncing || !this.isOnline) return;

            this.isSyncing = true;

            try {
                const operations = await offlineDb.getPendingOperations();

                if (operations.length === 0) {
                    this.isSyncing = false;
                    return;
                }

                console.log(`Sincronizando ${operations.length} operaciones...`);

                for (const operation of operations) {
                    try {
                        const result = await this.executeOperation(operation);

                        // Verificar si hay conflicto
                        if (result.conflict) {
                            await this.handleConflict(operation, result);
                        }

                        // Operación exitosa - eliminar de la cola
                        await offlineDb.removePendingOperation(operation.id);
                        this.pendingCount = Math.max(0, this.pendingCount - 1);

                    } catch (error) {
                        console.error('Error sincronizando operación:', operation, error);

                        // Analizar el tipo de error
                        const errorInfo = this.analyzeError(error, operation);

                        if (errorInfo.isConflict) {
                            // Es un conflicto - mover a conflictos
                            await this.handleConflict(operation, errorInfo);
                            await offlineDb.removePendingOperation(operation.id);
                            this.pendingCount = Math.max(0, this.pendingCount - 1);
                        } else if (errorInfo.isRecoverable) {
                            // Error recuperable - mantener en cola para reintentar
                            await offlineDb.updatePendingOperation(operation.id, {
                                lastError: errorInfo.message,
                                lastAttempt: Date.now()
                            });
                        } else {
                            // Error no recuperable pero NUNCA perdemos datos
                            // Mover a operaciones fallidas para revisión manual
                            this.addFailedOperation(operation, errorInfo);
                            await offlineDb.removePendingOperation(operation.id);
                            this.pendingCount = Math.max(0, this.pendingCount - 1);
                        }
                    }
                }

                this.lastSyncTime = Date.now();

            } catch (error) {
                console.error('Error en sincronización:', error);
            } finally {
                this.isSyncing = false;
            }
        },

        /**
         * Analiza un error para determinar cómo manejarlo
         */
        analyzeError(error, operation) {
            const status = error.response?.status;
            const message = error.response?.data?.message || error.message || 'Error desconocido';

            // Conflictos (409 o registro ya modificado)
            if (status === 409 || message.includes('conflict') || message.includes('modified')) {
                return {
                    isConflict: true,
                    isRecoverable: false,
                    message,
                    serverData: error.response?.data?.data
                };
            }

            // Registro no encontrado (ya fue eliminado)
            if (status === 404) {
                if (operation.method === 'DELETE') {
                    // Intentamos eliminar algo que ya no existe - OK
                    return { isConflict: false, isRecoverable: false, alreadyDone: true, message };
                }
                // Intentamos editar algo que no existe - conflicto
                return {
                    isConflict: true,
                    isRecoverable: false,
                    message: 'El registro ya no existe en el servidor'
                };
            }

            // Errores de red - recuperables
            if (!error.response || status >= 500) {
                return { isConflict: false, isRecoverable: true, message };
            }

            // Errores de validación (400) - no recuperables pero guardar
            if (status === 400 || status === 422) {
                return { isConflict: false, isRecoverable: false, message };
            }

            // Por defecto - intentar recuperar
            return { isConflict: false, isRecoverable: true, message };
        },

        /**
         * Maneja un conflicto detectado
         */
        async handleConflict(operation, errorInfo) {
            const conflict = {
                id: `conflict_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                operation,
                serverData: errorInfo.serverData || null,
                message: errorInfo.message,
                timestamp: Date.now(),
                resolved: false
            };

            this.conflicts.push(conflict);
            this.saveConflicts();

            // Notificar al usuario
            window.dispatchEvent(new CustomEvent('offline-conflict-detected', {
                detail: conflict
            }));
        },

        /**
         * Agrega una operación fallida (para revisión manual)
         */
        addFailedOperation(operation, errorInfo) {
            const failed = {
                id: `failed_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                operation,
                error: errorInfo.message,
                timestamp: Date.now()
            };

            this.failedOperations.push(failed);
            this.saveFailedOperations();

            // Notificar al usuario
            window.dispatchEvent(new CustomEvent('offline-operation-failed', {
                detail: failed
            }));
        },

        /**
         * Ejecuta una operación guardada
         */
        async executeOperation(operation) {
            const { method, url, data } = operation;

            switch (method.toUpperCase()) {
                case 'POST':
                    return await api.post(url, data);
                case 'PUT':
                    return await api.put(url, data);
                case 'DELETE':
                    return await api.delete(url);
                default:
                    throw new Error(`Método no soportado: ${method}`);
            }
        },

        /**
         * Resuelve un conflicto eligiendo una versión
         * @param {string} conflictId - ID del conflicto
         * @param {string} choice - 'local' o 'server'
         */
        async resolveConflict(conflictId, choice) {
            const conflictIndex = this.conflicts.findIndex(c => c.id === conflictId);
            if (conflictIndex === -1) return;

            const conflict = this.conflicts[conflictIndex];

            try {
                if (choice === 'local') {
                    // Forzar los datos locales al servidor
                    const { method, url, data } = conflict.operation;

                    if (method === 'PUT') {
                        // Para ediciones, forzar actualización
                        await api.put(url, { ...data, _forceUpdate: true });
                    } else if (method === 'POST') {
                        // Para creaciones, reintentar
                        await api.post(url, data);
                    } else if (method === 'DELETE') {
                        // Para eliminaciones, intentar de nuevo
                        try {
                            await api.delete(url);
                        } catch (e) {
                            // Si ya no existe, está OK
                            if (e.response?.status !== 404) throw e;
                        }
                    }
                }
                // Si elige 'server', simplemente descartamos la operación local

                // Marcar como resuelto y remover
                this.conflicts.splice(conflictIndex, 1);
                this.saveConflicts();

                return { success: true };

            } catch (error) {
                console.error('Error resolviendo conflicto:', error);
                return { success: false, error: error.message };
            }
        },

        /**
         * Reintenta una operación fallida
         */
        async retryFailedOperation(failedId) {
            const failedIndex = this.failedOperations.findIndex(f => f.id === failedId);
            if (failedIndex === -1) return;

            const failed = this.failedOperations[failedIndex];

            try {
                await this.executeOperation(failed.operation);

                // Éxito - remover de fallidas
                this.failedOperations.splice(failedIndex, 1);
                this.saveFailedOperations();

                return { success: true };

            } catch (error) {
                // Actualizar el error
                failed.error = error.message || 'Error desconocido';
                failed.lastAttempt = Date.now();
                this.saveFailedOperations();

                return { success: false, error: error.message };
            }
        },

        /**
         * Descarta una operación fallida (solo si el usuario lo decide explícitamente)
         */
        discardFailedOperation(failedId) {
            const index = this.failedOperations.findIndex(f => f.id === failedId);
            if (index !== -1) {
                this.failedOperations.splice(index, 1);
                this.saveFailedOperations();
            }
        },

        /**
         * Genera un ID temporal único
         */
        generateTempId() {
            return `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        },

        /**
         * Verifica si un ID es temporal
         */
        isTempId(id) {
            return typeof id === 'string' && id.startsWith('temp_');
        },

        /**
         * Fuerza sincronización manual
         */
        async forceSync() {
            if (!this.isOnline) {
                throw new Error('No hay conexión a internet');
            }
            await this.syncPendingOperations();
        }
    }
});
