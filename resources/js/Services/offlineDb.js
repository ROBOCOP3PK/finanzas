/**
 * Servicio de base de datos offline usando IndexedDB
 * Maneja la cola de sincronización para operaciones pendientes
 */

const DB_NAME = 'finanzas-offline';
const DB_VERSION = 1;
const STORE_NAME = 'pending-operations';

let db = null;

/**
 * Abre la conexión a IndexedDB
 */
const openDB = () => {
    return new Promise((resolve, reject) => {
        if (db) {
            resolve(db);
            return;
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => {
            console.error('Error abriendo IndexedDB:', request.error);
            reject(request.error);
        };

        request.onsuccess = () => {
            db = request.result;
            resolve(db);
        };

        request.onupgradeneeded = (event) => {
            const database = event.target.result;

            // Crear store para operaciones pendientes
            if (!database.objectStoreNames.contains(STORE_NAME)) {
                const store = database.createObjectStore(STORE_NAME, {
                    keyPath: 'id',
                    autoIncrement: true
                });
                store.createIndex('timestamp', 'timestamp', { unique: false });
                store.createIndex('type', 'type', { unique: false });
            }
        };
    });
};

/**
 * Agrega una operación pendiente a la cola
 * @param {Object} operation - La operación a guardar
 * @param {string} operation.type - Tipo: 'gasto', 'abono', etc.
 * @param {string} operation.method - Método HTTP: 'POST', 'PUT', 'DELETE'
 * @param {string} operation.url - URL del endpoint
 * @param {Object} operation.data - Datos de la operación
 * @param {string} operation.tempId - ID temporal para identificar en UI
 */
const addPendingOperation = async (operation) => {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);

        const record = {
            ...operation,
            timestamp: Date.now(),
            retries: 0
        };

        const request = store.add(record);

        request.onsuccess = () => {
            resolve({ ...record, id: request.result });
        };

        request.onerror = () => {
            console.error('Error guardando operación:', request.error);
            reject(request.error);
        };
    });
};

/**
 * Obtiene todas las operaciones pendientes
 */
const getPendingOperations = async () => {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readonly');
        const store = transaction.objectStore(STORE_NAME);
        const index = store.index('timestamp');
        const request = index.getAll();

        request.onsuccess = () => {
            resolve(request.result || []);
        };

        request.onerror = () => {
            console.error('Error obteniendo operaciones:', request.error);
            reject(request.error);
        };
    });
};

/**
 * Obtiene el conteo de operaciones pendientes
 */
const getPendingCount = async () => {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readonly');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.count();

        request.onsuccess = () => {
            resolve(request.result);
        };

        request.onerror = () => {
            reject(request.error);
        };
    });
};

/**
 * Elimina una operación de la cola (después de sincronizar)
 * @param {number} id - ID de la operación
 */
const removePendingOperation = async (id) => {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.delete(id);

        request.onsuccess = () => {
            resolve(true);
        };

        request.onerror = () => {
            console.error('Error eliminando operación:', request.error);
            reject(request.error);
        };
    });
};

/**
 * Actualiza una operación (por ejemplo, incrementar reintentos)
 * @param {number} id - ID de la operación
 * @param {Object} updates - Campos a actualizar
 */
const updatePendingOperation = async (id, updates) => {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const getRequest = store.get(id);

        getRequest.onsuccess = () => {
            const record = getRequest.result;
            if (record) {
                const updated = { ...record, ...updates };
                const putRequest = store.put(updated);
                putRequest.onsuccess = () => resolve(updated);
                putRequest.onerror = () => reject(putRequest.error);
            } else {
                reject(new Error('Operación no encontrada'));
            }
        };

        getRequest.onerror = () => {
            reject(getRequest.error);
        };
    });
};

/**
 * Limpia todas las operaciones pendientes
 */
const clearAllPending = async () => {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const transaction = database.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.clear();

        request.onsuccess = () => {
            resolve(true);
        };

        request.onerror = () => {
            reject(request.error);
        };
    });
};

export default {
    openDB,
    addPendingOperation,
    getPendingOperations,
    getPendingCount,
    removePendingOperation,
    updatePendingOperation,
    clearAllPending
};
