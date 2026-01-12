<template>
  <button
    class="flex items-center gap-3 w-full p-3 rounded-lg font-medium transition-colors duration-200 cursor-pointer"
    :class="[
      isActive
        ? 'bg-white text-blue-950'
        : 'text-gray-100 hover:bg-white hover:text-blue-950',
    ]"
    :title="isCollapsed ? label : ''"
    @click="$emit('click')"
  >
    <component :is="iconComponent" />
    <span v-if="!isCollapsed">{{ label }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import { getIconComponent } from '@/utils/icons'

const props = defineProps({
  icon: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  isCollapsed: {
    type: Boolean,
    default: false,
  },
})

const iconComponent = computed(() => getIconComponent(props.icon))

defineEmits(['click'])
</script>



