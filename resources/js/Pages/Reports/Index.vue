<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { formatPeso, formatNumber, formatHour } from '@/utils/currency';
import {
    FileSpreadsheet,
    Download,
    Calendar,
    Play,
    Loader2,
    ArrowRight,
    Search,
    RotateCcw,
    FileText,
    ExternalLink
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface ReportItem {
    id: number;
    report_date: string;
    report_hour: number;
    total_orders: number;
    total_units: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
    status: string;
    generated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    reports: {
        data: ReportItem[];
        links: PaginationLink[];
        total: number;
        current_page: number;
        last_page: number;
    };
    filters: {
        date?: string;
    };
}>();

const dateFilter = ref(props.filters.date || '');
const isGenerating = ref(false);

function applyFilter() {
    router.get('/reports', { date: dateFilter.value }, { preserveState: true });
}

function clearFilter() {
    dateFilter.value = '';
    router.get('/reports', {}, { preserveState: true });
}

function generateReportNow() {
    if (isGenerating.value) return;
    isGenerating.value = true;
    const toastId = toast.loading('Triggering automated report snapshot...');
    router.post('/reports/generate', {}, {
        onSuccess: () => {
            toast.success('Report snapshot generated and saved!', { id: toastId });
        },
        onError: () => {
            toast.error('Failed to generate report snapshot.', { id: toastId });
        },
        onFinish: () => {
            isGenerating.value = false;
        }
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Generated Hourly Reports History - Hourly Reporting Automation" />

        <!-- 1. Header Banner -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Hourly Reports Audit History</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            PROTOTYPE — DEMO DATA
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Immutable consolidated snapshots generated automatically on the hour by the reporting engine
                    </p>
                </div>

                <!-- Batch Export & Action Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        :href="`/reports/export/summary.xlsx${dateFilter ? `?date=${dateFilter}` : ''}`"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 transition shadow-xs hover:shadow-sm min-h-[40px] cursor-pointer"
                        title="Download formatted Excel workbook with auto-fitted columns and currency formatting"
                    >
                        <FileSpreadsheet class="w-4 h-4 text-white" :stroke-width="2" />
                        <span>Export Excel (.xlsx)</span>
                    </a>

                    <a
                        :href="`/reports/export/detailed_stores.xlsx${dateFilter ? `?date=${dateFilter}` : ''}`"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 active:bg-slate-100 transition shadow-2xs min-h-[40px] cursor-pointer"
                        title="Download detailed store-by-store audit spreadsheet (.xlsx)"
                    >
                        <FileSpreadsheet class="w-4 h-4 text-emerald-600" :stroke-width="1.75" />
                        <span>Stores (.xlsx)</span>
                    </a>

                    <a
                        :href="`/reports/export/summary.csv${dateFilter ? `?date=${dateFilter}` : ''}`"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition min-h-[40px] cursor-pointer"
                        title="Download raw summary CSV"
                    >
                        <Download class="w-3.5 h-3.5 text-slate-400" :stroke-width="2" />
                        <span>CSV</span>
                    </a>

                    <button
                        type="button"
                        @click="generateReportNow"
                        :disabled="isGenerating"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 transition shadow-xs shadow-indigo-600/20 hover:shadow-sm min-h-[40px] cursor-pointer"
                    >
                        <Loader2 v-if="isGenerating" class="w-4 h-4 animate-spin" :stroke-width="2" />
                        <Play v-else class="w-3.5 h-3.5 fill-current" :stroke-width="2" />
                        <span>{{ isGenerating ? 'Generating...' : 'Generate Report Now' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Search & Filter Bar -->
        <div class="mb-4 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-slate-500 font-medium flex items-center gap-1.5">
                    <Calendar class="w-3.5 h-3.5 text-slate-400" :stroke-width="1.75" />
                    Filter by Date:
                </span>
                <input
                    type="date"
                    v-model="dateFilter"
                    @change="applyFilter"
                    class="border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 min-h-[36px]"
                />
                <button
                    v-if="dateFilter"
                    type="button"
                    @click="clearFilter"
                    class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-800 font-medium ml-1 cursor-pointer min-h-[36px] px-2"
                >
                    <RotateCcw class="w-3 h-3" :stroke-width="2" />
                    <span>Reset</span>
                </button>
            </div>
            <div class="text-slate-500 font-medium">
                Showing <strong class="text-slate-800">{{ reports.data.length }}</strong> of {{ reports.total }} snapshot reports
            </div>
        </div>

        <!-- 3. Reports Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3 px-4">Report ID</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Reporting Hour</th>
                            <th class="py-3 px-4 text-right">Orders</th>
                            <th class="py-3 px-4 text-right">Units</th>
                            <th class="py-3 px-4 text-right">Gross Sales</th>
                            <th class="py-3 px-4 text-right">Discounts</th>
                            <th class="py-3 px-4 text-right">Refunds</th>
                            <th class="py-3 px-4 text-right font-bold text-slate-900">Net Sales</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Generated Timestamp</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr
                            v-for="r in reports.data"
                            :key="r.id"
                            class="hover:bg-slate-50/70 transition-colors"
                        >
                            <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                #{{ r.id }}
                            </td>
                            <td class="py-3 px-4 text-slate-900 font-semibold">{{ r.report_date }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ formatHour(r.report_hour) }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-700">{{ formatNumber(r.total_orders) }}</td>
                            <td class="py-3 px-4 text-right text-slate-700">{{ formatNumber(r.total_units) }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">{{ formatPeso(r.gross_sales) }}</td>
                            <td class="py-3 px-4 text-right text-amber-600">-{{ formatPeso(r.discounts) }}</td>
                            <td class="py-3 px-4 text-right text-rose-600">-{{ formatPeso(r.refunds) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">{{ formatPeso(r.net_sales) }}</td>
                            <td class="py-3 px-4 text-center">
                                <StatusBadge :status="r.status" />
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ new Date(r.generated_at).toLocaleString() }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <Link
                                        :href="`/reports/${r.id}`"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 transition min-h-[32px]"
                                    >
                                        <span>View</span>
                                        <ArrowRight class="w-3 h-3" :stroke-width="2" />
                                    </Link>
                                    <a
                                        :href="`/reports/${r.id}/hourly_report_${r.id}.xlsx`"
                                        title="Export formatted Excel spreadsheet (.xlsx)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 transition min-h-[32px] cursor-pointer"
                                    >
                                        <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-600" :stroke-width="2" />
                                        <span>Excel</span>
                                    </a>
                                    <a
                                        :href="`/reports/${r.id}/hourly_report_${r.id}.csv`"
                                        title="Export raw tabular CSV"
                                        class="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 transition min-h-[32px] cursor-pointer"
                                    >
                                        <Download class="w-3 h-3 text-slate-400" :stroke-width="1.75" />
                                        <span>CSV</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="reports.data.length === 0">
                            <td colspan="12" class="p-8">
                                <EmptyState
                                    :icon="FileSpreadsheet"
                                    title="No generated reports match your criteria"
                                    description="No snapshots found for the selected date. Click below to generate an immediate snapshot."
                                    action-label="Generate Report Now"
                                    @action="generateReportNow"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div v-if="reports.links && reports.links.length > 3" class="px-4 py-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in reports.links"
                        :key="i"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg border text-xs min-h-[36px] inline-flex items-center justify-center"
                        :class="[
                            link.active ? 'bg-indigo-600 text-white border-indigo-600 font-bold shadow-2xs' : 'text-slate-600 border-slate-200 hover:bg-slate-50',
                            !link.url ? 'opacity-40 pointer-events-none' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
