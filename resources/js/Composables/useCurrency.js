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

        // Formatear el numero
        let formatted;
        if (formato === 'punto') {
            // Formato con punto como separador de miles (ej: 1.234.567)
            formatted = num.toLocaleString('de-DE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        } else {
            // Formato con coma como separador de miles (ej: 1,234,567)
            formatted = num.toLocaleString('en-US', {
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

        const formatted = new Intl.NumberFormat(divisa.locale, {
            notation: 'compact',
            compactDisplay: 'short',
            maximumFractionDigits: 1
        }).format(num);

        return `${divisa.simbolo} ${formatted}`;
    };

    // Para inputs - formatea mientras el usuario escribe
    const formatInputValue = (value) => {
        if (!value) return '';

        // Remover todo excepto numeros
        const numerico = String(value).replace(/\D/g, '');
        if (!numerico) return '';

        const num = parseInt(numerico, 10);
        const formato = configStore.formatoDivisa;

        if (formato === 'punto') {
            return num.toLocaleString('de-DE');
        } else {
            return num.toLocaleString('en-US');
        }
    };

    // Parsea un valor formateado a numero
    const parseFormattedValue = (value) => {
        if (!value) return 0;
        // Remover todo excepto numeros
        const numerico = String(value).replace(/\D/g, '');
        return parseInt(numerico, 10) || 0;
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
