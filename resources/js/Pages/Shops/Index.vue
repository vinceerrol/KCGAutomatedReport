<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { formatPeso, formatNumber } from '@/utils/currency';
import {
    Store,
    Search,
    ShoppingBag,
    Package,
    Sparkles,
    Layers,
    Clock,
    Tag
} from 'lucide-vue-next';

interface ShopSummaryItem {
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

interface PlatformItem {
    id: number;
    name: string;
    code: string;
    status: string;
    shops_count: number;
}

const props = defineProps<{
    shops: ShopSummaryItem[];
    platforms: PlatformItem[];
    date: string;
}>();

const searchQuery = ref('');

const filteredShops = computed(() => {
    if (!searchQuery.value.trim()) return props.shops;
    const q = searchQuery.value.toLowerCase().trim();
    return props.shops.filter(s =>
        s.shop_name.toLowerCase().includes(q) ||
        s.shop_code.toLowerCase().includes(q) ||
        s.platform_name.toLowerCase().includes(q)
    );
});

function calculateAOV(netSales: number, orders: number): string {
    if (!orders || orders === 0) return '₱0.00';
    return formatPeso(netSales / orders);
}
</script>

<template>
    <AppLayout>
        <Head title="Connected Storefronts - Hourly Reporting Automation" />

        <!-- 1. Header Banner -->
        <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Connected E-Commerce Storefronts</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            PROTOTYPE — DEMO DATA
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Relational storefront configuration and individual financial snapshots for {{ date }}
                    </p>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" :stroke-width="1.75" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search storefront..."
                        class="w-full pl-9 pr-3.5 py-2 rounded-xl text-xs border border-slate-300 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white min-h-[40px]"
                    />
                </div>
            </div>
        </div>

        <!-- 2. Shops Grouped by Platform -->
        <div class="space-y-6">
            <div
                v-for="platform in platforms"
                :key="platform.id"
                class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-2">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-3.5 h-3.5 rounded-full ring-4 ring-slate-100"
                            :class="platform.code === 'shopee' ? 'bg-orange-500' : 'bg-black'"
                        ></span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ platform.name }} Storefronts</h3>
                            <p class="text-xs text-slate-500">Platform Connector #{{ platform.id }} • Status: {{ platform.status }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200/60 self-start sm:self-auto">
                        {{ filteredShops.filter(s => s.platform_id === platform.id).length }} of {{ platform.shops_count }} Shops Connected
                    </span>
                </div>

                <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200/80 shadow-2xs">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3 px-4">Storefront Name</th>
                                <th class="py-3 px-4">Identifier Code</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Today's Orders</th>
                                <th class="py-3 px-4 text-right">Units Sold</th>
                                <th class="py-3 px-4 text-right">Gross Sales</th>
                                <th class="py-3 px-4 text-right">Discounts</th>
                                <th class="py-3 px-4 text-right">Refunds</th>
                                <th class="py-3 px-4 text-right font-bold text-slate-900">Net Sales</th>
                                <th class="py-3 px-4 text-right text-slate-600">AOV</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr
                                v-for="shop in filteredShops.filter(s => s.platform_id === platform.id)"
                                :key="shop.shop_id"
                                class="hover:bg-slate-50/70 transition-colors"
                            >
                                <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-2">
                                    <Store class="w-4 h-4 text-slate-400" :stroke-width="1.75" />
                                    {{ shop.shop_name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                    {{ shop.shop_code }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <StatusBadge :status="shop.status" />
                                </td>
                                <td class="py-3 px-4 text-right text-slate-700">{{ formatNumber(shop.orders) }}</td>
                                <td class="py-3 px-4 text-right text-slate-700">{{ formatNumber(shop.units_sold) }}</td>
                                <td class="py-3 px-4 text-right text-slate-600">{{ formatPeso(shop.gross_sales) }}</td>
                                <td class="py-3 px-4 text-right text-amber-600">-{{ formatPeso(shop.discounts) }}</td>
                                <td class="py-3 px-4 text-right text-rose-600">-{{ formatPeso(shop.refunds) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">{{ formatPeso(shop.net_sales) }}</td>
                                <td class="py-3 px-4 text-right text-indigo-600 font-semibold">{{ calculateAOV(shop.net_sales, shop.orders) }}</td>
                            </tr>
                            <tr v-if="filteredShops.filter(s => s.platform_id === platform.id).length === 0">
                                <td colspan="10" class="py-6 text-center text-slate-400">
                                    No storefronts match "{{ searchQuery }}" for this platform.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
