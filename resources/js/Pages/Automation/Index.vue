<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import {
    Cpu,
    Play,
    Loader2,
    Clock,
    Activity,
    CheckCircle2,
    Calendar,
    FileSpreadsheet,
    Zap,
    History
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface LogItem {
    id: number;
    job: string;
    status: string;
    message: string;
    created_at: string;
}

defineProps<{
    status: string;
    schedule: string;
    cronExpression: string;
    lastRun: string;
    nextRun: string;
    totalReportsCount: number;
    logs: LogItem[];
}>();

const isRunning = ref(false);

function runAutomationNow() {
    if (isRunning.value) return;
    isRunning.value = true;
    const toastId = toast.loading('Executing reporting automation pipeline...');

    router.post('/automation/run', {}, {
        onSuccess: () => {
            toast.success('Automation pipeline executed and snapshot recorded!', { id: toastId });
        },
        onError: () => {
            toast.error('Pipeline execution encountered an issue. See log below.', { id: toastId });
        },
        onFinish: () => {
            isRunning.value = false;
        }
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Automation Engine & Scheduler - Hourly Reporting Automation" />

        <!-- 1. Header Banner -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Automation Engine & Scheduler</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            SCHEDULER ACTIVE
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Automated hourly report generation daemon running via Laravel Console Scheduler
                    </p>
                </div>

                <!-- Manual Trigger Button -->
                <button
                    type="button"
                    @click="runAutomationNow"
                    :disabled="isRunning"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 transition shadow-xs shadow-indigo-600/20 hover:shadow-sm min-h-[40px] cursor-pointer"
                >
                    <Loader2 v-if="isRunning" class="w-4 h-4 animate-spin" :stroke-width="2" />
                    <Play v-else class="w-3.5 h-3.5 fill-current" :stroke-width="2" />
                    <span>{{ isRunning ? 'Executing Pipeline...' : 'Generate Report Now' }}</span>
                </button>
            </div>
        </div>

        <!-- 2. Automation Status Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block flex items-center gap-1.5">
                    <Activity class="w-3.5 h-3.5 text-slate-400" :stroke-width="1.75" />
                    Engine Status
                </span>
                <div class="mt-2 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-lg font-bold text-slate-900 tracking-tight">{{ status }}</span>
                </div>
                <span class="text-[11px] text-slate-400 block mt-1">Background daemon active</span>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-slate-400" :stroke-width="1.75" />
                    Cron Frequency
                </span>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-lg font-bold text-slate-900 tracking-tight">{{ schedule }}</span>
                </div>
                <span class="font-mono text-[11px] text-indigo-600 block mt-1 bg-indigo-50 px-1.5 py-0.5 rounded w-fit">{{ cronExpression }}</span>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block flex items-center gap-1.5">
                    <History class="w-3.5 h-3.5 text-slate-400" :stroke-width="1.75" />
                    Last Executed Snapshot
                </span>
                <div class="mt-2 text-lg font-bold text-slate-900 truncate tracking-tight">
                    {{ lastRun }}
                </div>
                <span class="text-[11px] text-slate-400 block mt-1">Successfully recorded</span>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block flex items-center gap-1.5">
                    <Zap class="w-3.5 h-3.5 text-indigo-500" :stroke-width="1.75" />
                    Next Scheduled Run
                </span>
                <div class="mt-2 text-lg font-bold text-indigo-600 truncate tracking-tight">
                    {{ nextRun }}
                </div>
                <span class="text-[11px] text-slate-400 block mt-1">Automatic cron trigger</span>
            </div>
        </div>

        <!-- 3. Automation Execution Logs Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Execution Audit Log Trail</h3>
                    <p class="text-xs text-slate-500">Live audit log of automated cron and manual pipeline runs</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                    {{ logs.length }} Recent Events
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3 px-4">Event ID</th>
                            <th class="py-3 px-4">Timestamp (PHT)</th>
                            <th class="py-3 px-4">Job Name</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Execution Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono text-slate-500">#{{ log.id }}</td>
                            <td class="py-3 px-4 text-slate-800 font-semibold whitespace-nowrap">
                                {{ new Date(log.created_at).toLocaleString() }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-indigo-600">
                                {{ log.job }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <StatusBadge :status="log.status" />
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                {{ log.message }}
                            </td>
                        </tr>
                        <tr v-if="logs.length === 0">
                            <td colspan="5" class="p-8">
                                <EmptyState
                                    :icon="Cpu"
                                    title="No automation logs recorded yet"
                                    description="Logs are created automatically whenever an hourly report is generated."
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
