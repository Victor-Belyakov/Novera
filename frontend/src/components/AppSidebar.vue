<template>
  <aside
    :class="[
      'h-full flex flex-col transition-all duration-300 border-r',
      isCollapsed ? 'w-16 border-blue-950 bg-blue-950' : 'w-56 border-blue-950 bg-blue-950',
    ]"
  >
    <nav class="flex-1 flex flex-col p-2 gap-2 overflow-y-auto">
      <template v-for="item in menuItems" :key="item.name">
        <AppMenuItem
          v-if="!item.children"
          :icon="item.icon"
          :label="item.label"
          :is-active="isItemActive(item)"
          :collapsed="isCollapsed"
          @click="$emit('updateActive', item.name)"
        />
        <div v-else class="flex flex-col gap-0.5">
          <AppMenuItem
            :icon="item.icon"
            :label="item.label"
            :is-active="isItemActive(item) && !hasActiveChild(item)"
            :collapsed="isCollapsed"
            @click="toggleParentExpand(item.name)"
          />
          <template v-if="!isCollapsed && isParentExpanded(item)">
            <button
              v-for="child in item.children"
              :key="child.name"
              class="flex items-center gap-3 w-full py-2 pl-11 pr-3 rounded-lg text-sm font-medium transition-colors cursor-pointer"
              :class="isChildItemActive(child) ? 'sidebar-item--active bg-white text-blue-950 shadow-sm ring-1 ring-blue-950/10' : 'text-white/90 hover:bg-white hover:text-blue-950'"
              @click="$emit('updateActive', child.name)"
            >
              <component :is="getIconComponent(child.icon || 'menu')" class="w-5 h-5 shrink-0 opacity-90" />
              {{ child.label }}
            </button>
          </template>
        </div>
      </template>
    </nav>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { getIconComponent } from '@/utils/icons'
import AppMenuItem from './AppMenuItem.vue'

const route = useRoute()

const props = defineProps({
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

const expandedParents = ref(new Set())

const currentPath = computed(() => route.path)

function norm(p) {
  return (p || '').replace(/\/$/, '') || '/'
}

function isItemActive(item) {
  return item.path != null && norm(currentPath.value) === norm(item.path)
}

function hasActiveChild(item) {
  return item.children?.some((c) => c.path && norm(currentPath.value) === norm(c.path)) ?? false
}

function isChildItemActive(child) {
  return child.path != null && norm(currentPath.value) === norm(child.path)
}

function isParentExpanded(item) {
  const pathMatches = norm(currentPath.value) === norm(item.path) || hasActiveChild(item)
  return expandedParents.value.has(item.name) || pathMatches
}

function toggleParentExpand(parentName) {
  const next = new Set(expandedParents.value)
  if (next.has(parentName)) next.delete(parentName)
  else next.add(parentName)
  expandedParents.value = next
}
</script>



