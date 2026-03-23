<template>
  <div class="w-full min-w-0 flex flex-col gap-4">
    <div class="flex flex-wrap items-center gap-2">
      <button
        type="button"
        @click="openAddModal"
        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium cursor-pointer flex items-center gap-2"
      >
        <span class="text-lg leading-none">+</span>
        Добавить
      </button>
      <button
        type="button"
        @click="toggleTracker"
        class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium cursor-pointer"
        :class="{ 'ring-2 ring-blue-950 bg-blue-50 text-blue-950 border-blue-950': showTracker }"
      >
        Трекер
      </button>
    </div>

    <!-- Трекер: карточки привычек с таблицей Дата / Выполнено -->
    <template v-if="showTracker">
      <div v-if="loading && !habits.length" class="py-8 flex justify-center">
        <AppSpinner block />
      </div>
      <div v-else-if="!habits.length" class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
        Нет привычек. Добавьте привычку, чтобы вести трекер.
      </div>
      <div v-else class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
        <div
          v-for="h in habits"
          :key="h.id"
          class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
        >
          <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 font-medium text-gray-900">
            {{ h.title }}
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200 text-left text-gray-500">
                  <th class="px-4 py-2 font-medium">Дата</th>
                  <th class="px-4 py-2 font-medium w-24">Выполнено</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in getTrackerRows(h)"
                  :key="row.dateKey"
                  class="border-b border-gray-100"
                  :class="{ 'hover:bg-gray-50/50': !row.skipped, 'bg-red-50/40': row.skipped }"
                >
                  <td class="px-4 py-2 text-gray-900">{{ row.dateLabel }}</td>
                  <td class="px-4 py-2">
                    <template v-if="row.done">
                      <span
                        class="inline-flex items-center justify-center w-8 h-8 rounded border bg-green-100 border-green-300 text-green-800"
                        title="Выполнено"
                      >
                        ✓
                      </span>
                    </template>
                    <template v-else-if="row.skipped">
                      <span
                        class="inline-flex items-center justify-center min-w-[4rem] px-2 py-1 rounded border border-red-200 bg-red-50 text-red-700 text-xs font-medium"
                        title="Не выполнено до конца дня"
                      >
                        Пропущено
                      </span>
                    </template>
                    <button
                      v-else
                      type="button"
                      class="inline-flex items-center justify-center w-8 h-8 rounded border border-gray-200 bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors cursor-pointer"
                      :disabled="trackerToggling === `${h.id}-${row.dateKey}`"
                      title="Отметить выполнение"
                      @click="toggleLog(h, row.dateKey)"
                    >
                      &nbsp;
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <p v-if="trackerLogsError" class="text-sm text-red-600">{{ trackerLogsError }}</p>
    </template>

    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12" aria-label="Уведомление"></th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название привычки</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Результат</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цель</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата создания</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="5" class="px-4 py-8 text-center">
                <AppSpinner block />
              </td>
            </tr>
            <tr v-else-if="error">
              <td colspan="5" class="px-4 py-8 text-left text-red-600">{{ error }}</td>
            </tr>
            <tr v-else-if="!habits.length">
              <td colspan="5" class="px-4 py-8 text-left text-gray-500">Нет привычек</td>
            </tr>
            <tr v-else v-for="h in habits" :key="h.id" class="hover:bg-gray-50">
              <td class="px-2 py-3">
                <button
                  type="button"
                  class="p-1.5 rounded-md transition-colors cursor-pointer"
                  :class="hasReminder('habit', h.id) ? 'text-amber-500 hover:text-amber-600' : 'text-gray-400 hover:text-gray-500'"
                  :disabled="reminderToggling === `habit-${h.id}`"
                  :title="hasReminder('habit', h.id) ? 'Выключить уведомление' : 'Включить уведомление'"
                  @click.stop="onBellClick('habit', h)"
                >
                  <IconBell />
                </button>
              </td>
              <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ h.title }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatResult(h) }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ h.goal_title ?? '—' }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(h.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Модальное окно: во сколько напомнить (привычка) -->
    <Teleport to="body">
      <div
        v-if="reminderSetup"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
        @click.self="reminderSetup = null"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-5 border border-gray-100">
          <h3 class="text-base font-semibold text-gray-900 mb-1">Во сколько напомнить?</h3>
          <p class="text-sm text-gray-500 mb-4">{{ reminderSetup.entity.title }}</p>
          <p class="text-xs text-gray-500 mb-3">Ежедневно в выбранное время</p>
          <div class="flex flex-col gap-2">
            <button
              v-for="opt in habitReminderTimeOptions"
              :key="opt.value"
              type="button"
              class="w-full px-4 py-2.5 text-left text-sm font-medium text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
              @click="createHabitReminderAtTime(opt.value)"
            >
              {{ opt.label }}
            </button>
          </div>
          <button
            type="button"
            class="mt-4 w-full px-4 py-2 text-sm text-gray-500 hover:text-gray-700"
            @click="reminderSetup = null"
          >
            Отмена
          </button>
        </div>
      </div>
    </Teleport>

    <!-- Модальное окно: новая привычка -->
    <Teleport to="body">
      <div
        v-if="showAddModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="closeAddModal"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between bg-blue-950 rounded-t-xl shrink-0">
            <h3 class="text-lg font-semibold text-white">Новая привычка</h3>
            <button
              type="button"
              @click="closeAddModal"
              class="p-2 rounded-lg hover:bg-white/20 text-white transition cursor-pointer"
              aria-label="Закрыть"
            >
              ✕
            </button>
          </div>
          <div class="overflow-y-auto flex-1 min-h-0">
            <form @submit.prevent="submitHabit" class="p-6 space-y-5">
              <div>
                <label class="app-label app-label--required">Название привычки</label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="app-input"
                  placeholder="Название"
                />
              </div>
              <div>
                <label class="app-label">Описание</label>
                <textarea
                  v-model="form.description"
                  rows="2"
                  class="app-input"
                  placeholder="Подробное описание"
                />
              </div>
              <div>
                <label class="app-label">Периодичность</label>
                <AppSelect v-model="form.frequency" :options="frequencyOptions" placeholder="Выберите" />
              </div>
              <div>
                <label class="app-label">Категория</label>
                <AppSelect v-model="form.category_id" :options="categoryOptions" placeholder="Выберите" />
              </div>
              <div>
                <label class="app-label">Цель</label>
                <AppSelect v-model="form.goal_id" :options="goalOptions" placeholder="Выберите" />
              </div>
              <div>
                <label class="app-label">Предпочтительное время</label>
                <input
                  v-model="form.preferred_time"
                  type="time"
                  class="app-input"
                />
              </div>
              <div class="flex gap-3 pt-3">
                <button
                  type="submit"
                  :disabled="saving"
                  class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 text-sm font-medium shadow-sm transition"
                >
                  {{ saving ? 'Сохранение...' : 'Создать' }}
                </button>
                <button
                  type="button"
                  @click="closeAddModal"
                  class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition"
                >
                  Отмена
                </button>
              </div>
              <p v-if="formError" class="text-sm text-red-600">{{ formError }}</p>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { apiGet, apiPost, apiDelete, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import AppSelect from '@/components/AppSelect.vue'
import AppSpinner from '@/components/AppSpinner.vue'
import IconBell from '@/components/icons/IconBell.vue'

const habits = ref([])
const categoriesList = ref([])
const goalsList = ref([])
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const saving = ref(false)
const formError = ref(null)

const showTracker = ref(false)
/** Слоты с бэкенда по habitId: { [habitId]: [{ date, status }, ...] } */
const habitSlots = ref({})
const trackerLogsError = ref(null)
const trackerToggling = ref(null)

const reminders = ref([])
const reminderToggling = ref(null)
const reminderSetup = ref(null)

const habitReminderTimeOptions = [
  { value: '06:00', label: 'В 06:00' },
  { value: '09:00', label: 'В 09:00' },
  { value: '12:00', label: 'В 12:00' },
  { value: '18:00', label: 'В 18:00' },
]

const form = reactive({
  title: '',
  description: '',
  frequency: 'daily',
  category_id: '',
  goal_id: '',
  preferred_time: '',
})

const frequencyOptions = [
  { value: 'daily', label: 'Ежедневно' },
  { value: '3_per_week', label: '3 раза в неделю' },
  { value: '2_per_week', label: '2 раза в неделю' },
  { value: 'weekly', label: 'Еженедельно' },
  { value: 'monthly', label: 'Ежемесячно' },
]

const categoryOptions = computed(() =>
  categoriesList.value.map((c) => ({ value: String(c.id), label: c.title }))
)

const goalOptions = computed(() =>
  goalsList.value.map((g) => ({
    value: String(g.id),
    label: g.category_title ? `${g.title} (${g.category_title})` : g.title,
  }))
)

function formatResult(h) {
  const done = h.progress ?? 0
  const total = h.result_total ?? 0
  return `${done} / ${total}`
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

function dateKeyToLabel(dateKey) {
  if (!dateKey) return '—'
  const [y, m, day] = dateKey.split('-')
  return [day, m, y].join('.')
}

/** Строки трекера — только из слотов с бэкенда (HabitLog). */
function getTrackerRows(h) {
  const slots = habitSlots.value[h.id] || []
  return slots.map((slot) => ({
    dateKey: slot.date,
    dateLabel: dateKeyToLabel(slot.date),
    done: slot.status === 'completed',
    skipped: slot.status === 'skipped',
  }))
}

function toggleTracker() {
  showTracker.value = !showTracker.value
  trackerLogsError.value = null
  if (showTracker.value && habits.value.length) {
    fetchAllHabitLogs()
  } else if (!showTracker.value) {
    fetchHabits()
  }
}

async function fetchAllHabitLogs() {
  trackerLogsError.value = null
  const next = {}
  try {
    await Promise.all(
      habits.value.map(async (h) => {
        try {
          const res = await apiGet(API_ENDPOINTS.HABITS.logsUrl(h.id))
          const data = await handleResponse(res)
          next[h.id] = Array.isArray(data?.slots) ? data.slots : []
        } catch {
          next[h.id] = []
        }
      })
    )
    habitSlots.value = next
  } catch (e) {
    trackerLogsError.value = e.message || 'Ошибка загрузки слотов'
  }
}

async function toggleLog(habit, dateKey) {
  const key = `${habit.id}-${dateKey}`
  trackerToggling.value = key
  const slots = habitSlots.value[habit.id] || []
  const slot = slots.find((s) => s.date === dateKey)
  const done = slot?.status === 'completed'
  try {
    if (done) {
      const res = await apiDelete(`${API_ENDPOINTS.HABITS.logsUrl(habit.id)}?date=${encodeURIComponent(dateKey)}`)
      await handleResponse(res)
    } else {
      const res = await apiPost(API_ENDPOINTS.HABITS.addLogUrl(habit.id), { date: dateKey })
      await handleResponse(res)
    }
    await fetchAllHabitLogs()
  } catch (e) {
    trackerLogsError.value = e.message || 'Ошибка'
  } finally {
    trackerToggling.value = null
  }
}

async function fetchHabits() {
  loading.value = true
  error.value = null
  try {
    const response = await apiGet(API_ENDPOINTS.HABITS.LIST)
    const data = await handleResponse(response)
    habits.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Ошибка загрузки привычек'
    habits.value = []
  } finally {
    loading.value = false
  }
}

async function fetchCategories() {
  try {
    const response = await apiGet(API_ENDPOINTS.CATEGORIES.LIST)
    const data = await handleResponse(response)
    categoriesList.value = Array.isArray(data) ? data : []
  } catch {
    categoriesList.value = []
  }
}

async function fetchGoals() {
  try {
    const response = await apiGet(API_ENDPOINTS.GOALS.LIST)
    const data = await handleResponse(response)
    goalsList.value = Array.isArray(data) ? data : []
  } catch {
    goalsList.value = []
  }
}

async function fetchReminders() {
  try {
    const response = await apiGet(API_ENDPOINTS.REMINDERS.LIST)
    const data = await handleResponse(response)
    reminders.value = Array.isArray(data) ? data : []
  } catch {
    reminders.value = []
  }
}

function hasReminder(entityType, entityId) {
  return reminders.value.some(
    (r) => r.entity_type === entityType && r.entity_id === entityId && r.status !== 'done'
  )
}

function getReminderFor(entityType, entityId) {
  return reminders.value.find(
    (r) => r.entity_type === entityType && r.entity_id === entityId && r.status !== 'done'
  )
}

function notifyAtForHabitTime(timeStr) {
  const [h, m] = timeStr.split(':').map(Number)
  const d = new Date()
  d.setHours(h, m ?? 0, 0, 0)
  if (d <= new Date()) d.setDate(d.getDate() + 1)
  return d.toISOString().slice(0, 19)
}

function onBellClick(entityType, entity) {
  const existing = getReminderFor(entityType, entity.id)
  if (existing) {
    toggleReminder(entityType, entity)
    return
  }
  reminderSetup.value = { entityType, entity }
}

async function createHabitReminderAtTime(timeStr) {
  if (!reminderSetup.value) return
  const { entityType, entity } = reminderSetup.value
  reminderSetup.value = null
  const key = `${entityType}-${entity.id}`
  reminderToggling.value = key
  try {
    const notify_at = notifyAtForHabitTime(timeStr)
    const payload = {
      title: entity.title,
      entity_type: entityType,
      entity_id: entity.id,
      notify_at,
      frequency: (entity.frequency && entity.frequency !== 'none') ? entity.frequency : 'daily',
    }
    const res = await apiPost(API_ENDPOINTS.REMINDERS.CREATE, payload)
    const data = await handleResponse(res)
    reminders.value = [...reminders.value, data]
  } catch {
    await fetchReminders()
  } finally {
    reminderToggling.value = null
  }
}

async function toggleReminder(entityType, entity) {
  const key = `${entityType}-${entity.id}`
  reminderToggling.value = key
  try {
    const existing = getReminderFor(entityType, entity.id)
    if (existing) {
      await apiDelete(API_ENDPOINTS.REMINDERS.deleteUrl(existing.id))
      reminders.value = reminders.value.filter((r) => r.id !== existing.id)
    }
  } catch {
    await fetchReminders()
  } finally {
    reminderToggling.value = null
  }
}

function openAddModal() {
  form.title = ''
  form.description = ''
  form.frequency = 'daily'
  form.category_id = ''
  form.goal_id = ''
  form.preferred_time = ''
  formError.value = null
  showAddModal.value = true
  fetchCategories()
  fetchGoals()
}

function closeAddModal() {
  showAddModal.value = false
}

async function submitHabit() {
  saving.value = true
  formError.value = null
  try {
    const payload = {
      title: form.title.trim(),
      description: form.description?.trim() || null,
      frequency: form.frequency,
      category_id: form.category_id ? Number(form.category_id) : null,
      goal_id: form.goal_id ? Number(form.goal_id) : null,
      preferred_time: form.preferred_time || null,
    }
    const response = await apiPost(API_ENDPOINTS.HABITS.CREATE, payload)
    await handleResponse(response)
    closeAddModal()
    await fetchHabits()
  } catch (e) {
    formError.value = e.message || 'Ошибка при создании привычки'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchHabits()
  fetchReminders()
})
</script>
