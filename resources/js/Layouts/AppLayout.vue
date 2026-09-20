<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    FileSpreadsheet,
    Store,
    Layers,
    Cpu,
    Play,
    Loader2,
    Clock,
    Sparkles,
    CheckCircle2,
    AlertCircle,
    Menu,
    X,
    TrendingUp
} from 'lucide-vue-next';
import { Toaster, toast } from 'vue-sonner';

defineProps<{
    title?: string;
}>();

const page = usePage();
const isGenerating = ref(false);
const mobileMenuOpen = ref(false);

const navItems = [
    { name: 'Dashboard', href: '/', icon: LayoutDashboard, current: (url: string) => url === '/' },
    { name: 'TikTok Breakdown', href: '/tiktok', icon: TrendingUp, current: (url: string) => url.startsWith('/tiktok'), highlight: true },
    { name: 'Reports', href: '/reports', icon: FileSpreadsheet, current: (url: string) => url.startsWith('/reports') },
    { name: 'Shops', href: '/shops', icon: Store, current: (url: string) => url.startsWith('/shops') },
    { name: 'Platforms', href: '/platforms', icon: Layers, current: (url: string) => url.startsWith('/platforms') },
    { name: 'Automation', href: '/automation', icon: Cpu, current: (url: string) => url.startsWith('/automation') },
];

// Live Philippine Standard Time clock
const phtTime = ref('');
let timer: ReturnType<typeof setInterval> | null = null;

function updateClock() {
    try {
        const now = new Date();
        phtTime.value = new Intl.DateTimeFormat('en-US', {
            timeZone: 'Asia/Manila',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        }).format(now);
    } catch {
        phtTime.value = 'PHT';
    }
}

onMounted(() => {
    updateClock();
    timer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

function triggerReportGeneration() {
    if (isGenerating.value) return;
    isGenerating.value = true;
    const toastId = toast.loading('Generating hourly snapshot report...');

    router.post('/reports/generate', {}, {
        onSuccess: () => {
            toast.success('Hourly snapshot generated successfully!', { id: toastId });
        },
        onError: () => {
            toast.error('Failed to generate report. Check automation logs.', { id: toastId });
        },
        onFinish: () => {
            isGenerating.value = false;
        }
    });
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col text-slate-900 pb-16 md:pb-0">
        <!-- Toast Notification Container -->
        <Toaster position="top-right" richColors :expand="false" />

        <!-- Top Prototype Notice Banner -->
        <div class="bg-amber-500/10 border-b border-amber-500/20 text-amber-900 px-4 py-2 text-xs font-medium">
            <div class="flex items-center justify-between max-w-7xl mx-auto w-full gap-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-200/80 text-amber-950 border border-amber-300">
                        <Sparkles class="w-3 h-3" :stroke-width="2" />
                        Prototype
                    </span>
                    <span class="text-xs">
                        <strong>DEMO DATA MODE:</strong> Figures, shops, and metrics displayed are simulated for multi-platform architectural demonstration.
                    </span>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 text-[11px] text-amber-800/80 bg-amber-100/60 px-2.5 py-0.5 rounded-full border border-amber-200">
                    <Clock class="w-3 h-3 text-amber-700" :stroke-width="2" />
                    <span>Philippine Standard Time (PHT, UTC+8)</span>
                </div>
            </div>
        </div>

        <!-- Main Navigation Header with Glassmorphism -->
        <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-2xs transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Left: Brand + Live Time -->
                    <div class="flex items-center gap-4">
                        <Link href="/" class="flex items-center gap-3 group">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-indigo-600/20 group-hover:bg-indigo-700 transition duration-200 group-hover:scale-105">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-base font-bold text-slate-900 leading-tight flex items-center gap-2">
                                    Hourly Reporting System
                                </h1>
                                <p class="text-[11px] text-slate-500 font-medium">Multi-Platform Central Automation</p>
                            </div>
                        </Link>

                        <!-- Live PHT Clock Chip -->
                        <div class="hidden xl:flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-mono">{{ phtTime }} PHT</span>
                        </div>
                    </div>

                    <!-- Navigation Links (Desktop) -->
                    <nav class="hidden md:flex items-center space-x-1">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition duration-150"
                            :class="[
                                item.current(page.url)
                                    ? (item.highlight ? 'bg-rose-50 text-rose-700 font-semibold shadow-2xs border border-rose-200' : 'bg-indigo-50 text-indigo-700 font-semibold shadow-2xs border border-indigo-100')
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
                            ]"
                        >
                            <component :is="item.icon" class="w-4 h-4" :class="item.highlight ? 'text-rose-600' : ''" :stroke-width="1.75" />
                            <span>{{ item.name }}</span>
                            <span v-if="item.highlight" class="ml-0.5 px-1.5 py-0.2 bg-rose-500 text-white rounded text-[10px] font-black uppercase tracking-wider">
                                New
                            </span>
                        </Link>
                    </nav>

                    <!-- Right Actions -->
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="triggerReportGeneration"
                            :disabled="isGenerating"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 transition shadow-xs shadow-indigo-600/20 hover:shadow-sm cursor-pointer min-h-[40px]"
                            title="Trigger an automated report snapshot immediately"
                        >
                            <Loader2 v-if="isGenerating" class="w-4 h-4 animate-spin" :stroke-width="2" />
                            <Play v-else class="w-3.5 h-3.5 fill-current" :stroke-width="2" />
                            <span>{{ isGenerating ? 'Generating...' : 'Generate Report Now' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages (Inertia Props) -->
        <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div
                v-if="$page.props.flash?.success"
                class="rounded-xl p-3.5 bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-sm flex items-center justify-between shadow-xs"
            >
                <div class="flex items-center gap-2.5">
                    <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0" :stroke-width="2" />
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.error"
                class="rounded-xl p-3.5 bg-rose-50 border border-rose-200/80 text-rose-800 text-sm flex items-center justify-between shadow-xs"
            >
                <div class="flex items-center gap-2.5">
                    <AlertCircle class="w-5 h-5 text-rose-600 shrink-0" :stroke-width="2" />
                    <span>{{ $page.props.flash.error }}</span>
                </div>
            </div>
        </div>

        <!-- Main Content Slot -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
            <slot />
        </main>

        <!-- Mobile Bottom Thumb-Zone Navigation Bar -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-2 py-1 shadow-lg flex items-center justify-around">
            <Link
                v-for="item in navItems"
                :key="item.name"
                :href="item.href"
                class="flex flex-col items-center justify-center py-1.5 px-2 rounded-lg text-[10px] font-medium min-h-[44px] min-w-[56px] transition-colors"
                :class="[
                    item.current(page.url)
                        ? 'text-indigo-600 font-semibold bg-indigo-50/70'
                        : 'text-slate-500 hover:text-slate-900'
                ]"
            >
                <component :is="item.icon" class="w-5 h-5 mb-0.5" :stroke-width="1.75" />
                <span>{{ item.name }}</span>
            </Link>
        </div>

        <!-- Desktop Footer -->
        <footer class="bg-white border-t border-slate-200/80 mt-auto py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-700">Hourly Reporting Automation System</span>
                    <span>•</span>
                    <span class="text-amber-600 font-medium">Prototype MVP</span>
                    <span>•</span>
                    <span class="text-slate-400 font-mono">{{ phtTime }} PHT</span>
                </div>
                <div>
                    Built with Laravel 12 + Vue 3 + Inertia.js + Tailwind CSS. Multi-platform API expandable architecture.
                </div>
            </div>
        </footer>
    </div>
</template>
