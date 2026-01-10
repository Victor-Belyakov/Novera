<template>
    <aside
        :class="[
      'h-full flex flex-col transition-all duration-300 border-r',
      collapsed ? 'w-16 border-blue-950 bg-blue-950' : 'w-56 border-blue-950 bg-blue-950'
    ]"
    >

        <!-- Меню -->
        <nav class="flex-1 flex flex-col p-2 gap-2 overflow-y-auto">
            <MenuItem
                v-for="item in menuItems"
                :key="item.name"
                :icon="item.icon"
                :label="item.label"
                :active="activeMenu === item.name"
                :collapsed="collapsed"
                @click="$emit('updateActive', item.name)"
            />
        </nav>
    </aside>
</template>

<script setup>
import MenuItem from './MenuItem.vue'

defineProps({
    activeMenu: String,
    collapsed: Boolean,
    menuItems: Array
})

defineEmits(['updateActive', 'toggleCollapse'])
</script>
