<template>
  <div
    class="inline-flex flex-col items-center justify-center gap-3"
    :class="[block ? 'w-full py-12' : '', sizeClass]"
    role="status"
    aria-label="Загрузка"
  >
    <svg
      class="animate-spin text-blue-950"
      :class="iconSizeClass"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
    <span v-if="label" class="text-sm text-gray-500">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  /** Показать как блок по центру с отступами (для экрана/секции) */
  block: { type: Boolean, default: false },
  /** Текст под спиннером */
  label: { type: String, default: '' },
  /** sm | md | lg */
  size: { type: String, default: 'md' },
})

const sizeClass = computed(() => (props.block ? 'min-h-[120px]' : ''))
const iconSizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-5 h-5'
    case 'lg':
      return 'w-10 h-10'
    default:
      return 'w-8 h-8'
  }
})
</script>
