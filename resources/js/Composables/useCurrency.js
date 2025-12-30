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

    // Para inputs - formatea mientras el usuario escribe (soporta decimales)
    const formatInputValue = (value) => {
        if (!value && value !== 0) return '';

        const formato = configStore.formatoDivisa;
        const separadorDecimal = formato === 'punto' ? ',' : '.';
        const separadorMiles = formato === 'punto' ? '.' : ',';

        // Convertir a string y normalizar el separador decimal
        let str = String(value);

        // Si el valor viene como número o string numérico con punto decimal, convertir al formato correcto
        if (typeof value === 'number' || (typeof value === 'string' && /^\d+\.?\d*$/.test(value))) {
            const numValue = typeof value === 'number' ? value : parseFloat(value);
            const partes = numValue.toFixed(2).split('.');
            const entero = parseInt(partes[0], 10);
            const decimal = partes[1];

            let enteroFormateado;
            if (formato === 'punto') {
                enteroFormateado = entero.toLocaleString('de-DE');
            } else {
                enteroFormateado = entero.toLocaleString('en-US');
            }

            return decimal && decimal !== '00'
                ? `${enteroFormateado}${separadorDecimal}${decimal}`
                : enteroFormateado;
        }

        // Para strings, permitir dígitos y el separador decimal correcto
        const regexPermitidos = formato === 'punto'
            ? /[^0-9,]/g  // Punto: solo números y coma como decimal
            : /[^0-9.]/g; // Coma: solo números y punto como decimal

        str = str.replace(regexPermitidos, '');

        // Asegurar solo un separador decimal
        const partes = str.split(separadorDecimal);
        if (partes.length > 2) {
            str = partes[0] + separadorDecimal + partes.slice(1).join('');
        }

        // Limitar a 2 decimales
        if (partes.length === 2 && partes[1].length > 2) {
            str = partes[0] + separadorDecimal + partes[1].substring(0, 2);
        }

        // Formatear la parte entera con separadores de miles
        const [parteEntera, parteDecimal] = str.split(separadorDecimal);
        if (!parteEntera) return str;

        const entero = parseInt(parteEntera.replace(/\D/g, ''), 10);
        if (isNaN(entero)) return '';

        let enteroFormateado;
        if (formato === 'punto') {
            enteroFormateado = entero.toLocaleString('de-DE');
        } else {
            enteroFormateado = entero.toLocaleString('en-US');
        }

        // Si hay parte decimal o el usuario escribió el separador, incluirla
        if (parteDecimal !== undefined) {
            return `${enteroFormateado}${separadorDecimal}${parteDecimal}`;
        }

        // Verificar si el string original terminaba con el separador decimal
        if (str.endsWith(separadorDecimal)) {
            return `${enteroFormateado}${separadorDecimal}`;
        }

        return enteroFormateado;
    };

    // Parsea un valor formateado a numero (soporta decimales)
    const parseFormattedValue = (value) => {
        if (!value && value !== 0) return 0;

        const formato = configStore.formatoDivisa;
        let str = String(value);

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
