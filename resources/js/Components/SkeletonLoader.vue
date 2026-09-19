<script setup lang="ts">
withDefaults(defineProps<{
    type?: 'card' | 'table' | 'chart' | 'text';
    count?: number;
    rows?: number;
}>(), {
    type: 'card',
    count: 1,
    rows: 4
});
</script>

<template>
    <div v-if="type === 'card'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div
            v-for="i in count"
            :key="i"
            class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs animate-pulse flex flex-col justify-between h-32"
        >
            <div class="flex items-center justify-between">
                <div class="h-3.5 bg-slate-200 rounded w-24"></div>
                <div class="w-8 h-8 rounded-lg bg-slate-200"></div>
            </div>
            <div>
                <div class="h-7 bg-slate-200 rounded w-32 mb-2"></div>
                <div class="h-3 bg-slate-200 rounded w-20"></div>
            </div>
        </div>
    </div>

    <div v-else-if="type === 'chart'" class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs animate-pulse">
        <div class="flex justify-between items-center mb-6">
            <div class="h-5 bg-slate-200 rounded w-48"></div>
            <div class="h-4 bg-slate-200 rounded w-28"></div>
        </div>
        <div class="h-72 bg-slate-100 rounded-lg flex items-end gap-3 p-4">
            <div
                v-for="i in 12"
                :key="i"
                class="flex-1 bg-slate-200 rounded-t"
                :style="{ height: `${20 + (i * 7) % 70}%` }"
            ></div>
        </div>
    </div>

    <div v-else-if="type === 'table'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden animate-pulse">
        <div class="px-6 py-4 border-b border-slate-200/80 flex justify-between items-center">
            <div class="h-4 bg-slate-200 rounded w-36"></div>
            <div class="h-4 bg-slate-200 rounded w-20"></div>
        </div>
        <div class="p-6 space-y-4">
            <div v-for="r in rows" :key="r" class="flex items-center justify-between gap-4">
                <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                <div class="h-4 bg-slate-200 rounded w-1/5"></div>
                <div class="h-4 bg-slate-200 rounded w-1/6"></div>
                <div class="h-4 bg-slate-200 rounded w-16"></div>
            </div>
        </div>
    </div>

    <div v-else class="space-y-2.5 animate-pulse">
        <div v-for="i in count" :key="i" class="h-4 bg-slate-200 rounded w-full"></div>
    </div>
</template>
