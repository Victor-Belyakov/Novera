<template>
  <div class="flex-1 flex flex-col overflow-hidden p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Главная</h2>
    <div class="flex-1 flex min-h-0">
      <Calendar :tasks="tasks" @date-selected="handleDateSelected" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiGet, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import Calendar from '@/components/Calendar.vue'

const tasks = ref([])
const selectedDate = ref(null)

async function fetchTasks() {
  try {
    const res = await apiGet(API_ENDPOINTS.TASKS.LIST)
    const data = await handleResponse(res)
    tasks.value = Array.isArray(data) ? data : []
  } catch {
    tasks.value = []
  }
}

const handleDateSelected = (date) => {
  selectedDate.value = date
}

onMounted(() => {
  fetchTasks()
})
</script>



