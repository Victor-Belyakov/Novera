<template>
  <div class="bg-white rounded-lg shadow-lg p-6 h-full w-full overflow-y-auto">
    <div class="mb-6">
      <h3 class="text-xl font-semibold text-gray-900">Статистика</h3>
      <p class="text-sm text-gray-500 mt-1">Сводная статистика по сущностям системы</p>
    </div>

    <div v-if="loading" class="text-gray-500">Загрузка статистики...</div>
    <div v-else-if="summary.length === 0 && habitCategoryGroups.length === 0 && goalCategoryGroups.length === 0 && taskCategoryGroups.length === 0 && financeCategoryGroups.length === 0" class="text-gray-500">Пока недостаточно данных для статистики.</div>

    <div v-else class="space-y-8">
      <section
        v-for="section in sections"
        :key="section.key"
        class="rounded-2xl border border-gray-200 bg-gray-50/70 p-5"
      >
        <div class="mb-5 flex items-center justify-between gap-4">
          <div>
            <h4 class="text-lg font-semibold text-gray-900">{{ section.title }}</h4>
            <p class="text-sm text-gray-500 mt-1">{{ section.description }}</p>
          </div>
        </div>

        <div v-if="section.cards.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
          <div
            v-for="stat in section.cards"
            :key="`${section.key}-${stat.key}`"
            class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm min-h-[7.5rem] flex flex-col justify-between"
          >
            <h5 class="text-sm font-semibold leading-snug text-gray-900 max-w-[13rem]">
              {{ stat.label }}
            </h5>

            <div class="pt-3 flex items-end justify-between gap-3">
              <div class="text-xs uppercase tracking-wide text-gray-400">
                Показатель
              </div>
              <div class="text-3xl font-bold leading-none text-blue-950 tabular-nums">
                {{ stat.value }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="section.groups.length > 0" class="mt-5 space-y-5">
          <div
            v-for="group in section.groups"
            :key="`${section.key}-${group.category}`"
            class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
          >
            <h5 class="text-base font-semibold text-gray-900 mb-4">{{ group.category }}</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
              <div
                v-for="donut in group.items"
                :key="`${section.key}-${group.category}-${donut.label}`"
                class="rounded-xl border border-gray-100 bg-gray-50 p-4"
              >
                <div class="flex items-center gap-4">
                  <div class="relative h-20 w-20 shrink-0">
                    <div
                      class="h-20 w-20 rounded-full"
                      :style="getDonutStyle(section.key, donut)"
                    ></div>
                    <div class="absolute inset-3 rounded-full bg-white flex items-center justify-center">
                      <span class="text-xs font-bold text-gray-800">{{ donut.success_percent }}%</span>
                    </div>
                  </div>

                  <div class="min-w-0">
                    <h6 class="text-sm font-semibold text-gray-900">{{ donut.label }}</h6>
                    <div class="mt-2 text-sm text-gray-600 space-y-1">
                      <template v-if="section.key === 'goals'">
                        <div>Успех: <span class="font-semibold text-green-600">{{ donut.success }}</span></div>
                        <div>Нет: <span class="font-semibold text-red-600">{{ donut.fail }}</span></div>
                        <div>В процессе: <span class="font-semibold text-gray-500">{{ donut.in_progress }}</span></div>
                      </template>
                      <template v-else-if="section.key === 'habits'">
                        <div>Успех: <span class="font-semibold text-green-600">{{ donut.success }}</span></div>
                        <div>Нет: <span class="font-semibold text-red-600">{{ donut.fail }}</span></div>
                      </template>
                      <template v-if="section.key === 'tasks'">
                        <div>Новая: <span class="font-semibold text-gray-600">{{ donut.new }}</span></div>
                        <div>В работе: <span class="font-semibold text-blue-600">{{ donut.in_progress }}</span></div>
                        <div>Выполнена: <span class="font-semibold text-green-600">{{ donut.done }}</span></div>
                        <div>Закрыта: <span class="font-semibold text-red-600">{{ donut.closed }}</span></div>
                      </template>
                      <template v-if="section.key === 'finance'">
                        <div>Тип: <span class="font-semibold" :class="donut.type === 'income' ? 'text-green-600' : 'text-red-600'">{{ donut.type_label }}</span></div>
                        <div>План: <span class="font-semibold text-gray-800">{{ formatAmount(donut.planned_amount) }}</span></div>
                        <div>Факт: <span class="font-semibold text-gray-800">{{ formatAmount(donut.actual_amount) }}</span></div>
                        <div>Отклонение: <span class="font-semibold" :class="financeDifferenceClass(donut)">{{ formatSignedAmount(donut.difference) }}</span></div>
                      </template>
                      <div v-if="section.key !== 'finance'">Всего: <span class="font-semibold text-gray-800">{{ donut.total }}</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { apiGet, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'

const summary = ref([])
const habitCategoryGroups = ref([])
const goalCategoryGroups = ref([])
const taskCategoryGroups = ref([])
const financeCategoryGroups = ref([])
const loading = ref(false)

const sections = computed(() => [
  {
    key: 'goals',
    title: 'Цели',
    description: 'Общий срез по целям и их категориям.',
    cards: summary.value.filter((item) => item.section === 'goals'),
    groups: goalCategoryGroups.value,
  },
  {
    key: 'habits',
    title: 'Привычки',
    description: 'Состояние привычек и результативность по категориям.',
    cards: summary.value.filter((item) => item.section === 'habits'),
    groups: habitCategoryGroups.value,
  },
  {
    key: 'tasks',
    title: 'Задачи',
    description: 'Состояние задач по категориям целей и их текущим статусам.',
    cards: summary.value.filter((item) => item.section === 'tasks'),
    groups: taskCategoryGroups.value,
  },
  {
    key: 'finance',
    title: 'Финансы',
    description: 'Выполнение финансового плана по категориям за текущий месяц.',
    cards: summary.value.filter((item) => item.section === 'finance'),
    groups: financeCategoryGroups.value,
  },
].filter((section) => section.cards.length > 0 || section.groups.length > 0))

async function fetchStatistics() {
  loading.value = true
  try {
    const res = await apiGet(API_ENDPOINTS.STATISTICS.LIST)
    const data = await handleResponse(res)
    summary.value = Array.isArray(data?.summary) ? data.summary : []
    habitCategoryGroups.value = Array.isArray(data?.habit_category_groups) ? data.habit_category_groups : []
    goalCategoryGroups.value = Array.isArray(data?.goal_category_groups) ? data.goal_category_groups : []
    taskCategoryGroups.value = Array.isArray(data?.task_category_groups) ? data.task_category_groups : []
    financeCategoryGroups.value = Array.isArray(data?.finance_category_groups) ? data.finance_category_groups : []
  } catch {
    summary.value = []
    habitCategoryGroups.value = []
    goalCategoryGroups.value = []
    taskCategoryGroups.value = []
    financeCategoryGroups.value = []
  } finally {
    loading.value = false
  }
}

function donutStyle(donut) {
  const success = donut.success_percent ?? 0
  return {
    background: `conic-gradient(#16a34a 0 ${success}%, #dc2626 ${success}% 100%)`,
  }
}

function goalDonutStyle(donut) {
  if ((donut.success ?? 0) === 0) {
    return {
      background: '#d1d5db',
    }
  }

  return donutStyle(donut)
}

function getDonutStyle(sectionKey, donut) {
  if (sectionKey === 'goals') return goalDonutStyle(donut)
  if (sectionKey === 'tasks') return taskDonutStyle(donut)
  if (sectionKey === 'finance') return financeDonutStyle(donut)

  return donutStyle(donut)
}

function taskDonutStyle(donut) {
  const total = donut.total ?? 0

  if (total === 0) {
    return {
      background: '#d1d5db',
    }
  }

  const newPercent = Math.round(((donut.new ?? 0) / total) * 100)
  const inProgressPercent = Math.round(((donut.in_progress ?? 0) / total) * 100)
  const donePercent = Math.round(((donut.done ?? 0) / total) * 100)
  const closedPercent = 100 - newPercent - inProgressPercent - donePercent

  return {
    background: `conic-gradient(
      #9ca3af 0 ${newPercent}%,
      #60a5fa ${newPercent}% ${newPercent + inProgressPercent}%,
      #16a34a ${newPercent + inProgressPercent}% ${newPercent + inProgressPercent + donePercent}%,
      #dc2626 ${newPercent + inProgressPercent + donePercent}% ${newPercent + inProgressPercent + donePercent + closedPercent}%
    )`,
  }
}

function financeDonutStyle(donut) {
  if (!donut.has_plan) {
    return {
      background: '#d1d5db',
    }
  }

  const progress = Math.max(0, Number(donut.success_percent ?? 0))
  const cappedProgress = Math.min(progress, 100)
  const fillColor = donut.type === 'income' ? '#16a34a' : '#dc2626'

  return {
    background: `conic-gradient(${fillColor} 0 ${cappedProgress}%, #e5e7eb ${cappedProgress}% 100%)`,
  }
}

function formatAmount(value) {
  const amount = Number(value ?? 0)

  return new Intl.NumberFormat('ru-RU', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

function formatSignedAmount(value) {
  const amount = Number(value ?? 0)
  const sign = amount > 0 ? '+' : ''
  return `${sign}${formatAmount(amount)}`
}

function financeDifferenceClass(donut) {
  const difference = Number(donut.difference ?? 0)

  if (donut.type === 'expense') {
    return difference >= 0 ? 'text-green-600' : 'text-red-600'
  }

  return difference >= 0 ? 'text-green-600' : 'text-red-600'
}

onMounted(() => {
  fetchStatistics()
})
</script>
