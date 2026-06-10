<template>
  <div class="flex-1 flex flex-col overflow-hidden p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Главная</h2>
    <div class="flex gap-3 mb-6">
      <button
        class="px-4 py-2 rounded-md transition-colors font-medium cursor-pointer"
        :class="activeView === 'home' ? 'app-btn-primary' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'"
        @click="activeView = 'home'"
      >
        Домашняя
      </button>
      <button
        class="px-4 py-2 rounded-md transition-colors font-medium cursor-pointer"
        :class="activeView === 'calendar' ? 'app-btn-primary' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'"
        @click="activeView = 'calendar'"
      >
        Календарь
      </button>
      <button
        class="px-4 py-2 rounded-md transition-colors font-medium cursor-pointer"
        :class="activeView === 'statistics' ? 'app-btn-primary' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'"
        @click="activeView = 'statistics'"
      >
        Статистика
      </button>
    </div>
    <div class="flex-1 flex min-h-0">
      <section v-if="activeView === 'home'" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm w-full overflow-y-auto">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Обо мне сейчас</h3>
            <p class="mt-1 text-sm text-gray-500">Текущий срез по финансам, целям, привычкам и задачам.</p>
          </div>
          <div v-if="personalState" class="text-sm text-gray-500">
            {{ personalState.full_name }}, {{ personalState.age }} лет
          </div>
        </div>

        <div v-if="loadingState" class="py-6 text-gray-500">Загрузка сводки...</div>
        <div v-else-if="personalState" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
          <div
            v-for="card in personalStateCards"
            :key="card.key"
            class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 shadow-sm min-h-[7rem] flex flex-col justify-between"
          >
            <div class="text-sm font-semibold text-gray-900">{{ card.label }}</div>
            <div class="pt-3">
              <div class="text-2xl font-bold leading-none" :class="card.valueClass || 'text-blue-950'">{{ card.value }}</div>
              <div v-if="card.meta" class="mt-2 text-xs text-gray-500">{{ card.meta }}</div>
            </div>
          </div>
        </div>
        <div v-else class="py-6 text-gray-500">Не удалось загрузить персональную сводку.</div>
      </section>
      <Calendar v-else-if="activeView === 'calendar'" :tasks="tasks" @date-selected="handleDateSelected" />
      <StatisticsContent v-else />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { apiGet, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import Calendar from '@/components/Calendar.vue'
import StatisticsContent from './StatisticsContent.vue'

const tasks = ref([])
const selectedDate = ref(null)
const activeView = ref('home')
const personalState = ref(null)
const loadingState = ref(false)

async function fetchTasks() {
  try {
    const res = await apiGet(API_ENDPOINTS.TASKS.LIST)
    const data = await handleResponse(res)
    tasks.value = Array.isArray(data) ? data : []
  } catch {
    tasks.value = []
  }
}

async function fetchPersonalState() {
  loadingState.value = true
  try {
    const res = await apiGet(API_ENDPOINTS.PERSONAL_STATE.GET)
    const data = await handleResponse(res)
    personalState.value = data ?? null
  } catch {
    personalState.value = null
  } finally {
    loadingState.value = false
  }
}

const handleDateSelected = (date) => {
  selectedDate.value = date
}

const personalStateCards = computed(() => {
  if (!personalState.value) return []

  return [
    {
      key: 'balance',
      label: 'Текущий баланс',
      value: formatAmount(personalState.value.current_balance),
      valueClass: Number(personalState.value.current_balance) >= 0 ? 'text-blue-950' : 'text-red-600',
    },
    {
      key: 'month-plan',
      label: 'Баланс месяца',
      value: formatAmount(personalState.value.month_balance_actual),
      meta: `План: ${formatAmount(personalState.value.month_balance_plan)}`,
      valueClass: Number(personalState.value.month_balance_actual) >= 0 ? 'text-blue-950' : 'text-red-600',
    },
    {
      key: 'goals',
      label: 'Активные цели',
      value: String(personalState.value.active_goals),
    },
    {
      key: 'habits',
      label: 'Активные привычки',
      value: String(personalState.value.active_habits),
      meta: `Успех за 7 дней: ${personalState.value.habit_success_rate}%`,
    },
    {
      key: 'tasks-progress',
      label: 'Задачи в работе',
      value: String(personalState.value.tasks_in_progress),
    },
    {
      key: 'tasks-overdue',
      label: 'Просроченные задачи',
      value: String(personalState.value.overdue_tasks),
      valueClass: personalState.value.overdue_tasks > 0 ? 'text-red-600' : 'text-blue-950',
    },
    {
      key: 'month-income',
      label: 'Доход месяца',
      value: formatAmount(personalState.value.month_income_actual),
      meta: `План: ${formatAmount(personalState.value.month_income_plan)}`,
      valueClass: 'text-green-600',
    },
    {
      key: 'month-expense',
      label: 'Расход месяца',
      value: formatAmount(personalState.value.month_expense_actual),
      meta: `План: ${formatAmount(personalState.value.month_expense_plan)}`,
      valueClass: 'text-red-600',
    },
    {
      key: 'weight',
      label: 'Последний вес',
      value: personalState.value.last_weight ? `${formatAmount(personalState.value.last_weight)} кг` : '—',
      meta: personalState.value.last_weight_recorded_at ? `Записан: ${formatDate(personalState.value.last_weight_recorded_at)}` : null,
    },
    {
      key: 'pressure',
      label: 'Последнее давление',
      value: personalState.value.last_blood_pressure || '—',
      meta: personalState.value.last_blood_pressure_recorded_at ? `Записано: ${formatDate(personalState.value.last_blood_pressure_recorded_at)}` : null,
    },
  ]
})

function formatAmount(value) {
  const amount = Number(value ?? 0)

  return new Intl.NumberFormat('ru-RU', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Date(value).toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return value
  }
}

onMounted(() => {
  fetchTasks()
  fetchPersonalState()
})
</script>
