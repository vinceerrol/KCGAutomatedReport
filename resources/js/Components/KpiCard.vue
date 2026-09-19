<script setup lang="ts">
import { computed } from 'vue';
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    title: string;
    value: string;
    subtitle?: string;
    trend?: string;
    trendType?: 'up' | 'down' | 'neutral';
    variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger';
    loading?: boolean;
}>(), {
    variant: 'default',
    trendType: 'neutral',
    loading: false
});

const variantStyles = computed(() => {
    switch (props.variant) {
        case 'primary':
            return {
                iconBg: 'bg-indigo-50 text-indigo-600 border-indigo-100',
                borderAccent: 'group-hover:border-indigo-300'
            };
        case 'success':
            return {
                iconBg: 'bg-emerald-50 text-emerald-600 border-emerald-100',
                borderAccent: 'group-hover:border-emerald-300'
            };
        case 'warning':
            return {
                iconBg: 'bg-amber-50 text-amber-600 border-amber-100',
                borderAccent: 'group-hover:border-amber-300'
            };
        case 'danger':
            return {
                iconBg: 'bg-rose-50 text-rose-600 border-rose-100',
                borderAccent: 'group-hover:border-rose-300'
            };
        default:
            return {
                iconBg: 'bg-slate-50 text-slate-600 border-slate-100',
                borderAccent: 'group-hover:border-slate-300'
            };
    }
});
</script>

<template>
    <div
        v-if="loading"
        class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs animate-pulse"
    >
        <div class="flex items-center justify-between">
            <div class="h-3.5 bg-slate-200 rounded w-20"></div>
            <div class="w-8 h-8 rounded-lg bg-slate-200"></div>
        </div>
        <div class="mt-4">
            <div class="h-7 bg-slate-200 rounded w-28 mb-2"></div>
            <div class="h-3 bg-slate-200 rounded w-16"></div>
        </div>
    </div>

    <div
        v-else
        class="group relative bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
        :class="variantStyles.borderAccent"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ title }}</span>
            <div
                class="w-9 h-9 rounded-xl flex items-center justify-center border shadow-2xs transition-colors"
                :class="variantStyles.iconBg"
            >
                <slot name="icon">
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                </slot>
            </div>
        </div>

        <div class="mt-3">
            <div class="text-2xl font-bold tracking-tight text-slate-900 truncate">
                {{ value }}
            </div>
            <div v-if="subtitle || trend" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                <span
                    v-if="trend"
                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-semibold"
                    :class="{
                        'bg-emerald-50 text-emerald-700': trendType === 'up',
                        'bg-rose-50 text-rose-700': trendType === 'down',
                        'bg-slate-100 text-slate-600': trendType === 'neutral'
                    }"
                >
                    <TrendingUp v-if="trendType === 'up'" class="w-3 h-3" :stroke-width="2" />
                    <TrendingDown v-else-if="trendType === 'down'" class="w-3 h-3" :stroke-width="2" />
                    <Minus v-else class="w-3 h-3" :stroke-width="2" />
                    {{ trend }}
                </span>
                <span v-if="subtitle" class="truncate">{{ subtitle }}</span>
            </div>
        </div>
    </div>
</template>
