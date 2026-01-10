<template>
    <button
        class="flex items-center gap-3 w-full p-3 rounded-lg font-medium transition-colors duration-200 cursor-pointer"
        :class="[
      active
        ? 'bg-white text-blue-950'
        : 'text-gray-100 hover:bg-white hover:text-blue-950'
    ]"
        :title="collapsed ? label : ''"
        @click="$emit('click')"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon"/>
        </svg>
        <span v-if="!collapsed">{{ label }}</span>
    </button>
</template>

<script setup>
defineProps({
    icon: String,
    label: String,
    active: Boolean,
    collapsed: Boolean
})
</script>
