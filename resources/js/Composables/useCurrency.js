import { computed } from 'vue';
import { useConfigStore } from '../Stores/config';

export function useCurrency() {
    const configStore = useConfigStore();

    const divisaInfo = computed(() => {
        const divisas = {
            COP: { codigo: 'COP', simbolo: '$', locale: 'es-CO', nombre: 'Peso Colombiano' },
            USD: { codigo: 'USD', simbolo: '$', locale: 'en-US', nombre: 'Dolar Estadounidense' },
            EUR: { codigo: 'EUR', simbolo: '€', locale: 'de-DE', nombre: 'Euro' },
            MXN: { codigo: 'MXN', simbolo: '$', locale: 'es-MX', nombre: 'Peso Mexicano' }
        };
        return divisas[configStore.divisa] || divisas.COP;
    });

    const formatCurrency = (value) => {
        if (value === null || value === undefined) return '';

        const num = Number(value);
        if (isNaN(num)) return value;

        const formato = configStore.formatoDivisa;
        const divisa = divisaInfo.value;

        // Redondear el numero para mostrar (sin decimales)
        const rounded = Math.round(num);
        let formatted;
        if (formato === 'punto') {
            // Formato con punto como separador de miles (ej: 1.235)
            formatted = rounded.toLocaleString('de-DE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        } else {
            // Formato con coma como separador de miles (ej: 1,235)
            formatted = rounded.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        return `${divisa.simbolo} ${formatted}`;
    };

    const formatCurrencyCompact = (value) => {
        if (value === null || value === undefined) return '';

        const num = Number(value);
        if (isNaN(num)) return value;

        const divisa = divisaInfo.value;

        // Redondear antes de formatear
        const rounded = Math.round(num);
        const formatted = new Intl.NumberFormat(divisa.locale, {
            notation: 'compact',
            compactDisplay: 'short',
            maximumFractionDigits: 0
        }).format(rounded);

        return `${divisa.simbolo} ${formatted}`;
    };

    // Para inputs - formatea mientras el usuario escribe
    const formatInputValue = (value) => {
        if (!value && value !== 0) return '';

        const formato = configStore.formatoDivisa || 'coma';

        // Si es un número puro (desde el backend o selección de servicio)
        if (typeof value === 'number') {
            const entero = Math.round(value);
            if (formato === 'punto') {
                return entero.toLocaleString('de-DE');
            } else {
                return entero.toLocaleString('en-US');
            }
        }

        // Para strings (input del usuario), solo extraer dígitos
        const str = String(value).replace(/[^0-9]/g, '');

        if (!str) return '';

        const entero = parseInt(str, 10);
        if (isNaN(entero)) return '';

        // Formatear con separadores de miles
        if (formato === 'punto') {
            return entero.toLocaleString('de-DE');
        } else {
            return entero.toLocaleString('en-US');
        }
    };

    // Parsea un valor formateado a numero (soporta decimales)
    const parseFormattedValue = (value) => {
        if (!value && value !== 0) return 0;

        const formato = configStore.formatoDivisa || 'coma';
        let str = String(value);

        // Remover todo excepto números, puntos y comas
        str = str.replace(/[^0-9.,]/g, '');

        if (!str) return 0;

        if (formato === 'punto') {
            // Formato punto: 1.234,56 -> 1234.56
            str = str.replace(/\./g, '');  // Quitar separadores de miles
            str = str.replace(',', '.');    // Convertir coma decimal a punto
        } else {
            // Formato coma: 1,234.56 -> 1234.56
            str = str.replace(/,/g, '');   // Quitar separadores de miles
        }

        const num = parseFloat(str);
        return isNaN(num) ? 0 : Math.round(num * 100) / 100; // Redondear a 2 decimales
    };

    return {
        formatCurrency,
        formatCurrencyCompact,
        formatInputValue,
        parseFormattedValue,
        divisaInfo,
        divisa: computed(() => configStore.divisa),
        formatoDivisa: computed(() => configStore.formatoDivisa)
    };
}
