<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import HourlyChart from '@/Components/HourlyChart.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatPeso, formatNumber, formatHour, formatShortHour } from '@/utils/currency';
import {
    ShoppingBag,
    Package,
    Receipt,
    TicketPercent,
    RotateCcw,
    Sparkles,
    Clock,
    TrendingUp,
    TrendingDown,
    ArrowRight,
    Store,
    Layers,
    CheckCircle2,
    AlertTriangle,
    FileSpreadsheet,
    Calendar,
    ChevronRight,
    Zap
} from 'lucide-vue-next';

interface KPIProps {
    total_orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
}

interface PlatformItem {
    platform_id: number;
    name: string;
    code: string;
    status: string;
    shops_count: number;
    orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
}

interface ShopItem {
    shop_id: number;
    shop_name: string;
    shop_code: string;
    platform_id: number;
    platform_name: string;
    platform_code: string;
    status: string;
    orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
}

interface HourlyItem {
    hour: number;
    orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
}

interface ComparisonProps {
    current: {
        hour: number;
        orders: number;
        units: number;
        gross_sales: number;
        net_sales: number;
    };
    previous: {
        hour: number;
        orders: number;
        units: number;
        gross_sales: number;
        net_sales: number;
    };
    diff: {
        orders: number;
        units: number;
        sales: number;
        percent: number;
    };
}

interface AutomationProps {
    status: string;
    schedule: string;
    cron: string;
    last_report_at: string | null;
    last_report_id: number | null;
    next_report_at: string;
}

const props = defineProps<{
    kpis: KPIProps;
    platforms: PlatformItem[];
    shops: ShopItem[];
    hourlyPerformance: HourlyItem[];
    comparison: ComparisonProps;
    automation: AutomationProps;
    meta: {
        current_date: string;
        current_time: string;
        selected_date?: string;
        today_date?: string;
        yesterday_date?: string;
    };
}>();

// Calculate platform share percentages
const platformShares = computed(() => {
    const total = Number(props.kpis.net_sales) || 1;
    return props.platforms.map(p => ({
        ...p,
        percentage: Math.max(0, Math.round((Number(p.net_sales) / total) * 100))
    }));
});
</script>

<template>
    <AppLayout>
        <Head title="Executive Management Dashboard - Hourly Reporting Automation" />

        <!-- 1. Header & Quick Status Strip -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Executive Management Dashboard</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            PROTOTYPE — DEMO DATA
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Centralized multi-platform sales analytics across Shopee and TikTok Shop • Consolidated every hour
                    </p>

                    <!-- Date Selector Filter Pills -->
                    <div class="mt-3.5 flex items-center gap-2 flex-wrap">
                        <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                            <Calendar class="w-3.5 h-3.5" :stroke-width="1.75" />
                            Viewing Date:
                        </span>
                        <Link
                            :href="`/?date=${meta.today_date}`"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition min-h-[36px]"
                            :class="meta.selected_date === meta.today_date ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                        >
                            <span>Today (Sep 19 - Current)</span>
                        </Link>
                        <Link
                            :href="`/?date=${meta.yesterday_date}`"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition min-h-[36px]"
                            :class="meta.selected_date === meta.yesterday_date ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                        >
                            <span>Yesterday (Sep 18 - Full Day)</span>
                        </Link>
                    </div>
                </div>

                <!-- Schedule & Operational Indicators -->
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Local Time (PHT)</span>
                        <span class="font-bold text-slate-800">{{ meta.current_date }} • {{ meta.current_time }}</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl px-3.5 py-2">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Last Report Snapshot</span>
                        <span class="font-bold text-slate-800">
                            {{ automation.last_report_at || 'None generated yet' }}
                        </span>
                    </div>

                    <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl px-3.5 py-2">
                        <span class="text-indigo-400 block text-[10px] uppercase font-semibold">Next Scheduled Snapshot</span>
                        <span class="font-bold text-indigo-700 flex items-center gap-1">
                            <Clock class="w-3 h-3" :stroke-width="2" />
                            {{ automation.next_report_at }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Main KPI Cards (Philippine Peso) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3.5 mb-6">
            <KpiCard
                title="Total Orders"
                :value="formatNumber(kpis.total_orders)"
                subtitle="Aggregated today"
                variant="default"
            >
                <template #icon>
                    <ShoppingBag class="w-4 h-4 text-blue-600" :stroke-width="1.75" />
                </template>
            </KpiCard>

            <KpiCard
                title="Units Sold"
                :value="formatNumber(kpis.units_sold)"
                subtitle="Items ordered"
                variant="default"
            >
                <template #icon>
                    <Package class="w-4 h-4 text-cyan-600" :stroke-width="1.75" />
                </template>
            </KpiCard>

            <KpiCard
                title="Gross Sales"
                :value="formatPeso(kpis.gross_sales)"
                subtitle="Before deductions"
                variant="default"
            >
                <template #icon>
                    <Receipt class="w-4 h-4 text-slate-600" :stroke-width="1.75" />
                </template>
            </KpiCard>

            <KpiCard
                title="Discounts"
                :value="formatPeso(kpis.discounts)"
                subtitle="Platform vouchers"
                variant="warning"
            >
                <template #icon>
                    <TicketPercent class="w-4 h-4 text-amber-600" :stroke-width="1.75" />
                </template>
            </KpiCard>

            <KpiCard
                title="Refunds"
                :value="formatPeso(kpis.refunds)"
                subtitle="Returns & cancels"
                variant="danger"
            >
                <template #icon>
                    <RotateCcw class="w-4 h-4 text-rose-600" :stroke-width="1.75" />
                </template>
            </KpiCard>

            <KpiCard
                title="Net Sales"
                :value="formatPeso(kpis.net_sales)"
                subtitle="Gross - Disc - Ref"
                variant="primary"
            >
                <template #icon>
                    <Sparkles class="w-4 h-4 text-indigo-600" :stroke-width="1.75" />
                </template>
            </KpiCard>
        </div>

        <!-- 3. Current Hour vs Previous Hour Comparison (Momentum Card) -->
        <div class="mb-6 bg-slate-900 border border-slate-800 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
            <!-- Subtle background accent glow -->
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-800 relative z-10">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200">Hourly Momentum & Velocity</h3>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Real-time comparative analysis (comparing {{ formatHour(comparison.current.hour) }} to {{ formatHour(comparison.previous.hour) }})
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-white/10 text-slate-300">
                        Hourly Delta:
                    </span>
                    <span
                        class="text-sm font-bold px-3 py-1 rounded-lg inline-flex items-center gap-1.5"
                        :class="comparison.diff.sales >= 0 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30'"
                    >
                        <TrendingUp v-if="comparison.diff.sales >= 0" class="w-4 h-4" :stroke-width="2" />
                        <TrendingDown v-else class="w-4 h-4" :stroke-width="2" />
                        {{ comparison.diff.sales >= 0 ? '+' : '' }}{{ formatPeso(comparison.diff.sales) }}
                        <span class="text-xs font-medium ml-0.5">({{ comparison.diff.percent }}%)</span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-xs relative z-10">
                <div class="bg-white/5 rounded-xl p-4 border border-white/10 hover:border-white/20 transition">
                    <div class="text-slate-400 font-medium flex items-center justify-between">
                        <span>Current Hour ({{ formatHour(comparison.current.hour) }})</span>
                        <span class="text-[10px] bg-indigo-500/30 text-indigo-200 px-2 py-0.5 rounded-full">Active</span>
                    </div>
                    <div class="mt-2.5 flex items-baseline justify-between">
                        <div>
                            <span class="text-2xl font-bold text-white tracking-tight">{{ formatPeso(comparison.current.net_sales) }}</span>
                            <span class="text-slate-400 block text-[11px] mt-0.5">Net Sales</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-semibold text-slate-200">{{ comparison.current.orders }} orders</span>
                            <span class="text-slate-400 block text-[11px] mt-0.5">{{ comparison.current.units }} units</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white/5 rounded-xl p-4 border border-white/10 hover:border-white/20 transition">
                    <div class="text-slate-400 font-medium">Previous Hour ({{ formatHour(comparison.previous.hour) }})</div>
                    <div class="mt-2.5 flex items-baseline justify-between">
                        <div>
                            <span class="text-2xl font-bold text-slate-300 tracking-tight">{{ formatPeso(comparison.previous.net_sales) }}</span>
                            <span class="text-slate-400 block text-[11px] mt-0.5">Net Sales</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-semibold text-slate-300">{{ comparison.previous.orders }} orders</span>
                            <span class="text-slate-400 block text-[11px] mt-0.5">{{ comparison.previous.units }} units</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white/5 rounded-xl p-4 border border-white/10 flex flex-col justify-center hover:border-white/20 transition">
                    <div class="text-slate-400 font-medium">Net Growth Delta</div>
                    <div class="mt-2.5 flex items-center justify-between">
                        <div>
                            <span class="text-xl font-bold tracking-tight" :class="comparison.diff.orders >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                                {{ comparison.diff.orders >= 0 ? '+' : '' }}{{ comparison.diff.orders }} Orders
                            </span>
                            <span class="text-slate-400 block text-[11px] mt-0.5">
                                {{ comparison.diff.units >= 0 ? '+' : '' }}{{ comparison.diff.units }} Units Sold
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-bold" :class="comparison.diff.sales >= 0 ? 'bg-emerald-400/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-400/20 text-rose-300 border border-rose-400/30'">
                                {{ comparison.diff.sales >= 0 ? '▲ Acceleration' : '▼ Dip' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Spotlight: TikTok Hourly Breakdown Automation -->
        <div class="mb-6 bg-gradient-to-r from-rose-900 via-rose-800 to-slate-900 rounded-2xl p-5 text-white shadow-md relative overflow-hidden border border-rose-700/50">
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-rose-500/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#ff0050] to-[#e11d48] text-white flex items-center justify-center font-black text-lg shadow-sm shadow-rose-500/30 shrink-0">
                        TT
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-bold text-white tracking-tight">TikTok Hourly Breakdown Automation</h3>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-500/30 text-rose-200 border border-rose-400/40">
                                Live Matrix
                            </span>
                        </div>
                        <p class="text-xs text-rose-100/80 mt-0.5 max-w-2xl">
                            Hourly GMV, Ad Spend, Orders, and ROAS per shop by hour (9:00 AM – 12:00 AM) across all 6 TikTok storefronts with Excel export and group chat dispatch.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        href="/tiktok"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-900 rounded-xl text-xs font-bold shadow-sm transition active:scale-95 shrink-0"
                    >
                        <span>Open TikTok Matrix</span>
                        <ChevronRight class="w-4 h-4 text-rose-700" :stroke-width="2.5" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- 4. Hourly Performance Line Chart & Metrics Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Hourly Performance Timeline</h3>
                    <p class="text-xs text-slate-500">
                        Net Sales progression plotted over 24 hours in Philippine Standard Time
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                        <span class="w-3 h-1 bg-indigo-600 rounded"></span> Net Sales
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-medium ml-2">
                        <span class="w-3 h-1 border-b border-dashed border-slate-400"></span> Gross Sales
                    </span>
                </div>
            </div>

            <!-- Chart -->
            <div class="py-4">
                <HourlyChart :data="hourlyPerformance" />
            </div>

            <!-- Hourly Table Underneath -->
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Hourly Performance Audit Breakdown</h4>
                    <Link href="/reports" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 min-h-[36px]">
                        <span>View Snapshot History</span>
                        <ChevronRight class="w-3.5 h-3.5" :stroke-width="2" />
                    </Link>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200/80 shadow-2xs">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3 px-4">Hour (PHT)</th>
                                <th class="py-3 px-4 text-right">Orders</th>
                                <th class="py-3 px-4 text-right">Units Sold</th>
                                <th class="py-3 px-4 text-right">Gross Sales</th>
                                <th class="py-3 px-4 text-right">Discounts</th>
                                <th class="py-3 px-4 text-right">Refunds</th>
                                <th class="py-3 px-4 text-right font-bold text-slate-900">Net Sales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr
                                v-for="item in hourlyPerformance"
                                :key="item.hour"
                                class="hover:bg-slate-50/70 transition-colors"
                            >
                                <td class="py-2.5 px-4 font-semibold text-slate-900">
                                    {{ formatShortHour(item.hour) }}
                                    <span class="text-[10px] text-slate-400 font-normal ml-1">({{ formatHour(item.hour) }})</span>
                                </td>
                                <td class="py-2.5 px-4 text-right text-slate-700">{{ formatNumber(item.orders) }}</td>
                                <td class="py-2.5 px-4 text-right text-slate-700">{{ formatNumber(item.units_sold) }}</td>
                                <td class="py-2.5 px-4 text-right text-slate-600">{{ formatPeso(item.gross_sales) }}</td>
                                <td class="py-2.5 px-4 text-right text-amber-600">-{{ formatPeso(item.discounts) }}</td>
                                <td class="py-2.5 px-4 text-right text-rose-600">-{{ formatPeso(item.refunds) }}</td>
                                <td class="py-2.5 px-4 text-right font-bold text-indigo-600">{{ formatPeso(item.net_sales) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. Platform Summary & Contribution Share -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs mb-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Platform Performance & Market Share</h3>
                    <p class="text-xs text-slate-500">
                        Consolidated multi-channel breakdown across Shopee and TikTok Shop
                    </p>
                </div>
                <Link href="/platforms" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 min-h-[36px]">
                    <span>Platform Settings</span>
                    <ChevronRight class="w-3.5 h-3.5" :stroke-width="2" />
                </Link>
            </div>

            <!-- Platform Share Progress Bar -->
            <div class="mt-4 p-4 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                    <span>Revenue Share Contribution</span>
                    <div class="flex items-center gap-4">
                        <span v-for="p in platformShares" :key="p.platform_id" class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full" :class="p.code === 'shopee' ? 'bg-orange-500' : 'bg-slate-900'"></span>
                            <span>{{ p.name }}: {{ p.percentage }}%</span>
                        </span>
                    </div>
                </div>
                <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden flex">
                    <div
                        v-for="p in platformShares"
                        :key="p.platform_id"
                        class="h-full transition-all duration-500"
                        :class="p.code === 'shopee' ? 'bg-orange-500' : 'bg-slate-900'"
                        :style="{ width: `${p.percentage}%` }"
                        :title="`${p.name}: ${p.percentage}%`"
                    ></div>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/80 shadow-2xs">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3 px-4">Platform</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Connected Shops</th>
                            <th class="py-3 px-4 text-right">Orders</th>
                            <th class="py-3 px-4 text-right">Units</th>
                            <th class="py-3 px-4 text-right">Gross Sales</th>
                            <th class="py-3 px-4 text-right">Discounts</th>
                            <th class="py-3 px-4 text-right">Refunds</th>
                            <th class="py-3 px-4 text-right font-bold text-slate-900">Net Sales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="p in platforms" :key="p.platform_id" class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-2">
                                <span
                                    class="w-2.5 h-2.5 rounded-full"
                                    :class="p.code === 'shopee' ? 'bg-orange-500' : 'bg-black'"
                                ></span>
                                {{ p.name }}
                            </td>
                            <td class="py-3 px-4">
                                <StatusBadge :status="p.status" />
                            </td>
                            <td class="py-3 px-4 text-center text-slate-600">
                                <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-700 font-semibold">
                                    {{ p.shops_count }} shops
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-slate-700">{{ formatNumber(p.orders) }}</td>
                            <td class="py-3 px-4 text-right text-slate-700">{{ formatNumber(p.units_sold) }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ formatPeso(p.gross_sales) }}</td>
                            <td class="py-3 px-4 text-right text-amber-600">-{{ formatPeso(p.discounts) }}</td>
                            <td class="py-3 px-4 text-right text-rose-600">-{{ formatPeso(p.refunds) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">{{ formatPeso(p.net_sales) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50/90 font-bold border-t border-slate-200 text-slate-900">
                        <tr>
                            <td class="py-3 px-4" colspan="3">Total Consolidated Performance</td>
                            <td class="py-3 px-4 text-right">{{ formatNumber(kpis.total_orders) }}</td>
                            <td class="py-3 px-4 text-right">{{ formatNumber(kpis.units_sold) }}</td>
                            <td class="py-3 px-4 text-right">{{ formatPeso(kpis.gross_sales) }}</td>
                            <td class="py-3 px-4 text-right text-amber-700">-{{ formatPeso(kpis.discounts) }}</td>
                            <td class="py-3 px-4 text-right text-rose-700">-{{ formatPeso(kpis.refunds) }}</td>
                            <td class="py-3 px-4 text-right text-indigo-700 text-sm">{{ formatPeso(kpis.net_sales) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 6. Shop Summary Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs mb-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Connected Storefront Drilldown</h3>
                    <p class="text-xs text-slate-500">
                        Individual storefront performance grouped dynamically by platform
                    </p>
                </div>
                <Link href="/shops" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 min-h-[36px]">
                    <span>Manage Shops</span>
                    <ChevronRight class="w-3.5 h-3.5" :stroke-width="2" />
                </Link>
            </div>

            <div class="mt-4 space-y-5">
                <div v-for="platform in platforms" :key="platform.platform_id" class="border border-slate-200/80 rounded-xl overflow-hidden shadow-2xs">
                    <div class="bg-slate-50/80 px-4 py-3 flex items-center justify-between border-b border-slate-200/80">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-2.5 h-2.5 rounded-full"
                                :class="platform.code === 'shopee' ? 'bg-orange-500' : 'bg-black'"
                            ></span>
                            <span class="font-bold text-xs text-slate-900">{{ platform.name }}</span>
                            <span class="text-[11px] text-slate-500">({{ platform.shops_count }} active storefronts)</span>
                        </div>
                        <span class="text-xs font-bold text-slate-800">
                            Subtotal: {{ formatPeso(platform.net_sales) }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50/40 text-slate-500 font-semibold border-b border-slate-200/80">
                                <tr>
                                    <th class="py-2.5 px-4">Shop Name</th>
                                    <th class="py-2.5 px-4">Identifier Code</th>
                                    <th class="py-2.5 px-4 text-right">Orders</th>
                                    <th class="py-2.5 px-4 text-right">Units Sold</th>
                                    <th class="py-2.5 px-4 text-right">Gross Sales</th>
                                    <th class="py-2.5 px-4 text-right">Discounts</th>
                                    <th class="py-2.5 px-4 text-right">Refunds</th>
                                    <th class="py-2.5 px-4 text-right font-bold text-slate-900">Net Sales</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr
                                    v-for="shop in shops.filter(s => s.platform_id === platform.platform_id)"
                                    :key="shop.shop_id"
                                    class="hover:bg-slate-50/60 transition-colors"
                                >
                                    <td class="py-2.5 px-4 font-semibold text-slate-900 flex items-center gap-2">
                                        <Store class="w-3.5 h-3.5 text-slate-400" :stroke-width="1.75" />
                                        {{ shop.shop_name }}
                                    </td>
                                    <td class="py-2.5 px-4 font-mono text-[11px] text-slate-500">{{ shop.shop_code }}</td>
                                    <td class="py-2.5 px-4 text-right text-slate-700">{{ formatNumber(shop.orders) }}</td>
                                    <td class="py-2.5 px-4 text-right text-slate-700">{{ formatNumber(shop.units_sold) }}</td>
                                    <td class="py-2.5 px-4 text-right text-slate-600">{{ formatPeso(shop.gross_sales) }}</td>
                                    <td class="py-2.5 px-4 text-right text-amber-600">-{{ formatPeso(shop.discounts) }}</td>
                                    <td class="py-2.5 px-4 text-right text-rose-600">-{{ formatPeso(shop.refunds) }}</td>
                                    <td class="py-2.5 px-4 text-right font-bold text-slate-900">{{ formatPeso(shop.net_sales) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Architectural Presentation Aid for Management -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-1 flex items-center gap-2">
                <Zap class="w-4 h-4 text-indigo-600" :stroke-width="2" />
                Management Value Proposition
            </h3>
            <p class="text-xs text-slate-500 mb-4">
                Comparison of the current operational burden versus the centralized automated architecture.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Manual Current Workflow -->
                <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-200/80 shadow-2xs">
                    <div class="flex items-center gap-2 font-bold text-rose-900 mb-2">
                        <AlertTriangle class="w-4 h-4 text-rose-600" :stroke-width="2" />
                        CURRENT MANUAL WORKFLOW (HIGH FRICTION)
                    </div>
                    <div class="space-y-1.5 text-rose-800">
                        <div class="flex items-center gap-2"><span>1. Seller Portals</span> <span class="text-rose-400">→</span> <span>Login to each platform center individually</span></div>
                        <div class="flex items-center gap-2"><span>2. Manual Scraping</span> <span class="text-rose-400">→</span> <span>Download/copy hourly metrics by hand</span></div>
                        <div class="flex items-center gap-2"><span>3. Spreadsheet Merge</span> <span class="text-rose-400">→</span> <span>Paste numbers into fragile spreadsheets</span></div>
                        <div class="flex items-center gap-2"><span>4. Calculation Risk</span> <span class="text-rose-400">→</span> <span>Manual math prone to formula errors</span></div>
                        <div class="flex items-center gap-2"><span>5. Delayed Dispatch</span> <span class="text-rose-400">→</span> <span>Reports lag 20–45 minutes behind real time</span></div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-rose-200/80 text-[11px] text-rose-700 font-semibold flex items-center gap-1.5">
                        <RotateCcw class="w-3.5 h-3.5" :stroke-width="2" />
                        High personnel cost, prone to human error, delays management decisions.
                    </div>
                </div>

                <!-- Proposed Automated Architecture -->
                <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200/80 shadow-2xs">
                    <div class="flex items-center gap-2 font-bold text-emerald-900 mb-2">
                        <CheckCircle2 class="w-4 h-4 text-emerald-600" :stroke-width="2" />
                        PROPOSED AUTOMATED ENGINE (PRODUCTION-READY)
                    </div>
                    <div class="space-y-1.5 text-emerald-800">
                        <div class="flex items-center gap-2"><span>1. Direct API Sync</span> <span class="text-emerald-400">→</span> <span>Shopee Open Platform & TikTok Shop APIs</span></div>
                        <div class="flex items-center gap-2"><span>2. Cron Workers</span> <span class="text-emerald-400">→</span> <span>Automated background pulls precisely on the hour</span></div>
                        <div class="flex items-center gap-2"><span>3. Central Database</span> <span class="text-emerald-400">→</span> <span>Single source of truth with immutable snapshots</span></div>
                        <div class="flex items-center gap-2"><span>4. Exact Accounting</span> <span class="text-emerald-400">→</span> <span>Deterministic decimal Net Sales calculation</span></div>
                        <div class="flex items-center gap-2"><span>5. Instant Multi-Channel</span> <span class="text-emerald-400">→</span> <span>Auto-generated dashboard, Excel CSVs, webhooks</span></div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-emerald-200/80 text-[11px] text-emerald-700 font-semibold flex items-center gap-1.5">
                        <Sparkles class="w-3.5 h-3.5" :stroke-width="2" />
                        Zero human intervention, sub-second accuracy, scalable to 100+ stores.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
