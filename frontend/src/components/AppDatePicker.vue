<template>
  <div ref="rootRef" class="relative" :class="inline ? 'w-auto max-w-[10.5rem] shrink-0' : 'w-full'">
    <button
      type="button"
      class="text-left flex items-center justify-between cursor-pointer border rounded-lg transition-colors app-input"
      :class="[
        inline ? 'app-input-date-inline h-[2.25rem] w-full min-w-0 text-sm' : 'w-full',
        showTime ? 'min-w-[11rem]' : '',
        { 'opacity-60': disabled }
      ]"
      :disabled="disabled"
      :title="placeholder"
      @click="open = !open"
    >
      <span class="truncate font-variant-numeric tabular-nums">{{ displayValue || placeholder }}</span>
      <span class="shrink-0 ml-2 text-gray-500" aria-hidden>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
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
      <div
        v-show="open"
        class="absolute z-50 mt-1 rounded-lg border border-gray-200 bg-white p-4 shadow-lg min-w-[18rem]"
      >
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm font-semibold text-gray-900">{{ currentMonthYear }}</span>
          <div class="flex items-center gap-1">
            <button
              type="button"
              @click.stop="previousMonth"
              class="p-1.5 rounded-md hover:bg-gray-100 transition-colors text-gray-600"
              title="Предыдущий месяц"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
              </svg>
            </button>
            <button
              type="button"
              @click.stop="nextMonth"
              class="p-1.5 rounded-md hover:bg-gray-100 transition-colors text-gray-600"
              title="Следующий месяц"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
              </svg>
            </button>
            <button
              type="button"
              @click.stop="goToToday"
              class="ml-1 px-2 py-1 text-xs rounded-md bg-blue-950 text-white hover:bg-blue-900 transition-colors"
              title="Сегодня"
            >
              Сегодня
            </button>
          </div>
        </div>
        <div class="grid grid-cols-7 gap-1">
          <div
            v-for="day in weekDays"
            :key="day"
            class="text-center text-xs font-medium text-gray-500 py-1"
          >
            {{ day }}
          </div>
          <div v-for="n in firstDayOfMonth" :key="`empty-${n}`" />
          <button
            v-for="day in daysInMonth"
            :key="day"
            type="button"
            @click="selectDate(day)"
            :class="[
              'flex items-center justify-center rounded-md cursor-pointer transition-colors text-sm font-medium min-h-[2rem]',
              isToday(day) ? 'bg-blue-950 text-white' : '',
              isSelected(day) && !isToday(day) ? 'bg-blue-100 text-blue-950' : '',
              !isToday(day) && !isSelected(day) ? 'hover:bg-gray-100 text-gray-900' : ''
            ]"
          >
            {{ day }}
          </button>
        </div>
        <div v-if="showTime" class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
          <span class="text-xs font-medium text-gray-700">Время:</span>
          <input
            v-model.number="pickerHour"
            type="number"
            min="0"
            max="23"
            class="w-12 px-2 py-1.5 text-sm text-gray-900 font-medium border border-gray-200 rounded-md text-center tabular-nums"
            @input="clampTime"
          />
          <span class="text-gray-600 font-medium">:</span>
          <input
            v-model.number="pickerMinute"
            type="number"
            min="0"
            max="59"
            class="w-12 px-2 py-1.5 text-sm text-gray-900 font-medium border border-gray-200 rounded-md text-center tabular-nums"
            @input="clampTime"
          />
          <button
            type="button"
            @click="applyTime"
            class="ml-1 px-2 py-1.5 text-xs rounded-md bg-blue-950 text-white hover:bg-blue-900 transition-colors"
          >
            OK
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: null },
  placeholder: { type: String, default: 'Выберите дату' },
  disabled: { type: Boolean, default: false },
  /** Компактный вид для фильтров (одна линия) */
  inline: { type: Boolean, default: false },
  /** Показывать выбор времени (часы:минуты) */
  showTime: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])

const rootRef = ref(null)
const open = ref(false)

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']

/** Текущий отображаемый месяц (для навигации) */
const currentDate = ref(new Date())

function parseDate(str) {
  if (!str || typeof str !== 'string') return null
  const datePart = str.split('T')[0].split(' ')[0]
  const [y, m, d] = datePart.split('-').map(Number)
  if (!y || !m || !d) return null
  const date = new Date(y, m - 1, d)
  return isNaN(date.getTime()) ? null : date
}

function parseTime(str) {
  if (!str || typeof str !== 'string') return { hour: 9, minute: 0 }
  const timePart = str.includes('T') ? str.split('T')[1] : str.split(' ')[1]
  if (!timePart) return { hour: 9, minute: 0 }
  const [h, min] = timePart.split(':').map(Number)
  return {
    hour: Number.isInteger(h) && h >= 0 && h <= 23 ? h : 9,
    minute: Number.isInteger(min) && min >= 0 && min <= 59 ? min : 0,
  }
}

function toYYYYMMDD(date) {
  if (!date) return ''
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function toValue(date, hour, minute) {
  if (!date) return ''
  const dateStr = toYYYYMMDD(date)
  if (!props.showTime) return dateStr
  const h = String(hour ?? 9).padStart(2, '0')
  const min = String(minute ?? 0).padStart(2, '0')
  return `${dateStr}T${h}:${min}`
}

const selectedDate = computed(() => parseDate(props.modelValue))

const pickerHour = ref(9)
const pickerMinute = ref(0)

const displayValue = computed(() => {
  const d = selectedDate.value
  if (!d) return ''
  const dateStr = d.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
  if (!props.showTime) return dateStr
  const t = parseTime(props.modelValue)
  const h = String(t.hour).padStart(2, '0')
  const m = String(t.minute).padStart(2, '0')
  return `${dateStr} ${h}:${m}`
})

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
})

const firstDayOfMonth = computed(() => {
  const first = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1)
  let day = first.getDay()
  return day === 0 ? 6 : day - 1
})

const daysInMonth = computed(() => {
  const y = currentDate.value.getFullYear()
  const m = currentDate.value.getMonth()
  return new Date(y, m + 1, 0).getDate()
})

function isToday(day) {
  const today = new Date()
  return (
    day === today.getDate() &&
    currentDate.value.getMonth() === today.getMonth() &&
    currentDate.value.getFullYear() === today.getFullYear()
  )
}

function isSelected(day) {
  const sel = selectedDate.value
  if (!sel) return false
  return (
    day === sel.getDate() &&
    currentDate.value.getMonth() === sel.getMonth() &&
    currentDate.value.getFullYear() === sel.getFullYear()
  )
}

function selectDate(day) {
  const date = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth(),
    day
  )
  emit('update:modelValue', toValue(date, pickerHour.value, pickerMinute.value))
  if (!props.showTime) open.value = false
}

function clampTime() {
  if (pickerHour.value < 0) pickerHour.value = 0
  if (pickerHour.value > 23) pickerHour.value = 23
  if (pickerMinute.value < 0) pickerMinute.value = 0
  if (pickerMinute.value > 59) pickerMinute.value = 59
}

function applyTime() {
  const d = selectedDate.value
  if (d) {
    emit('update:modelValue', toValue(d, pickerHour.value, pickerMinute.value))
  } else {
    const today = new Date()
    emit('update:modelValue', toValue(today, pickerHour.value, pickerMinute.value))
  }
  open.value = false
}

function previousMonth() {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  )
}

function nextMonth() {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  )
}

function goToToday() {
  const today = new Date()
  currentDate.value = new Date(today.getFullYear(), today.getMonth(), 1)
  emit('update:modelValue', toValue(today, pickerHour.value, pickerMinute.value))
  if (!props.showTime) open.value = false
}

watch(open, (isOpen) => {
  if (isOpen && props.modelValue) {
    const d = parseDate(props.modelValue)
    if (d) {
      currentDate.value = new Date(d.getFullYear(), d.getMonth(), 1)
      if (props.showTime) {
        const t = parseTime(props.modelValue)
        pickerHour.value = t.hour
        pickerMinute.value = t.minute
      }
    }
  } else if (isOpen) {
    const today = new Date()
    currentDate.value = new Date(today.getFullYear(), today.getMonth(), 1)
    if (props.showTime) {
      const t = parseTime(props.modelValue)
      pickerHour.value = t.hour
      pickerMinute.value = t.minute
    }
  }
})

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
