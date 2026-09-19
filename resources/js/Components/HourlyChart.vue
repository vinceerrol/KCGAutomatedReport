<script setup lang="ts">
import { computed } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    type ChartOptions,
    type ChartData
} from 'chart.js';
import { Line } from 'vue-chartjs';
import { formatHour, formatPeso } from '@/utils/currency';
import EmptyState from '@/Components/EmptyState.vue';
import { LineChart as ChartIcon } from 'lucide-vue-next';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

interface HourlyItem {
    hour: number;
    orders: number;
    units_sold: number;
    gross_sales: number;
    net_sales: number;
}

const props = defineProps<{
    data: HourlyItem[];
}>();

const chartData = computed<ChartData<'line'>>(() => {
    const labels = props.data.map((item) => formatHour(item.hour));
    const netSales = props.data.map((item) => Number(item.net_sales));
    const grossSales = props.data.map((item) => Number(item.gross_sales));

    return {
        labels,
        datasets: [
            {
                label: 'Net Sales (PHP)',
                data: netSales,
                borderColor: '#4f46e5', // Indigo-600
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2.5,
            },
            {
                label: 'Gross Sales (PHP)',
                data: grossSales,
                borderColor: '#94a3b8', // Slate-400
                backgroundColor: 'transparent',
                borderDash: [5, 5],
                tension: 0.35,
                pointBackgroundColor: '#94a3b8',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
                pointRadius: 3,
                borderWidth: 1.5,
            }
        ]
    };
});

const chartOptions = computed<ChartOptions<'line'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: {
            position: 'top',
            align: 'end',
            labels: {
                boxWidth: 8,
                boxHeight: 8,
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 16,
                font: {
                    family: "'Plus Jakarta Sans', 'Inter', sans-serif",
                    size: 12,
                    weight: 500,
                },
                color: '#64748b',
            },
        },
        tooltip: {
            backgroundColor: '#0f172a',
            borderColor: '#334155',
            borderWidth: 1,
            titleFont: { family: "'Plus Jakarta Sans', 'Inter', sans-serif", size: 13, weight: 600 },
            bodyFont: { family: "'Plus Jakarta Sans', 'Inter', sans-serif", size: 12 },
            padding: { top: 10, bottom: 10, left: 14, right: 14 },
            cornerRadius: 10,
            displayColors: true,
            boxWidth: 8,
            boxHeight: 8,
            usePointStyle: true,
            callbacks: {
                label(context) {
                    const label = context.dataset.label || '';
                    const value = context.parsed.y ?? 0;
                    return ` ${label}: ${formatPeso(value)}`;
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: { family: "'Plus Jakarta Sans', 'Inter', sans-serif", size: 11 },
                color: '#64748b',
            }
        },
        y: {
            grid: {
                color: '#f1f5f9',
            },
            ticks: {
                font: { family: "'Plus Jakarta Sans', 'Inter', sans-serif", size: 11 },
                color: '#64748b',
                callback(value) {
                    return `₱${Number(value).toLocaleString()}`;
                }
            }
        }
    }
}));
</script>

<template>
    <div class="h-72 w-full">
        <Line v-if="data && data.length > 0" :data="chartData" :options="chartOptions" />
        <EmptyState
            v-else
            :icon="ChartIcon"
            title="No hourly data recorded"
            description="Hourly performance will appear here once snapshots are recorded."
        />
    </div>
</template>
