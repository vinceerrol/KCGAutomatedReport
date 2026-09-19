<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatPeso, formatNumber } from '@/utils/currency';
import {
    Layers,
    Store,
    ShoppingBag,
    Package,
    Sparkles,
    CheckCircle2,
    Zap,
    ShieldCheck,
    Clock,
    Activity
} from 'lucide-vue-next';

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

defineProps<{
    platforms: PlatformItem[];
    date: string;
}>();
</script>

<template>
    <AppLayout>
        <Head title="E-Commerce Platforms & Connectors - Hourly Reporting Automation" />

        <!-- 1. Header Banner -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Marketplace Platform Connectors</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            PROTOTYPE — DEMO DATA
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Central multi-channel ingestion pipelines for Shopee Open Platform and TikTok Shop APIs
                    </p>
                </div>

                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-slate-400" :stroke-width="1.75" />
                    <span>Metrics calculated for <strong class="text-slate-800">{{ date }}</strong></span>
                </div>
            </div>
        </div>

        <!-- 2. Platform Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div
                v-for="p in platforms"
                :key="p.platform_id"
                class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs relative overflow-hidden flex flex-col justify-between transition-all duration-200 hover:shadow-md"
            >
                <div
                    class="absolute top-0 left-0 right-0 h-1.5"
                    :class="p.code === 'shopee' ? 'bg-orange-500' : 'bg-slate-900'"
                ></div>

                <div>
                    <!-- Top Identity Row -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3.5">
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-white text-lg shadow-xs"
                                :class="p.code === 'shopee' ? 'bg-orange-500 shadow-orange-500/20' : 'bg-slate-900 shadow-slate-900/20'"
                            >
                                {{ p.code === 'shopee' ? 'S' : 'TT' }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ p.name }}</h3>
                                <p class="text-xs text-slate-500 font-mono">Connector Code: {{ p.code }}</p>
                            </div>
                        </div>
                        <StatusBadge :status="p.status" />
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 gap-3 mt-6 pt-5 border-t border-slate-100 text-xs">
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/60">
                            <span class="text-slate-500 block text-[11px] font-medium flex items-center gap-1">
                                <Store class="w-3 h-3 text-slate-400" :stroke-width="1.75" />
                                Connected Stores
                            </span>
                            <span class="text-lg font-bold text-slate-900 tracking-tight mt-0.5 block">{{ p.shops_count }} Storefronts</span>
                        </div>
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/60">
                            <span class="text-slate-500 block text-[11px] font-medium flex items-center gap-1">
                                <Sparkles class="w-3 h-3 text-indigo-500" :stroke-width="1.75" />
                                Today's Net Sales
                            </span>
                            <span class="text-lg font-bold text-indigo-600 tracking-tight mt-0.5 block">{{ formatPeso(p.net_sales) }}</span>
                        </div>
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/60">
                            <span class="text-slate-500 block text-[11px] font-medium flex items-center gap-1">
                                <ShoppingBag class="w-3 h-3 text-blue-500" :stroke-width="1.75" />
                                Total Orders
                            </span>
                            <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ formatNumber(p.orders) }}</span>
                        </div>
                        <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/60">
                            <span class="text-slate-500 block text-[11px] font-medium flex items-center gap-1">
                                <Package class="w-3 h-3 text-cyan-500" :stroke-width="1.75" />
                                Units Sold
                            </span>
                            <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ formatNumber(p.units_sold) }}</span>
                        </div>
                    </div>

                    <!-- Readiness Capabilities -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-4 text-[11px] text-slate-600 flex-wrap">
                        <span class="flex items-center gap-1 text-emerald-700 font-medium">
                            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" :stroke-width="2" />
                            Hourly Batch Pulls
                        </span>
                        <span class="flex items-center gap-1 text-emerald-700 font-medium">
                            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" :stroke-width="2" />
                            Voucher Reconciliation
                        </span>
                        <span class="flex items-center gap-1 text-emerald-700 font-medium">
                            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" :stroke-width="2" />
                            Refund Tracking
                        </span>
                    </div>
                </div>

                <!-- Dedicated TikTok Hourly Breakdown Feature Action -->
                <div v-if="p.code === 'tiktok'" class="mt-4 pt-3 border-t border-slate-100">
                    <Link
                        href="/tiktok"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white rounded-xl text-xs font-bold shadow-xs shadow-rose-500/20 transition active:scale-[0.98]"
                    >
                        <Zap class="w-3.5 h-3.5 fill-current" />
                        <span>Open TikTok Hourly GMV Breakdown</span>
                    </Link>
                </div>

                <!-- Integration Status Callout -->
                <div class="mt-4 p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/60 text-xs">
                    <div class="flex items-center justify-between text-slate-800 font-semibold mb-1">
                        <span class="flex items-center gap-1.5">
                            <Activity class="w-3.5 h-3.5 text-indigo-600" :stroke-width="2" />
                            API Connector State:
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Prototype Mode (Demo Data)
                        </span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">
                        Architecture uses <code class="text-indigo-600 font-mono bg-indigo-50 px-1 py-0.5 rounded">PlatformDataServiceInterface</code>. Swapping to production APIs only requires supplying API credentials.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
