/**
 * Utility functions for currency and numeric formatting in Philippine Peso (PHP).
 */

export function formatPeso(amount: number | string | null | undefined): string {
    if (amount === null || amount === undefined || amount === '') {
        return '₱0.00';
    }

    const num = typeof amount === 'string' ? parseFloat(amount) : amount;

    if (isNaN(num)) {
        return '₱0.00';
    }

    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
}

export function formatNumber(count: number | string | null | undefined): string {
    if (count === null || count === undefined || count === '') {
        return '0';
    }

    const num = typeof count === 'string' ? parseInt(count, 10) : count;

    if (isNaN(num)) {
        return '0';
    }

    return new Intl.NumberFormat('en-PH').format(num);
}

export function formatHour(hour: number): string {
    if (hour === 0) return '12:00 AM';
    if (hour < 12) return `${hour}:00 AM`;
    if (hour === 12) return '12:00 PM';
    return `${hour - 12}:00 PM`;
}

export function formatShortHour(hour: number): string {
    if (hour === 0) return '12 AM';
    if (hour < 12) return `${hour} AM`;
    if (hour === 12) return '12 PM';
    return `${hour - 12} PM`;
}
