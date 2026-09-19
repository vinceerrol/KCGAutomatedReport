<script setup lang="ts">
import type { Component } from 'vue';
import { Inbox } from 'lucide-vue-next';

withDefaults(defineProps<{
    title: string;
    description?: string;
    icon?: Component;
    actionLabel?: string;
}>(), {
    description: 'No data available to display at this moment.',
    icon: Inbox
});

const emit = defineEmits<{
    (e: 'action'): void;
}>();
</script>

<template>
    <div class="text-center py-12 px-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/50">
        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 shadow-2xs">
            <component :is="icon" class="w-6 h-6" :stroke-width="1.75" />
        </div>
        <h3 class="text-sm font-semibold text-slate-900 mb-1">{{ title }}</h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">{{ description }}</p>
        <button
            v-if="actionLabel"
            type="button"
            @click="emit('action')"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 active:bg-indigo-200 transition cursor-pointer min-h-[44px]"
        >
            <slot name="action-icon" />
            <span>{{ actionLabel }}</span>
        </button>
    </div>
</template>
