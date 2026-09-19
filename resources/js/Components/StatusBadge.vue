<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    status: string;
    size?: 'sm' | 'md';
    pulse?: boolean;
}>(), {
    size: 'sm',
    pulse: false
});

const config = computed(() => {
    const s = props.status?.toLowerCase().trim() || '';
    if (s === 'active' || s === 'completed' || s === 'success' || s === 'done' || s === 'healthy') {
        return {
            classes: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            dot: 'bg-emerald-500',
            pulse: props.pulse || s === 'active'
        };
    }
    if (s === 'failed' || s === 'inactive' || s === 'error' || s === 'overdue') {
        return {
            classes: 'bg-rose-50 text-rose-700 border-rose-200/80 font-semibold',
            dot: 'bg-rose-500',
            pulse: false
        };
    }
    if (s === 'running' || s === 'in progress' || s === 'processing') {
        return {
            classes: 'bg-blue-50 text-blue-700 border-blue-200/80 font-medium',
            dot: 'bg-blue-500',
            pulse: true
        };
    }
    if (s === 'scheduled' || s === 'pending') {
        return {
            classes: 'bg-amber-50 text-amber-700 border-amber-200/80 font-medium',
            dot: 'bg-amber-500',
            pulse: props.pulse
        };
    }
    return {
        classes: 'bg-slate-100 text-slate-700 border-slate-200/80 font-medium',
        dot: 'bg-slate-400',
        pulse: false
    };
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full border shadow-2xs transition-colors"
        :class="[
            config.classes,
            size === 'sm' ? 'px-2.5 py-0.5 text-xs' : 'px-3 py-1 text-xs'
        ]"
    >
        <span class="relative flex h-2 w-2 items-center justify-center">
            <span
                v-if="config.pulse"
                class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                :class="config.dot"
            ></span>
            <span
                class="relative inline-flex h-1.5 w-1.5 rounded-full"
                :class="config.dot"
            ></span>
        </span>
        <span class="tracking-wide uppercase">{{ status }}</span>
    </span>
</template>
