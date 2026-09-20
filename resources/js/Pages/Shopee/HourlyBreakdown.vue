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
        platform: string;
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
    router.get('/shopee', {
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
    toast.success(`Shopee daily target updated to ${formatPeso(targetInput.value)}`);
}

function downloadExcel() {
    const url = `/shopee/export/excel?date=${selectedDate.value}&target=${targetInput.value}`;
    window.location.href = url;
    toast.success('Downloading Shopee Excel workbook (.xlsx)...');
}

function downloadCsv() {
    const url = `/shopee/export/csv?date=${selectedDate.value}&target=${targetInput.value}`;
    window.location.href = url;
    toast.success('Downloading Shopee CSV file...');
}

function simulateMidnight() {
    if (isSimulating.value) return;
    isSimulating.value = true;
    const toastId = toast.loading('Simulating Shopee 12:00 AM final push...');

    router.post('/shopee/simulate-midnight', {
        date: selectedDate.value
    }, {
        onSuccess: () => {
            toast.success('Shopee 12:00 AM Midnight snapshot populated successfully!', { id: toastId });
        },
        onError: () => {
            toast.error('Simulation failed. Check logs.', { id: toastId });
        },
        onFinish: () => {
            isSimulating.value = false;
        }
    });
}

// Build formatted chat message matching the required Shopee hourly dispatch format
const dispatchMessage = computed(() => {
    const meta = props.report.meta;
    const kpis = props.report.kpis;
    const latestHour = meta.latest_hour || '11:00 PM';

    let msg = `🟠 *SHOPEE HOURLY GMV REPORT* 🟠\n`;
    msg += `📅 Date: ${meta.formatted_date}\n`;
    msg += `⏰ Snapshot as of: ${latestHour} (PHT)\n\n`;

    msg += `📊 *OVERALL SHOPEE PERFORMANCE*\n`;
    msg += `• Total Sales (GMV): ${formatPeso(kpis.total_gmv)}\n`;
    msg += `• Shopee Ads Spend: ${formatPeso(kpis.total_ad_spend)}\n`;
    msg += `• Blended ROAS: ${kpis.blended_roas.toFixed(2)}x\n`;
    msg += `• Total Orders: ${formatNumber(kpis.total_orders)}\n`;
    msg += `• Daily Target: ${formatPeso(kpis.daily_target)} (${kpis.target_progress}% achieved)\n\n`;

    msg += `🏬 *SHOPEE STORE BREAKDOWN:*\n`;
    props.report.shops.forEach(s => {
        const lastSales = getLatestMetricValue(s.metrics.sales);
        const lastSpend = getLatestMetricValue(s.metrics.ad_spend);
        const roas = lastSpend > 0 ? (lastSales / lastSpend).toFixed(2) + 'x' : '0.00x';
        msg += `▸ *${s.shop_name}*: ${formatPeso(lastSales)} | Spend: ${formatPeso(lastSpend)} | ROAS: ${roas}\n`;
    });

    msg += `\n_Generated via KCG Shopee Reporting Pipeline_`;
    return msg;
});

function getLatestMetricValue(metricRecord: Record<number, number | null>): number {
    const validHours = Object.keys(metricRecord)
        .map(Number)
        .filter(h => metricRecord[h] !== null && metricRecord[h] !== undefined)
        .sort((a, b) => b - a);

    if (validHours.length === 0) return 0;
    return metricRecord[validHours[0]] || 0;
}

function copyDispatch() {
    navigator.clipboard.writeText(dispatchMessage.value);
    copied.value = true;
    toast.success('Shopee hourly dispatch copied to clipboard!');
    setTimeout(() => {
        copied.value = false;
    }, 2500);
}
</script>

<template>
    <AppLayout>
        <Head title="Shopee Hourly Breakdown (GMV per shop by hour) - Independent Reporting" />

        <!-- 1. Header Navigation & Branding Banner -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <div class="w-9 h-9 rounded-xl bg-orange-500 text-white flex items-center justify-center font-black text-sm shadow-xs shadow-orange-500/20">
                            S
                        </div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Shopee — Hourly Breakdown</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 text-orange-800 border border-orange-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                            INDEPENDENT SHOPEE REPORT
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Independent GMV, Shopee Ads Spend, Orders, and ROAS tracking per Shopee storefront from 9:00 AM to 12:00 AM Midnight.
                    </p>
                </div>

                <!-- Date picker & export actions -->
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 rounded-xl px-3 py-1.5 text-xs text-slate-700">
                        <Calendar class="w-3.5 h-3.5 text-slate-400" :stroke-width="1.75" />
                        <span class="font-medium text-slate-500">Date:</span>
                        <input
                            type="date"
                            v-model="selectedDate"
                            @change="handleDateChange(selectedDate)"
                            class="bg-transparent border-0 font-bold text-slate-800 focus:ring-0 p-0 text-xs cursor-pointer"
                        />
                    </div>

                    <button
                        @click="downloadExcel"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs shadow-emerald-600/20 transition active:scale-[0.98]"
                        title="Download exact Microsoft Excel workbook formatted with Shopee styling"
                    >
                        <FileSpreadsheet class="w-3.5 h-3.5" :stroke-width="2" />
                        <span>Export Excel (.xlsx)</span>
                    </button>

                    <button
                        @click="downloadCsv"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98]"
                        title="Download raw CSV data"
                    >
                        <FileText class="w-3.5 h-3.5" :stroke-width="2" />
                        <span>CSV</span>
                    </button>

                    <button
                        @click="showDispatchModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 rounded-xl text-xs font-semibold transition active:scale-[0.98]"
                        title="Generate ready-to-send Viber/Telegram text report"
                    >
                        <Send class="w-3.5 h-3.5" :stroke-width="2" />
                        <span>Dispatch Text</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. KPI Overview Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 mb-6">
            <!-- Total GMV Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-amber-500"></div>
                <div class="flex items-center justify-between text-slate-500 text-xs mb-1">
                    <span class="font-medium">Total Shopee GMV</span>
                    <Sparkles class="w-4 h-4 text-orange-500" :stroke-width="1.75" />
                </div>
                <div class="text-xl font-black text-slate-900 tracking-tight">
                    {{ formatPeso(report.kpis.total_gmv) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    As of {{ report.meta.latest_hour || 'end of day' }}
                </div>
            </div>

            <!-- Shopee Ads Spend Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400"></div>
                <div class="flex items-center justify-between text-slate-500 text-xs mb-1">
                    <span class="font-medium">Shopee Ads Spend</span>
                    <DollarSign class="w-4 h-4 text-amber-500" :stroke-width="1.75" />
                </div>
                <div class="text-xl font-black text-slate-900 tracking-tight">
                    {{ formatPeso(report.kpis.total_ad_spend) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Cumulative marketplace ads
                </div>
            </div>

            <!-- Blended ROAS Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                <div class="flex items-center justify-between text-slate-500 text-xs mb-1">
                    <span class="font-medium">Shopee ROAS</span>
                    <TrendingUp class="w-4 h-4 text-blue-500" :stroke-width="1.75" />
                </div>
                <div class="text-xl font-black text-blue-600 tracking-tight">
                    {{ report.kpis.blended_roas.toFixed(2) }}x
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    GMV / Ads Spend
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                <div class="flex items-center justify-between text-slate-500 text-xs mb-1">
                    <span class="font-medium">Total Orders</span>
                    <ShoppingBag class="w-4 h-4 text-emerald-500" :stroke-width="1.75" />
                </div>
                <div class="text-xl font-black text-slate-900 tracking-tight">
                    {{ formatNumber(report.kpis.total_orders) }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Orders across 4 stores
                </div>
            </div>

            <!-- Target Progress Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden col-span-2 lg:col-span-1">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-rose-500"></div>
                <div class="flex items-center justify-between text-slate-500 text-xs mb-1">
                    <span class="font-medium">Target Progress</span>
                    <Target class="w-4 h-4 text-orange-500" :stroke-width="1.75" />
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xl font-black text-orange-600 tracking-tight">
                        {{ report.kpis.target_progress }}%
                    </span>
                    <span class="text-xs text-slate-400 font-medium">of {{ formatPeso(report.kpis.daily_target) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div
                        class="bg-gradient-to-r from-orange-500 to-amber-500 h-1.5 rounded-full transition-all duration-500"
                        :style="{ width: `${Math.min(report.kpis.target_progress, 100)}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- 3. Target Configuration & Midnight Simulation Banner -->
        <div class="mb-6 bg-gradient-to-r from-orange-50/80 via-amber-50/60 to-white p-4 rounded-2xl border border-orange-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                    <Target class="w-4 h-4" :stroke-width="2" />
                </div>
                <div>
                    <span class="font-bold text-slate-800">Shopee Daily Sales Target:</span>
                    <span class="text-slate-500 ml-1">Configure daily benchmark for hourly percentage pacing.</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-semibold">₱</span>
                    <input
                        type="number"
                        v-model.number="targetInput"
                        class="w-32 pl-6 pr-2 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="500000"
                    />
                </div>
                <button
                    @click="applyTargetChange"
                    class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold shadow-xs transition active:scale-[0.98]"
                >
                    Update Target
                </button>

                <button
                    @click="simulateMidnight"
                    :disabled="isSimulating"
                    class="ml-2 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-orange-50 text-orange-700 border border-orange-300 rounded-xl font-semibold transition active:scale-[0.98] disabled:opacity-50"
                    title="Simulate the final 12:00 AM Midnight push"
                >
                    <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isSimulating }" :stroke-width="2" />
                    <span>Simulate 12 AM Push</span>
                </button>
            </div>
        </div>

        <!-- 4. Matrix Breakdown Table (Exact Corporate Excel replica) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
            <!-- Table Header Banner -->
            <div class="bg-gradient-to-r from-orange-500 via-orange-600 to-amber-600 text-white px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center font-black text-xs">
                        S
                    </div>
                    <div>
                        <h2 class="font-bold text-sm tracking-tight">SHOPEE — HOURLY BREAKDOWN (GMV per shop by hour)</h2>
                        <p class="text-[11px] text-orange-100">
                            DATE: {{ report.meta.formatted_date }} | All amounts in Philippine Peso (PHP ₱)
                        </p>
                    </div>
                </div>
                <div class="text-[11px] text-orange-100 hidden sm:block">
                    Standard Business Hours: 9:00 AM to 12:00 AM
                </div>
            </div>

            <!-- Scrollable Matrix Table Container -->
            <div class="overflow-x-auto max-w-full">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-orange-500 text-white border-b border-orange-600 text-[11px]">
                            <th class="py-2.5 px-3 text-center font-bold sticky left-0 z-20 bg-orange-500 border-r border-orange-600 min-w-[160px]">
                                SHOP
                            </th>
                            <th class="py-2.5 px-2 text-center font-bold border-r border-orange-600 min-w-[90px]">
                                METRIC
                            </th>
                            <th
                                v-for="h in hoursList"
                                :key="h.key"
                                class="py-2 px-2 text-center font-bold border-r border-orange-600 min-w-[80px]"
                            >
                                {{ h.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Loop over each Shopee Shop (4 rows each: Ad Spend, Orders, Sales, ROAS) -->
                        <template v-for="shop in report.shops" :key="shop.shop_id">
                            <!-- Row 1: AD SPEND -->
                            <tr class="hover:bg-slate-50/50">
                                <!-- Shop Name (Merged 4 rows in UI) -->
                                <td
                                    rowspan="4"
                                    class="py-3 px-3 font-bold text-orange-700 bg-white text-center border-r border-slate-200 sticky left-0 z-10 shadow-xs align-middle"
                                >
                                    <div class="text-xs tracking-tight font-extrabold">{{ shop.shop_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ shop.shop_code }}</div>
                                </td>

                                <!-- Metric Label: AD SPEND -->
                                <td class="py-1.5 px-2 font-bold text-center border-r border-slate-200 bg-amber-50/80 text-amber-900 text-[10px]">
                                    AD SPEND
                                </td>
                                <!-- Metric Values -->
                                <td
                                    v-for="h in hoursList"
                                    :key="`spend-${h.key}`"
                                    class="py-1.5 px-2 text-center border-r border-slate-100 bg-amber-50/40 text-slate-700 font-mono text-[11px]"
                                >
                                    {{ shop.metrics.ad_spend[h.key] !== null ? formatNumber(shop.metrics.ad_spend[h.key]!) : '' }}
                                </td>
                            </tr>

                            <!-- Row 2: ORDERS -->
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-1.5 px-2 font-bold text-center border-r border-slate-200 bg-emerald-50/80 text-emerald-900 text-[10px]">
                                    ORDERS
                                </td>
                                <td
                                    v-for="h in hoursList"
                                    :key="`orders-${h.key}`"
                                    class="py-1.5 px-2 text-center border-r border-slate-100 bg-emerald-50/40 text-slate-700 font-mono text-[11px]"
                                >
                                    {{ shop.metrics.orders[h.key] !== null ? formatNumber(shop.metrics.orders[h.key]!) : '' }}
                                </td>
                            </tr>

                            <!-- Row 3: SALES -->
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-1.5 px-2 font-bold text-center border-r border-slate-200 bg-orange-50/80 text-orange-950 text-[10px]">
                                    SALES
                                </td>
                                <td
                                    v-for="h in hoursList"
                                    :key="`sales-${h.key}`"
                                    class="py-1.5 px-2 text-center border-r border-slate-100 bg-orange-50/40 text-slate-900 font-semibold font-mono text-[11px]"
                                >
                                    {{ shop.metrics.sales[h.key] !== null ? formatNumber(shop.metrics.sales[h.key]!) : '' }}
                                </td>
                            </tr>

                            <!-- Row 4: ROAS -->
                            <tr class="hover:bg-slate-50/50 border-b-2 border-slate-200">
                                <td class="py-1.5 px-2 font-bold text-center border-r border-slate-200 bg-sky-50/80 text-sky-900 text-[10px]">
                                    ROAS
                                </td>
                                <td
                                    v-for="h in hoursList"
                                    :key="`roas-${h.key}`"
                                    class="py-1.5 px-2 text-center border-r border-slate-100 bg-sky-50/40 text-sky-900 font-bold font-mono text-[11px]"
                                >
                                    {{ shop.metrics.roas[h.key] !== null ? `${Number(shop.metrics.roas[h.key]).toFixed(2)}x` : '' }}
                                </td>
                            </tr>
                        </template>

                        <!-- Summary Section Subheader Banner -->
                        <tr class="bg-orange-500 text-white font-bold text-[11px]">
                            <td :colspan="hoursList.length + 2" class="py-2 px-4 tracking-wider text-center uppercase">
                                TOTAL SHOPEE HOURLY
                            </td>
                        </tr>

                        <!-- Summary Row 1: TOTAL SALES (P) -->
                        <tr class="bg-orange-600 text-white font-bold text-xs">
                            <td class="py-2.5 px-3 text-center border-r border-orange-700 sticky left-0 z-10 bg-orange-600">
                                TOTAL SHOPEE
                            </td>
                            <td class="py-2.5 px-2 text-center border-r border-orange-700 bg-orange-700 text-[10px] uppercase">
                                TOTAL SALES (₱)
                            </td>
                            <td
                                v-for="h in hoursList"
                                :key="`total-sales-${h.key}`"
                                class="py-2.5 px-2 text-center border-r border-orange-700 font-mono tracking-tight font-black"
                            >
                                {{ report.summary.total_sales[h.key] !== null ? formatNumber(report.summary.total_sales[h.key]!) : '' }}
                            </td>
                        </tr>

                        <!-- Summary Row 2: SALES INCREMENT -->
                        <tr class="bg-slate-50 font-semibold text-slate-800 text-xs">
                            <td class="py-2 px-3 text-center border-r border-slate-200 sticky left-0 z-10 bg-slate-50 font-bold text-slate-700">
                                TOTAL SHOPEE
                            </td>
                            <td class="py-2 px-2 text-center border-r border-slate-200 bg-slate-100 text-[10px] font-bold text-slate-700 uppercase">
                                SALES INCREMENT
                            </td>
                            <td
                                v-for="h in hoursList"
                                :key="`sales-inc-${h.key}`"
                                class="py-2 px-2 text-center border-r border-slate-200 font-mono text-[11px]"
                                :class="{
                                    'text-emerald-600 font-bold': (report.summary.sales_increment[h.key] || 0) > 0,
                                    'text-rose-600 font-bold': (report.summary.sales_increment[h.key] || 0) < 0,
                                    'text-slate-400': report.summary.sales_increment[h.key] === null
                                }"
                            >
                                {{ report.summary.sales_increment[h.key] !== null ? formatNumber(report.summary.sales_increment[h.key]!) : '-' }}
                            </td>
                        </tr>

                        <!-- Summary Row 3: VS DAILY TARGET -->
                        <tr class="bg-orange-50/70 font-semibold text-orange-950 text-xs">
                            <td class="py-2 px-3 text-center border-r border-orange-200 sticky left-0 z-10 bg-orange-50 font-bold text-orange-800">
                                TOTAL SHOPEE
                            </td>
                            <td class="py-2 px-2 text-center border-r border-orange-200 bg-orange-100/70 text-[10px] font-bold text-orange-900 uppercase">
                                VS DAILY TARGET
                            </td>
                            <td
                                v-for="h in hoursList"
                                :key="`vs-target-${h.key}`"
                                class="py-2 px-2 text-center border-r border-orange-200 font-mono text-[11px] font-bold"
                                :class="{
                                    'text-orange-700': (report.summary.vs_daily_target[h.key] || 0) > 0,
                                    'text-slate-400': report.summary.vs_daily_target[h.key] === null
                                }"
                            >
                                {{ report.summary.vs_daily_target[h.key] !== null ? `${report.summary.vs_daily_target[h.key]}%` : '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Recent Automation Logs for Shopee -->
        <div v-if="logs && logs.length > 0" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs mb-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <Clock class="w-4 h-4 text-orange-500" :stroke-width="1.75" />
                    <h3 class="font-bold text-sm text-slate-800">Recent Shopee Automation Activity</h3>
                </div>
                <span class="text-xs text-slate-400">Server Time: {{ serverTime }}</span>
            </div>

            <div class="space-y-2">
                <div
                    v-for="log in logs"
                    :key="log.id"
                    class="p-3 rounded-xl border border-slate-100 bg-slate-50/60 flex items-start justify-between text-xs gap-3"
                >
                    <div class="flex items-start gap-2.5">
                        <span
                            class="w-2 h-2 rounded-full mt-1.5 shrink-0"
                            :class="log.status === 'SUCCESS' ? 'bg-emerald-500' : 'bg-rose-500'"
                        ></span>
                        <div>
                            <span class="font-semibold text-slate-800">{{ log.job }}:</span>
                            <span class="text-slate-600 ml-1">{{ log.message }}</span>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 shrink-0 font-mono">{{ log.created_at }}</span>
                </div>
            </div>
        </div>

        <!-- 6. Dispatch Text Preview Modal -->
        <div
            v-if="showDispatchModal"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-150"
            @click.self="showDispatchModal = false"
        >
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <Send class="w-4 h-4 text-orange-500" />
                        <h3 class="font-bold text-base text-slate-900">Shopee Chat Dispatch Text</h3>
                    </div>
                    <button
                        @click="showDispatchModal = false"
                        class="text-slate-400 hover:text-slate-600 text-lg leading-none"
                    >
                        &times;
                    </button>
                </div>

                <p class="text-xs text-slate-500 my-3">
                    Pre-formatted text template ready to copy and paste directly into Viber, Telegram, or WhatsApp management group chats:
                </p>

                <textarea
                    readonly
                    class="w-full h-64 p-3 rounded-xl border border-slate-200 bg-slate-50 font-mono text-xs text-slate-800 leading-relaxed focus:outline-hidden resize-none"
                    :value="dispatchMessage"
                ></textarea>

                <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-slate-100">
                    <button
                        @click="showDispatchModal = false"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition"
                    >
                        Close
                    </button>
                    <button
                        @click="copyDispatch"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-semibold shadow-xs shadow-orange-500/20 transition active:scale-[0.98]"
                    >
                        <Check v-if="copied" class="w-3.5 h-3.5" />
                        <Copy v-else class="w-3.5 h-3.5" />
                        <span>{{ copied ? 'Copied to Clipboard!' : 'Copy Text' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
