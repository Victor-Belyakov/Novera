<template>
  <div class="flex-1 flex flex-col overflow-hidden p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Главная</h2>
    <div class="flex-1 flex">
      <Calendar @date-selected="handleDateSelected" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Calendar from '@/components/Calendar.vue'

const selectedDate = ref(null)

const handleDateSelected = (date) => {
  selectedDate.value = date
  console.log('Выбранная дата:', date)
}
</script>



