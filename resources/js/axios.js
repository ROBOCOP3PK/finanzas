import axios from 'axios';
import offlineDb from './Services/offlineDb';

const TOKEN_KEY = 'finanzas_auth_token';

// Flag para indicar si estamos online
let isOnline = navigator.onLine;
window.addEventListener('online', () => { isOnline = true; });
window.addEventListener('offline', () => { isOnline = false; });

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// Request interceptor - añadir token a cada petición
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem(TOKEN_KEY);
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    error => Promise.reject(error)
);

// Response interceptor - manejar errores y 401
api.interceptors.response.use(
    response => response,
    async error => {
        // Manejar 401 (no modificado)
        if (error.response?.status === 401) {
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem('finanzas_auth_user');
            window.location.href = '/login';
            return Promise.reject(error);
        }

        // Detectar error de red (sin response = sin conexión)
        const isNetworkError = !error.response && error.message === 'Network Error';
        const config = error.config;

        // Solo manejar offline si:
        // 1. Es un error de red
        // 2. La petición tiene el header 'X-Offline-Capable'
        // 3. Es una operación de escritura (POST, PUT, DELETE)
        if (isNetworkError && config?.headers?.['X-Offline-Capable']) {
            const method = config.method?.toUpperCase();

            if (['POST', 'PUT', 'DELETE'].includes(method)) {
                try {
                    // Guardar la operación para sincronizar después
                    const operation = {
                        type: config.headers['X-Offline-Type'] || 'unknown',
                        method: method,
                        url: config.url,
                        data: config.data ? JSON.parse(config.data) : null,
                        tempId: `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
                    };

                    await offlineDb.addPendingOperation(operation);

                    // Disparar evento para que el store de offline actualice
                    window.dispatchEvent(new CustomEvent('offline-operation-saved', {
                        detail: operation
                    }));

                    // Devolver una respuesta simulada para que la UI funcione
                    const fakeResponse = {
                        data: {
                            success: true,
                            data: {
                                ...operation.data,
                                id: operation.tempId,
                                _offline: true,
                                _pendingSync: true
                            },
                            message: 'Guardado localmente. Se sincronizara cuando vuelva la conexion.',
                            _offline: true
                        },
                        status: 200,
                        statusText: 'OK (Offline)',
                        config: config
                    };

                    return fakeResponse;

                } catch (offlineError) {
                    console.error('Error guardando operación offline:', offlineError);
                    // Si falla el guardado offline, propagar el error original
                    return Promise.reject(error);
                }
            }
        }

        return Promise.reject(error);
    }
);

/**
 * Helper para hacer peticiones con soporte offline
 * @param {string} type - Tipo de operación (gasto, abono, etc.)
 */
export const withOfflineSupport = (type) => ({
    'X-Offline-Capable': 'true',
    'X-Offline-Type': type
});

/**
 * Verifica si estamos online
 */
export const checkOnline = () => isOnline;

export default api;
