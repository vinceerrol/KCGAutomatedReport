<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatPeso, formatNumber, formatHour } from '@/utils/currency';
import {
    ArrowLeft,
    Download,
    Copy,
    Check,
    FileSpreadsheet,
    Sparkles,
    Calculator,
    Send,
    Database,
    Clock,
    Store
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface ReportDetail {
    id: number;
    report_date: string;
    report_hour: number;
    total_orders: number;
    total_units: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
    report_data: {
        meta?: any;
        summary?: any;
        platforms?: Array<{
            platform_id: number;
            name: string;
            code: string;
            orders: number;
            units_sold: number;
            gross_sales: number;
            discounts: number;
            refunds: number;
            net_sales: number;
        }>;
        shops?: Array<{
            shop_id: number;
            shop_name: string;
            shop_code: string;
            platform_id: number;
            platform_name: string;
            orders: number;
            units_sold: number;
            gross_sales: number;
            discounts: number;
            refunds: number;
            net_sales: number;
        }>;
    } | null;
    status: string;
    generated_at: string;
}

const props = defineProps<{
    report: ReportDetail;
}>();

const copied = ref(false);

const formattedDate = computed(() => {
    try {
        const d = new Date(props.report.report_date + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    } catch {
        return props.report.report_date;
    }
});

const formattedHourText = computed(() => {
    return formatHour(props.report.report_hour);
});

// Build raw formatted report string matching the prompt's exact specification
const textReport = computed(() => {
    let text = `----------------------------------------\n`;
    text += `HOURLY SALES REPORT\n\n`;
    text += `${formattedDate.value}\n`;
    text += `${formattedHourText.value}\n\n`;
    text += `PROTOTYPE — DEMO DATA\n\n`;
    text += `TOTAL\n\n`;
    text += `Orders: ${formatNumber(props.report.total_orders)}\n`;
    text += `Units Sold: ${formatNumber(props.report.total_units)}\n`;
    text += `Gross Sales: ${formatPeso(props.report.gross_sales)}\n`;
    text += `Discounts: ${formatPeso(props.report.discounts)}\n`;
    text += `Refunds: ${formatPeso(props.report.refunds)}\n`;
    text += `Net Sales: ${formatPeso(props.report.net_sales)}\n\n`;

    if (props.report.report_data?.platforms) {
        props.report.report_data.platforms.forEach((p) => {
            text += `${p.name.toUpperCase()}\n\n`;
            text += `Orders: ${formatNumber(p.orders)}\n`;
            text += `Units: ${formatNumber(p.units_sold)}\n`;
            text += `Net Sales: ${formatPeso(p.net_sales)}\n\n`;
        });
    }

    text += `----------------------------------------`;
    return text;
});

function copyToClipboard() {
    navigator.clipboard.writeText(textReport.value);
    copied.value = true;
    toast.success('Formatted report copied to clipboard! Ready to paste into Slack/chat.');
    setTimeout(() => {
        copied.value = false;
    }, 2500);
}
</script>

<template>
    <AppLayout>
        <Head :title="`Report #${report.id} (${report.report_date} ${formattedHourText}) - Hourly Reporting Automation`" />

        <!-- Breadcrumb & Top Action Header -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <Link
                href="/reports"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600 transition min-h-[40px]"
            >
                <ArrowLeft class="w-4 h-4" :stroke-width="2" />
                <span>Back to Reports History</span>
            </Link>

            <div class="flex items-center gap-2 flex-wrap">
                <a
                    :href="`/reports/${report.id}/hourly_report_${report.id}.xlsx`"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-xs hover:shadow-sm transition min-h-[40px] cursor-pointer"
                    title="Download formatted Microsoft Excel (.xlsx) with auto-fitted columns and currency styling"
                >
                    <FileSpreadsheet class="w-4 h-4 text-white" :stroke-width="2" />
                    <span>Export Excel (.xlsx)</span>
                </a>

                <a
                    :href="`/reports/${report.id}/hourly_report_${report.id}.csv`"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition shadow-2xs min-h-[40px] cursor-pointer"
                    title="Export raw tabular CSV"
                >
                    <Download class="w-3.5 h-3.5 text-slate-500" :stroke-width="2" />
                    <span>CSV</span>
                </a>

                <button
                    type="button"
                    @click="copyToClipboard"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition shadow-2xs min-h-[40px] cursor-pointer"
                >
                    <Check v-if="copied" class="w-4 h-4 text-emerald-600" :stroke-width="2" />
                    <Copy v-else class="w-4 h-4 text-slate-500" :stroke-width="1.75" />
                    <span>{{ copied ? 'Copied to Clipboard!' : 'Copy Formatted Report' }}</span>
                </button>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Formatted Report Card (Exact Specification Layout) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Formal Report Document Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                    <!-- Header -->
                    <div class="border-b-2 border-slate-900 pb-5 mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-slate-400">AUTOMATED SNAPSHOT #{{ report.id }}</span>
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-1">HOURLY SALES REPORT</h2>
                            <div class="mt-2 text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <span>{{ formattedDate }}</span>
                                <span>•</span>
                                <span class="text-indigo-600 font-bold">{{ formattedHourText }} (PHT)</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:items-end gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-300 shadow-2xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                PROTOTYPE — DEMO DATA
                            </span>
                            <StatusBadge :status="report.status" />
                        </div>
                    </div>

                    <!-- TOTAL CONSOLIDATED SECTION -->
                    <div class="mb-8">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
                            <Calculator class="w-3.5 h-3.5 text-slate-400" :stroke-width="2" />
                            Total Consolidated Metrics
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 bg-slate-50/80 rounded-xl p-5 border border-slate-200/80 shadow-2xs">
                            <div>
                                <span class="text-xs text-slate-500 font-medium block">Total Orders</span>
                                <span class="text-xl font-bold text-slate-900 tracking-tight">{{ formatNumber(report.total_orders) }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 font-medium block">Units Sold</span>
                                <span class="text-xl font-bold text-slate-900 tracking-tight">{{ formatNumber(report.total_units) }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 font-medium block">Gross Sales</span>
                                <span class="text-xl font-bold text-slate-800 tracking-tight">{{ formatPeso(report.gross_sales) }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-amber-600 font-medium block">Discounts</span>
                                <span class="text-base font-bold text-amber-700">-{{ formatPeso(report.discounts) }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-rose-600 font-medium block">Refunds</span>
                                <span class="text-base font-bold text-rose-700">-{{ formatPeso(report.refunds) }}</span>
                            </div>
                            <div class="border-t sm:border-t-0 sm:border-l border-slate-200 sm:pl-4 pt-2 sm:pt-0">
                                <span class="text-xs text-indigo-600 font-bold block flex items-center gap-1">
                                    <Sparkles class="w-3 h-3 text-indigo-500" :stroke-width="2" />
                                    Net Sales
                                </span>
                                <span class="text-2xl font-black text-indigo-600 tracking-tight">{{ formatPeso(report.net_sales) }}</span>
                            </div>
                        </div>

                        <!-- Calculation Equation Callout -->
                        <div class="mt-2.5 px-3.5 py-2 rounded-lg bg-indigo-50/60 border border-indigo-100 text-[11px] text-indigo-800 flex items-center justify-between flex-wrap gap-2">
                            <span class="font-medium">Calculation Rule:</span>
                            <span class="font-mono font-semibold">
                                {{ formatPeso(report.gross_sales) }} (Gross) − {{ formatPeso(report.discounts) }} (Discounts) − {{ formatPeso(report.refunds) }} (Refunds) = <strong class="text-indigo-900">{{ formatPeso(report.net_sales) }}</strong>
                            </span>
                        </div>
                    </div>

                    <!-- PLATFORM BREAKDOWNS -->
                    <div v-if="report.report_data?.platforms" class="space-y-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">PLATFORM BREAKDOWN</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                v-for="platform in report.report_data.platforms"
                                :key="platform.platform_id"
                                class="border border-slate-200/80 rounded-xl p-4 bg-white shadow-2xs hover:shadow-xs transition"
                            >
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                                    <span class="font-extrabold text-sm uppercase tracking-wide text-slate-900 flex items-center gap-2">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full"
                                            :class="platform.code === 'shopee' ? 'bg-orange-500' : 'bg-black'"
                                        ></span>
                                        {{ platform.name }}
                                    </span>
                                    <span class="text-xs font-bold text-indigo-600">
                                        {{ formatPeso(platform.net_sales) }}
                                    </span>
                                </div>

                                <div class="space-y-1.5 text-xs text-slate-600">
                                    <div class="flex justify-between">
                                        <span>Orders:</span>
                                        <span class="font-semibold text-slate-800">{{ formatNumber(platform.orders) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Units:</span>
                                        <span class="font-semibold text-slate-800">{{ formatNumber(platform.units_sold) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Gross Sales:</span>
                                        <span class="font-semibold text-slate-700">{{ formatPeso(platform.gross_sales) }}</span>
                                    </div>
                                    <div class="flex justify-between text-amber-600">
                                        <span>Discounts:</span>
                                        <span>-{{ formatPeso(platform.discounts) }}</span>
                                    </div>
                                    <div class="flex justify-between text-rose-600">
                                        <span>Refunds:</span>
                                        <span>-{{ formatPeso(platform.refunds) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t border-slate-100 font-bold text-slate-900">
                                        <span>Net Sales:</span>
                                        <span class="text-indigo-600">{{ formatPeso(platform.net_sales) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SHOP LEVEL BREAKDOWN -->
                    <div v-if="report.report_data?.shops" class="mt-8">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3">SHOP-LEVEL DETAILS</h3>
                        <div class="overflow-x-auto rounded-xl border border-slate-200/80 shadow-2xs">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80">
                                    <tr>
                                        <th class="py-2.5 px-3">Shop</th>
                                        <th class="py-2.5 px-3">Platform</th>
                                        <th class="py-2.5 px-3 text-right">Orders</th>
                                        <th class="py-2.5 px-3 text-right">Units</th>
                                        <th class="py-2.5 px-3 text-right">Gross</th>
                                        <th class="py-2.5 px-3 text-right">Discounts</th>
                                        <th class="py-2.5 px-3 text-right">Refunds</th>
                                        <th class="py-2.5 px-3 text-right font-bold text-slate-900">Net Sales</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <tr v-for="shop in report.report_data.shops" :key="shop.shop_id" class="hover:bg-slate-50 transition-colors">
                                        <td class="py-2.5 px-3 font-semibold text-slate-800 flex items-center gap-1.5">
                                            <Store class="w-3 h-3 text-slate-400" :stroke-width="1.75" />
                                            {{ shop.shop_name }}
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-500">{{ shop.platform_name }}</td>
                                        <td class="py-2.5 px-3 text-right text-slate-700">{{ formatNumber(shop.orders) }}</td>
                                        <td class="py-2.5 px-3 text-right text-slate-700">{{ formatNumber(shop.units_sold) }}</td>
                                        <td class="py-2.5 px-3 text-right text-slate-600">{{ formatPeso(shop.gross_sales) }}</td>
                                        <td class="py-2.5 px-3 text-right text-amber-600">-{{ formatPeso(shop.discounts) }}</td>
                                        <td class="py-2.5 px-3 text-right text-rose-600">-{{ formatPeso(shop.refunds) }}</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ formatPeso(shop.net_sales) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Dispatch Preview & Metadata -->
            <div class="space-y-6">
                <!-- Message / Dispatch Preview Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
                                <Send class="w-3.5 h-3.5 text-indigo-600" :stroke-width="2" />
                                Notification Dispatch Preview
                            </h3>
                            <p class="text-[11px] text-slate-500">How this report is dispatched to Slack/Teams/Email</p>
                        </div>
                        <button
                            type="button"
                            @click="copyToClipboard"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 cursor-pointer min-h-[36px] px-2"
                        >
                            <Check v-if="copied" class="w-3 h-3 text-emerald-600" :stroke-width="2" />
                            <Copy v-else class="w-3 h-3" :stroke-width="1.75" />
                            <span>{{ copied ? 'Copied' : 'Copy' }}</span>
                        </button>
                    </div>

                    <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl text-[11px] font-mono overflow-x-auto whitespace-pre leading-relaxed border border-slate-800 shadow-inner">{{ textReport }}</pre>

                    <div class="mt-3 text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                        💡 In production, this consolidated block can be triggered automatically to management channels on the hour.
                    </div>
                </div>

                <!-- Snapshot Meta Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs text-xs space-y-2.5">
                    <h3 class="font-bold text-slate-900 uppercase tracking-wider text-xs flex items-center gap-1.5">
                        <Database class="w-3.5 h-3.5 text-slate-500" :stroke-width="1.75" />
                        Snapshot Provenance & Audit
                    </h3>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Report Snapshot ID:</span>
                        <span class="font-mono font-bold text-slate-800">#{{ report.id }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Generated At (PHT):</span>
                        <span class="font-semibold text-slate-800">{{ new Date(report.generated_at).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Calculation Engine:</span>
                        <span class="text-slate-700 font-mono text-[11px]">ReportGeneratorService</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-slate-500">Storage Destination:</span>
                        <span class="text-slate-700 font-mono text-[11px]">generated_reports (MySQL)</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
