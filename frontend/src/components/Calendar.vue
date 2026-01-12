<template>
  <div class="bg-white rounded-lg shadow-lg p-6 h-full flex flex-col w-full">
    <div class="flex items-center justify-between mb-4 flex-shrink-0">
      <h3 class="text-xl font-semibold text-gray-900">{{ currentMonthYear }}</h3>
      <div class="flex gap-2">
        <button
          @click="previousMonth"
          class="p-2 rounded-md hover:bg-gray-100 transition-colors"
          title="Предыдущий месяц"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
          </svg>
        </button>
        <button
          @click="nextMonth"
          class="p-2 rounded-md hover:bg-gray-100 transition-colors"
          title="Следующий месяц"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-600">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </button>
        <button
          @click="goToToday"
          class="px-3 py-1.5 text-sm rounded-md bg-blue-950 text-white hover:bg-blue-900 transition-colors"
          title="Сегодня"
        >
          Сегодня
        </button>
      </div>
    </div>

    <div class="grid grid-cols-7 gap-2 flex-1">
      <!-- Дни недели -->
      <div
        v-for="day in weekDays"
        :key="day"
        class="text-center text-sm font-medium text-gray-500 py-2 flex items-center justify-center"
      >
        {{ day }}
      </div>

      <!-- Пустые ячейки в начале месяца -->
      <div
        v-for="n in firstDayOfMonth"
        :key="`empty-${n}`"
      ></div>

      <!-- Дни месяца -->
      <div
        v-for="day in daysInMonth"
        :key="day"
        @click="selectDate(day)"
        :class="[
          'flex items-center justify-center rounded-md cursor-pointer transition-colors text-lg font-medium',
          isToday(day) ? 'bg-blue-950 text-white font-semibold' : '',
          isSelected(day) ? 'bg-blue-100 text-blue-950 font-semibold' : '',
          !isToday(day) && !isSelected(day) ? 'hover:bg-gray-100 text-gray-900' : '',
        ]"
      >
        {{ day }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const currentDate = ref(new Date())
const selectedDate = ref(new Date())

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('ru-RU', {
    month: 'long',
    year: 'numeric'
  })
})

const firstDayOfMonth = computed(() => {
  const firstDay = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth(),
    1
  )
  // Получаем день недели (0 = воскресенье, 1 = понедельник, ...)
  // Преобразуем: воскресенье = 6, понедельник = 0, и т.д.
  let day = firstDay.getDay()
  return day === 0 ? 6 : day - 1
})

const daysInMonth = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  return new Date(year, month + 1, 0).getDate()
})

const isToday = (day) => {
  const today = new Date()
  return (
    day === today.getDate() &&
    currentDate.value.getMonth() === today.getMonth() &&
    currentDate.value.getFullYear() === today.getFullYear()
  )
}

const isSelected = (day) => {
  return (
    day === selectedDate.value.getDate() &&
    currentDate.value.getMonth() === selectedDate.value.getMonth() &&
    currentDate.value.getFullYear() === selectedDate.value.getFullYear()
  )
}

const selectDate = (day) => {
  selectedDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth(),
    day
  )
  emit('date-selected', selectedDate.value)
}

const previousMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  )
}

const nextMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  )
}

const goToToday = () => {
  currentDate.value = new Date()
  selectedDate.value = new Date()
  emit('date-selected', selectedDate.value)
}

const emit = defineEmits(['date-selected'])
</script>

