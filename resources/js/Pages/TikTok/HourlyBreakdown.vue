<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatPeso, formatNumber } from '@/utils/currency';
import {
    Calendar,
    Download,
    FileSpreadsheet,
    FileText,
    Copy,
    Check,
    RefreshCw,
    TrendingUp,
    Sparkles,
    Target,
    ShoppingBag,
    DollarSign,
    Zap,
    Play,
    Clock,
    Layers,
    Store,
    Info,
    ChevronRight,
    Send
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface ShopMetrics {
    ad_spend: Record<number, number | null>;
    orders: Record<number, number | null>;
    sales: Record<number, number | null>;
    roas: Record<number, number | null>;
}

interface ShopItem {
    shop_id: number;
    shop_code: string;
    shop_name: string;
    metrics: ShopMetrics;
}

interface ReportData {
    meta: {
        title: string;
        report_date: string;
        formatted_date: string;
        daily_target: number;
        latest_hour: string | null;
        source: string;
    };
    hours: Record<number, string>;
    kpis: {
        total_gmv: number;
        total_ad_spend: number;
        blended_roas: number;
        total_orders: number;
        daily_target: number;
        target_progress: number;
    };
    shops: ShopItem[];
    summary: {
        total_sales: Record<number, number | null>;
        sales_increment: Record<number, number | null>;
        vs_daily_target: Record<number, number | null>;
    };
}

const props = defineProps<{
    report: ReportData;
    logs?: Array<{ id: number; job: string; status: string; message: string; created_at: string }>;
    todayDate: string;
    serverTime: string;
}>();

const selectedDate = ref(props.report.meta.report_date);
const targetInput = ref(props.report.meta.daily_target);
const isSimulating = ref(false);
const copied = ref(false);
const showDispatchModal = ref(false);

const hoursList = computed(() => {
    return Object.entries(props.report.hours).map(([key, label]) => ({
        key: parseInt(key, 10),
        label
    }));
});

function applyDateFilter() {
    router.get('/tiktok', {
        date: selectedDate.value,
        target: targetInput.value
    }, { preserveState: true });
}

function handleDateChange(newDate: string) {
    selectedDate.value = newDate;
    applyDateFilter();
}

function applyTargetChange() {
    applyDateFilter();
    toast.success(`Daily target updated to ${formatPeso(targetInput.value)}`);
}

function downloadExcel() {
    const url = `/tiktok/export/excel?date=${selectedDate.value}&target=${targetInput.value}`;
    window.location.href = url;
    toast.success('Downloading Excel workbook (.xlsx)...');
}

function downloadCsv() {
    const url = `/tiktok/export/csv?date=${selectedDate.value}&target=${targetInput.value}`;
    window.location.href = url;
    toast.success('Downloading CSV file...');
}

function simulateMidnight() {
    if (isSimulating.value) return;
    isSimulating.value = true;
    const toastId = toast.loading('Simulating 12:00 AM final push...');

    router.post('/tiktok/simulate-midnight', {
        date: selectedDate.value
    }, {
        onSuccess: () => {
            toast.success('12:00 AM Midnight snapshot populated successfully!', { id: toastId });
        },
        onError: () => {
            toast.error('Simulation failed. Check logs.', { id: toastId });
        },
        onFinish: () => {
            isSimulating.value = false;
        }
    });
}

// Build formatted chat message matching the required TikTok hourly dispatch format
const dispatchMessage = computed(() => {
    const meta = props.report.meta;
    const kpis = props.report.kpis;
    const latestHour = meta.latest_hour || '11:00 PM';

    let msg = `🔥 *TIKTOK HOURLY GMV REPORT* 🔥\n`;
    msg += `📅 Date: ${meta.formatted_date}\n`;
    msg += `⏰ Snapshot as of: ${latestHour} (PHT)\n\n`;

    msg += `📊 *OVERALL TIKTOK PERFORMANCE*\n`;
    msg += `• Total Sales (GMV): ${formatPeso(kpis.total_gmv)}\n`;
    msg += `• Total Ad Spend: ${formatPeso(kpis.total_ad_spend)}\n`;
    msg += `• Blended ROAS: ${kpis.blended_roas}x\n`;
    msg += `• Total Orders: ${formatNumber(kpis.total_orders)}\n`;
    msg += `• Daily Target: ${formatPeso(kpis.daily_target)} (${kpis.target_progress}%)\n\n`;

    msg += `🏪 *STOREFRONT BREAKDOWN*\n`;
    props.report.shops.forEach((shop, index) => {
        // Find latest hour value
        const lastHourKey = hoursList.value
            .map(h => h.key)
            .reverse()
            .find(h => shop.metrics.sales[h] !== null);

        if (lastHourKey !== undefined) {
            const s = shop.metrics.sales[lastHourKey] || 0;
            const spend = shop.metrics.ad_spend[lastHourKey] || 0;
            const ord = shop.metrics.orders[lastHourKey] || 0;
            const roas = shop.metrics.roas[lastHourKey] || 0;

            msg += `${index + 1}. *${shop.shop_name}*\n`;
            msg += `   GMV: ${formatPeso(s)} | Spend: ${formatPeso(spend)} | ROAS: ${roas}x | Orders: ${ord}\n`;
        }
    });

    msg += `\n_Generated automatically via KCG Reporting System_`;
    return msg;
});

function copyDispatchToClipboard() {
    navigator.clipboard.writeText(dispatchMessage.value);
    copied.value = true;
    toast.success('Formatted report copied to clipboard! Ready to send.');
    setTimeout(() => {
        copied.value = false;
    }, 2500);
}
</script>

<template>
    <AppLayout>
        <Head title="TikTok Hourly Breakdown (GMV per shop by hour) - KCG Automated Reports" />

        <!-- 1. Top Sub-header & Action Toolbar -->
        <div class="mb-6 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="w-8 h-8 rounded-lg bg-[#ff0050] text-white flex items-center justify-center font-black text-sm shadow-sm shadow-rose-500/20">
                            TT
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">TikTok Hourly Breakdown</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Live Hourly Automation
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Automated GMV, Ad Spend, Orders, and ROAS per shop by hour tracking for official TikTok storefronts
                    </p>
                </div>

                <!-- Action Controls -->
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Date Selector Input -->
                    <div class="flex items-center bg-slate-50 rounded-xl border border-slate-200/80 px-2.5 py-1.5 gap-2 text-xs">
                        <Calendar class="w-4 h-4 text-slate-400" />
                        <input
                            type="date"
                            v-model="selectedDate"
                            @change="applyDateFilter"
                            class="bg-transparent text-slate-800 font-semibold focus:outline-none cursor-pointer"
                        />
                    </div>

                    <!-- Target Editor -->
                    <div class="hidden sm:flex items-center bg-slate-50 rounded-xl border border-slate-200/80 px-2.5 py-1.5 gap-1.5 text-xs">
                        <Target class="w-4 h-4 text-rose-500" />
                        <span class="text-slate-500 font-medium">Target:</span>
                        <input
                            type="number"
                            v-model.lazy="targetInput"
                            @change="applyTargetChange"
                            step="10000"
                            class="w-20 bg-transparent text-slate-900 font-bold focus:outline-none"
                            title="Daily Sales Target"
                        />
                    </div>

                    <!-- Copy Message Button -->
                    <button
                        @click="showDispatchModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition"
                        title="View & copy group chat dispatch"
                    >
                        <Send class="w-3.5 h-3.5 text-slate-600" />
                        <span>Dispatch Text</span>
                    </button>

                    <!-- Export Excel (.xlsx) -->
                    <button
                        @click="downloadExcel"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs shadow-emerald-600/20 transition active:scale-95"
                    >
                        <FileSpreadsheet class="w-3.5 h-3.5" />
                        <span>Export Excel (.xlsx)</span>
                    </button>

                    <!-- Export CSV -->
                    <button
                        @click="downloadCsv"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-95"
                    >
                        <Download class="w-3.5 h-3.5" />
                        <span>CSV</span>
                    </button>

                    <!-- Simulate Midnight -->
                    <button
                        v-if="!report.summary.total_sales[24]"
                        @click="simulateMidnight"
                        :disabled="isSimulating"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white rounded-xl text-xs font-semibold shadow-xs shadow-rose-500/20 transition active:scale-95"
                        title="Simulate 12:00 AM Midnight snapshot to conclude day"
                    >
                        <Play class="w-3 h-3 fill-current" />
                        <span>Simulate 12 AM</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. High-Level KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-6">
            <!-- Total GMV -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                    <span>Total Sales (GMV)</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600">
                        <DollarSign class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900 tracking-tight">
                    {{ formatPeso(report.kpis.total_gmv) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400 flex items-center gap-1">
                    <span>Snapshot at {{ report.meta.latest_hour || 'Active' }}</span>
                </div>
            </div>

            <!-- Total Ad Spend -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                    <span>Total Ad Spend</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                        <Zap class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900 tracking-tight">
                    {{ formatPeso(report.kpis.total_ad_spend) }}
                </div>
                <div class="mt-1 text-[11px] text-amber-600 font-medium">
                    Aggregated across all 6 shops
                </div>
            </div>

            <!-- Blended ROAS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                    <span>Blended ROAS</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <TrendingUp class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-indigo-600 tracking-tight">
                    {{ report.kpis.blended_roas }}x
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    Gross GMV / Total Ad Spend
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                    <span>Total Orders</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <ShoppingBag class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900 tracking-tight">
                    {{ formatNumber(report.kpis.total_orders) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    Completed orders day-to-date
                </div>
            </div>

            <!-- Daily Target Progress -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                    <span>Daily Target</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 font-bold text-xs">
                        {{ report.kpis.target_progress }}%
                    </div>
                </div>
                <div class="mt-2 text-xl font-bold text-slate-900 tracking-tight flex items-baseline justify-between">
                    <span>{{ formatPeso(report.kpis.daily_target) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                    <div
                        class="bg-gradient-to-r from-rose-500 to-pink-500 h-2 rounded-full transition-all duration-500"
                        :style="{ width: `${Math.min(100, report.kpis.target_progress)}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- 3. The Authentic Spreadsheet Matrix Container -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <!-- Spreadsheet Top Title Ribbon (Replicating exact reference banner) -->
            <div class="bg-[#e11d48] text-white flex flex-col sm:flex-row items-stretch sm:items-center border-b border-rose-700">
                <!-- Left Date Box -->
                <div class="bg-rose-900/40 px-4 py-2.5 border-b sm:border-b-0 sm:border-r border-rose-400/30 flex items-center gap-2 min-w-[260px]">
                    <span class="text-xs font-black tracking-wider uppercase text-rose-200">DATE:</span>
                    <span class="text-sm font-bold bg-amber-100 text-slate-900 px-3 py-0.5 rounded shadow-2xs">
                        {{ report.meta.formatted_date }}
                    </span>
                </div>

                <!-- Main Title Header -->
                <div class="px-5 py-2.5 text-center flex-1 font-bold text-xs sm:text-sm uppercase tracking-wider text-white shadow-inner">
                    TIKTOK — HOURLY BREAKDOWN (GMV per shop by hour)
                </div>
            </div>

            <!-- Scrollable Matrix Table -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-xs text-left border-collapse select-text">
                    <!-- Column Header Row -->
                    <thead>
                        <tr class="bg-[#e11d48] text-white text-[11px] font-bold border-b border-rose-800">
                            <th class="py-2.5 px-3 text-center border-r border-rose-500/40 min-w-[150px] sticky left-0 z-20 bg-[#e11d48]">
                                SHOP
                            </th>
                            <th class="py-2.5 px-2 text-center border-r border-rose-500/40 min-w-[95px] sticky left-[150px] z-20 bg-[#e11d48]">
                                METRIC
                            </th>
                            <th
                                v-for="h in hoursList"
                                :key="h.key"
                                class="py-2.5 px-2.5 text-center border-r border-rose-500/40 min-w-[84px]"
                            >
                                {{ h.label }}
                            </th>
                        </tr>
                    </thead>

                    <!-- Storefront Blocks -->
                    <tbody>
                        <template v-for="(shop, sIndex) in report.shops" :key="shop.shop_id">
                            <!-- Metric 1: AD SPEND -->
                            <tr class="hover:brightness-95 transition-colors border-t border-slate-200/80">
                                <!-- Merged Shop Name (spans 4 rows) -->
                                <td
                                    rowspan="4"
                                    class="py-3 px-3 text-center font-bold text-xs text-[#e11d48] bg-white border-r border-slate-200/80 sticky left-0 z-10 align-middle shadow-sm"
                                >
                                    <div class="leading-tight">
                                        {{ shop.shop_name }}
                                    </div>
                                    <span class="block text-[10px] text-slate-400 font-mono font-normal mt-0.5">
                                        {{ shop.shop_code }}
                                    </span>
                                </td>

                                <!-- Metric Label -->
                                <td class="py-2 px-2 text-center font-bold text-[10px] text-slate-700 bg-[#fde8d7] border-r border-slate-200/80 sticky left-[150px] z-10">
                                    AD SPEND
                                </td>

                                <!-- Hourly Columns for Ad Spend -->
                                <td
                                    v-for="h in hoursList"
                                    :key="h.key"
                                    class="py-2 px-2.5 text-right font-medium text-slate-800 bg-[#fde8d7]/70 border-r border-slate-200/50"
                                >
                                    {{ shop.metrics.ad_spend[h.key] !== null ? formatNumber(shop.metrics.ad_spend[h.key]) : '' }}
                                </td>
                            </tr>

                            <!-- Metric 2: ORDERS -->
                            <tr class="hover:brightness-95 transition-colors">
                                <td class="py-2 px-2 text-center font-bold text-[10px] text-emerald-800 bg-[#e2f7ea] border-r border-slate-200/80 sticky left-[150px] z-10">
                                    ORDERS
                                </td>
                                <td
                                    v-for="h in hoursList"
                                    :key="h.key"
                                    class="py-2 px-2.5 text-right font-medium text-slate-800 bg-[#e2f7ea]/70 border-r border-slate-200/50"
                                >
                                    {{ shop.metrics.orders[h.key] !== null ? formatNumber(shop.metrics.orders[h.key]) : '' }}
                                </td>
                            </tr>

                            <!-- Metric 3: SALES -->
                            <tr class="hover:brightness-95 transition-colors">
                                <td class="py-2 px-2 text-center font-bold text-[10px] text-amber-900 bg-[#fff2cc] border-r border-slate-200/80 sticky left-[150px] z-10">
                                    SALES
                                </td>
                                <td
                                    v-for="h in hoursList"
                                    :key="h.key"
                                    class="py-2 px-2.5 text-right font-bold text-slate-900 bg-[#fff2cc]/70 border-r border-slate-200/50"
                                >
                                    {{ shop.metrics.sales[h.key] !== null ? formatNumber(shop.metrics.sales[h.key]) : '' }}
                                </td>
                            </tr>

                            <!-- Metric 4: ROAS -->
                            <tr class="hover:brightness-95 transition-colors border-b-2 border-slate-300">
                                <td class="py-2 px-2 text-center font-bold text-[10px] text-sky-900 bg-[#e0f2fe] border-r border-slate-200/80 sticky left-[150px] z-10">
                                    ROAS
                                </td>
                                <td
                                    v-for="h in hoursList"
                                    :key="h.key"
                                    class="py-2 px-2.5 text-right font-bold text-slate-800 bg-[#e0f2fe]/70 border-r border-slate-200/50"
                                >
                                    {{ shop.metrics.roas[h.key] !== null ? `${shop.metrics.roas[h.key]}x` : '' }}
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    <!-- Summary Rows Footer (TOTAL TIKTOK HOURLY) -->
                    <tfoot>
                        <!-- Subheader Banner -->
                        <tr class="bg-[#e11d48] text-white text-xs font-bold">
                            <td :colspan="hoursList.length + 2" class="py-2 px-4 text-center tracking-wider uppercase border-t border-rose-700">
                                TOTAL TIKTOK HOURLY
                            </td>
                        </tr>

                        <!-- Row 1: TOTAL SALES (P) -->
                        <tr class="bg-[#e11d48] text-white font-bold border-t border-rose-600">
                            <td class="py-2.5 px-3 text-center border-r border-rose-500/50 sticky left-0 z-20 bg-[#e11d48]">
                                TOTAL TIKTOK
                            </td>
                            <td class="py-2.5 px-2 text-center border-r border-rose-500/50 sticky left-[150px] z-20 bg-[#e11d48] text-[10px]">
                                TOTAL SALES (P)
                            </td>
                            <td
                                v-for="h in hoursList"
                                :key="h.key"
                                class="py-2.5 px-2.5 text-right border-r border-rose-500/50 font-black text-[11px]"
                            >
                                {{ report.summary.total_sales[h.key] !== null ? formatNumber(report.summary.total_sales[h.key]) : '' }}
                            </td>
                        </tr>

                        <!-- Row 2: SALES INCREMENT -->
                        <tr class="bg-white font-semibold text-slate-800 border-t border-slate-200">
                            <td class="py-2.5 px-3 text-center border-r border-slate-200/80 sticky left-0 z-20 bg-[#e11d48] text-white font-bold">
                                TOTAL TIKTOK
                            </td>
                            <td class="py-2.5 px-2 text-center border-r border-slate-200/80 sticky left-[150px] z-20 bg-[#e11d48] text-white font-bold text-[10px]">
                                SALES INCREMENT
                            </td>
                            <td
                                v-for="h in hoursList"
                                :key="h.key"
                                class="py-2.5 px-2.5 text-right border-r border-slate-200/50 font-bold"
                                :class="[
                                    report.summary.sales_increment[h.key] !== null && report.summary.sales_increment[h.key]! > 0 ? 'text-slate-900 bg-slate-50' : 'text-slate-400 bg-white'
                                ]"
                            >
                                <span v-if="report.summary.sales_increment[h.key] !== null">
                                    {{ formatNumber(report.summary.sales_increment[h.key]) }}
                                </span>
                                <span v-else>-</span>
                            </td>
                        </tr>

                        <!-- Row 3: VS DAILY TARGET -->
                        <tr class="bg-rose-50/70 font-semibold text-slate-800 border-t border-rose-100">
                            <td class="py-2.5 px-3 text-center border-r border-slate-200/80 sticky left-0 z-20 bg-[#e11d48] text-white font-bold">
                                TOTAL TIKTOK
                            </td>
                            <td class="py-2.5 px-2 text-center border-r border-slate-200/80 sticky left-[150px] z-20 bg-[#e11d48] text-white font-bold text-[10px]">
                                VS DAILY TARGET
                            </td>
                            <td
                                v-for="h in hoursList"
                                :key="h.key"
                                class="py-2.5 px-2.5 text-center border-r border-slate-200/50 font-bold text-xs"
                            >
                                <div v-if="report.summary.vs_daily_target[h.key] !== null" class="inline-flex items-center gap-1">
                                    <span
                                        class="w-2 h-2 rounded-full inline-block"
                                        :class="[
                                            report.summary.vs_daily_target[h.key]! >= 50 ? 'bg-emerald-500' :
                                            report.summary.vs_daily_target[h.key]! >= 30 ? 'bg-amber-500' : 'bg-rose-500'
                                        ]"
                                    ></span>
                                    <span>{{ report.summary.vs_daily_target[h.key] }}%</span>
                                </div>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Spreadsheet Footer Status Bar -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-2">
                    <Clock class="w-3.5 h-3.5 text-slate-400" />
                    <span>Calculations computed automatically in Philippine Standard Time (PHT, UTC+8)</span>
                </div>
                <div class="flex items-center gap-4 text-[11px]">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-[#fde8d7] border border-amber-300"></span> Ad Spend</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-[#e2f7ea] border border-emerald-300"></span> Orders</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-[#fff2cc] border border-yellow-300"></span> Sales (GMV)</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-[#e0f2fe] border border-sky-300"></span> ROAS</span>
                </div>
            </div>
        </div>

        <!-- 4. Dispatch Modal Dialog -->
        <div
            v-if="showDispatchModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-in fade-in"
        >
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-xl w-full p-6 relative">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <Send class="w-4 h-4 text-rose-600" />
                        <h3 class="font-bold text-slate-900 text-sm">Formatted Group Chat Dispatch Message</h3>
                    </div>
                    <button
                        @click="showDispatchModal = false"
                        class="text-slate-400 hover:text-slate-600 p-1 text-sm rounded-lg hover:bg-slate-100"
                    >
                        ✕
                    </button>
                </div>

                <p class="text-xs text-slate-500 mt-2 mb-3">
                    Copy and paste directly into Viber, Telegram, Slack, or WhatsApp operations chat groups:
                </p>

                <div class="bg-slate-900 text-slate-100 p-4 rounded-xl font-mono text-xs overflow-x-auto whitespace-pre-wrap max-h-72 border border-slate-800">
                    {{ dispatchMessage }}
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        Snapshot as of: {{ report.meta.latest_hour || 'Latest' }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button
                            @click="showDispatchModal = false"
                            class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition"
                        >
                            Close
                        </button>
                        <button
                            @click="copyDispatchToClipboard"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-rose-600/20 transition active:scale-95"
                        >
                            <Check v-if="copied" class="w-3.5 h-3.5" />
                            <Copy v-else class="w-3.5 h-3.5" />
                            <span>{{ copied ? 'Copied!' : 'Copy to Clipboard' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
