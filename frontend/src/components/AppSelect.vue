<template>
  <div ref="rootRef" class="relative w-full">
    <button
      type="button"
      class="app-input w-full text-left flex items-center justify-between cursor-pointer"
      :class="{ 'opacity-60': disabled }"
      :disabled="disabled"
      @click="open = !open"
    >
      <span class="truncate">{{ selectedLabel || placeholder }}</span>
      <span
        class="shrink-0 ml-2 transition-transform"
        :class="open ? 'rotate-180' : ''"
        aria-hidden
      >
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </span>
    </button>
    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <ul
        v-show="open"
        class="absolute z-50 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg focus:outline-none"
      >
        <li
          v-for="opt in options"
          :key="opt.value === undefined ? opt.label : opt.value"
          class="px-4 py-2.5 cursor-pointer text-gray-900 hover:bg-gray-100 first:rounded-t-lg last:rounded-b-lg"
          :class="{ 'bg-blue-50 text-blue-950': isSelected(opt) }"
          @click="choose(opt)"
        >
          {{ opt.label }}
        </li>
      </ul>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: null },
  options: { type: Array, required: true },
  placeholder: { type: String, default: 'Выберите' },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const rootRef = ref(null)
const open = ref(false)

const selectedLabel = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') return ''
  const opt = props.options.find((o) => o.value === props.modelValue)
  return opt ? opt.label : ''
})

function isSelected(opt) {
  return opt.value === props.modelValue
}

function choose(opt) {
  emit('update:modelValue', opt.value)
  open.value = false
}

function onDocClick(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) open.value = false
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
})
onUnmounted(() => {
  document.removeEventListener('click', onDocClick)
})
</script>
