<template>
  <aside
    :class="[
      'h-full flex flex-col transition-all duration-300 border-r',
      isCollapsed ? 'w-16 border-blue-950 bg-blue-950' : 'w-56 border-blue-950 bg-blue-950',
    ]"
  >
    <nav class="flex-1 flex flex-col p-2 gap-2 overflow-y-auto">
      <AppMenuItem
        v-for="item in menuItems"
        :key="item.name"
        :icon="item.icon"
        :label="item.label"
        :active="activeMenu === item.name"
        :collapsed="isCollapsed"
        @click="$emit('updateActive', item.name)"
      />
    </nav>
  </aside>
</template>

<script setup>
import AppMenuItem from './AppMenuItem.vue'

defineProps({
  activeMenu: {
    type: String,
    required: true,
  },
  isCollapsed: {
    type: Boolean,
    default: false,
  },
  menuItems: {
    type: Array,
    required: true,
  },
})

defineEmits(['updateActive', 'toggleCollapse'])
</script>



