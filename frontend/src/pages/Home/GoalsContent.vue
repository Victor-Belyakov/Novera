<template>
  <div class="w-full min-w-0 flex flex-col gap-6">
    <div class="flex flex-wrap items-center gap-3">
      <button
        type="button"
        @click="openAddModal"
        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium cursor-pointer flex items-center gap-2"
      >
        <span class="text-lg leading-none">+</span>
        Добавить
      </button>
      <AppDatePicker
        v-model="filterDateFrom"
        placeholder="Дата с"
        inline
      />
      <span class="text-sm text-gray-400">—</span>
      <AppDatePicker
        v-model="filterDateTo"
        placeholder="Дата по"
        inline
      />
      <button
        type="button"
        @click="clearDateFilter"
        class="px-3 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md hover:bg-gray-200 hover:text-gray-700 transition cursor-pointer"
      >
        Сбросить
      </button>
    </div>

    <AppSpinner v-if="loading" block label="Загрузка целей..." />
    <div v-else-if="error" class="py-12 text-center text-red-600">
      {{ error }}
    </div>
    <div v-else-if="!goalsGrouped.length" class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
      Нет целей. Добавьте цель и выберите категорию, чтобы видеть группировку.
    </div>
    <div v-else class="flex flex-col gap-3">
      <section
        v-for="group in goalsGrouped"
        :key="group.key"
        class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm"
      >
        <button
          type="button"
          class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left font-medium text-gray-900 bg-gray-50 hover:bg-gray-100 border-b border-gray-200 transition-colors cursor-pointer"
          @click="toggleGroup(group.key)"
        >
          <span class="flex items-center gap-2">
            <span
              class="inline-block w-5 h-5 flex items-center justify-center text-gray-500 transition-transform"
              :class="{ 'rotate-90': expandedGroups.has(group.key) }"
            >
              ▶
            </span>
            {{ group.label }}
          </span>
          <span class="text-sm font-normal text-gray-500">
            {{ group.goals.length }} {{ pluralGoals(group.goals.length) }}
          </span>
        </button>
        <div
          v-show="expandedGroups.has(group.key)"
          class="divide-y divide-gray-100"
        >
          <div
            v-for="g in group.goals"
            :key="g.id"
            class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/80 transition-colors"
            :class="{ 'opacity-60': g.completed === true }"
          >
            <div class="flex-1 min-w-0">
              <div
                class="text-gray-900 font-medium"
                :class="{ 'line-through text-gray-500': g.completed === true }"
              >
                {{ g.title }}
              </div>
              <div class="flex flex-wrap gap-x-4 gap-y-0 mt-0.5 text-sm text-gray-500">
                <span v-if="g.due_date">Срок: {{ formatDate(g.due_date) }}</span>
                <span v-if="g.created_by_name">{{ g.created_by_name }}</span>
              </div>
            </div>
            <div class="flex items-center shrink-0 gap-3">
              <button
                type="button"
                class="p-1.5 rounded-md transition-colors cursor-pointer"
                :class="hasReminder('goal', g.id) ? 'text-amber-500 hover:text-amber-600' : 'text-gray-400 hover:text-gray-500'"
                :disabled="reminderToggling === `goal-${g.id}`"
                :title="hasReminder('goal', g.id) ? 'Выключить уведомление' : 'Включить уведомление'"
                @click.stop="onBellClick('goal', g)"
              >
                <IconBell />
              </button>
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  :checked="g.completed === true"
                  :disabled="togglingId === g.id"
                  class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"
                  @change="toggleCompleted(g)"
                />
                <span class="text-sm text-gray-600">Выполнена</span>
              </label>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Модальное окно: за сколько до срока напомнить -->
    <Teleport to="body">
      <div
        v-if="reminderSetup"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
        @click.self="reminderSetup = null"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-5 border border-gray-100">
          <h3 class="text-base font-semibold text-gray-900 mb-1">Напомнить за сколько до срока?</h3>
          <p class="text-sm text-gray-500 mb-4">
            {{ reminderSetup.entity.title }}
            <span v-if="reminderSetup.entity.due_date" class="block mt-0.5">Срок: {{ formatDate(reminderSetup.entity.due_date) }}</span>
          </p>
          <div v-if="!reminderSetup.entity.due_date" class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-4">
            У цели нет срока — напомним в выбранный день в 09:00.
          </div>
          <div class="flex flex-col gap-2">
            <button
              v-for="opt in reminderWhenOptions"
              :key="opt.value"
              type="button"
              class="w-full px-4 py-2.5 text-left text-sm font-medium text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
              @click="createReminderWithOffset(opt.value)"
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

    <!-- Модальное окно: новая цель -->
    <Teleport to="body">
      <div
        v-if="showAddModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="closeAddModal"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-100 flex flex-col max-h-[90vh]">
          <div class="p-6 flex items-center justify-between bg-blue-950 rounded-t-xl shrink-0">
            <h3 class="text-lg font-semibold text-white">Новая цель</h3>
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
            <form @submit.prevent="submitGoal" class="p-6 space-y-5">
            <div>
              <label class="app-label app-label--required">Название</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="app-input"
                placeholder="Название цели"
              />
            </div>
            <div>
              <label class="app-label">Описание</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="app-input"
                placeholder="Формулировка цели, что именно сделать"
              />
            </div>
            <div>
              <label class="app-label">Категория</label>
              <AppSelect v-model="form.category_id" :options="categoryOptions" placeholder="Выберите" />
            </div>
            <div>
              <label class="app-label">Тип цели</label>
              <AppSelect v-model="form.type" :options="goalTypeOptions" placeholder="Выберите" />
            </div>
            <div>
              <label class="app-label">Срок выполнения</label>
              <AppDatePicker v-model="form.due_date" placeholder="Выберите срок" />
            </div>
            <div>
              <label class="app-label">Приоритет</label>
              <AppSelect v-model="form.priority" :options="priorityOptions" placeholder="Выберите" />
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
import { apiGet, apiPost, apiPatch, apiDelete, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import AppSelect from '@/components/AppSelect.vue'
import AppDatePicker from '@/components/AppDatePicker.vue'
import AppSpinner from '@/components/AppSpinner.vue'
import IconBell from '@/components/icons/IconBell.vue'

const goals = ref([])
const categoriesList = ref([])
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const saving = ref(false)
const formError = ref(null)
const togglingId = ref(null)
const reminders = ref([])
const reminderToggling = ref(null)
/** { entityType, entity } — показывать модалку выбора «за сколько до срока» */
const reminderSetup = ref(null)

const reminderWhenOptions = [
  { value: '15m', label: 'За 15 минут' },
  { value: '1h', label: 'За 1 час' },
  { value: '3h', label: 'За 3 часа' },
  { value: '1d', label: 'За 1 день' },
  { value: 'day09', label: 'В день срока в 09:00' },
]

const expandedGroups = ref(new Set())
const filterDateFrom = ref('')
const filterDateTo = ref('')

const form = reactive({
  title: '',
  description: '',
  category_id: '',
  type: 'result',
  due_date: '',
  priority: '0',
})

const goalTypeOptions = [
  { value: 'result', label: 'Результат' },
  { value: 'habit', label: 'Привычка' },
  { value: 'learning', label: 'Обучение' },
  { value: 'project', label: 'Проект' },
]

const priorityOptions = [
  { value: '0', label: 'Обычный' },
  { value: '1', label: 'Средний' },
  { value: '2', label: 'Высокий' },
]

const categoryOptions = computed(() =>
  categoriesList.value.map((c) => ({ value: String(c.id), label: c.title }))
)

const goalsFiltered = computed(() => {
  const list = goals.value
  const from = filterDateFrom.value.trim()
  const to = filterDateTo.value.trim()
  if (!from && !to) return list
  return list.filter((g) => {
    const d = g.due_date
    if (!d) return false
    if (from && d < from) return false
    if (to && d > to) return false
    return true
  })
})

const goalsGrouped = computed(() => {
  const list = goalsFiltered.value
  if (!list.length) return []
  const byCategory = new Map()
  for (const g of list) {
    const key = g.category_title ?? '__none__'
    const label = g.category_title ?? 'Без категории'
    if (!byCategory.has(key)) {
      byCategory.set(key, { key, label, goals: [] })
    }
    byCategory.get(key).goals.push(g)
  }
  const result = Array.from(byCategory.values())
  result.sort((a, b) => a.label.localeCompare(b.label, 'ru'))
  return result
})

function clearDateFilter() {
  filterDateFrom.value = ''
  filterDateTo.value = ''
}

function pluralGoals(n) {
  if (n === 1) return 'цель'
  if (n >= 2 && n <= 4) return 'цели'
  return 'целей'
}

function toggleGroup(key) {
  const next = new Set(expandedGroups.value)
  if (next.has(key)) next.delete(key)
  else next.add(key)
  expandedGroups.value = next
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

async function fetchGoals() {
  loading.value = true
  error.value = null
  try {
    const response = await apiGet(API_ENDPOINTS.GOALS.LIST)
    const data = await handleResponse(response)
    goals.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Ошибка загрузки целей'
    goals.value = []
  } finally {
    loading.value = false
  }
}

async function toggleCompleted(goal) {
  const newCompleted = goal.completed !== true
  togglingId.value = goal.id
  try {
    const response = await apiPatch(API_ENDPOINTS.GOALS.updateUrl(goal.id), { completed: newCompleted })
    const data = await handleResponse(response)
    const idx = goals.value.findIndex((x) => x.id === goal.id)
    if (idx !== -1) goals.value[idx] = { ...goals.value[idx], ...data }
  } catch {
    // revert visually handled by :checked
  } finally {
    togglingId.value = null
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

function getDeadlineForGoal(goal) {
  if (!goal.due_date) return null
  const str = goal.due_date
  if (str.includes('T')) return new Date(str)
  return new Date(str + 'T09:00:00')
}

function notifyAtFromOffset(goal, offset) {
  const deadline = getDeadlineForGoal(goal)
  if (!deadline && offset !== 'day09') {
    const d = new Date()
    d.setDate(d.getDate() + 1)
    d.setHours(9, 0, 0, 0)
    return d.toISOString().slice(0, 19)
  }
  if (!deadline) {
    const d = new Date()
    d.setHours(9, 0, 0, 0)
    return d.toISOString().slice(0, 19)
  }
  const t = deadline.getTime()
  if (offset === '15m') return new Date(t - 15 * 60 * 1000).toISOString().slice(0, 19)
  if (offset === '1h') return new Date(t - 60 * 60 * 1000).toISOString().slice(0, 19)
  if (offset === '3h') return new Date(t - 3 * 60 * 60 * 1000).toISOString().slice(0, 19)
  if (offset === '1d') return new Date(t - 24 * 60 * 60 * 1000).toISOString().slice(0, 19)
  if (offset === 'day09') {
    const d = new Date(deadline)
    d.setHours(9, 0, 0, 0)
    return d.toISOString().slice(0, 19)
  }
  return deadline.toISOString().slice(0, 19)
}

function onBellClick(entityType, entity) {
  const existing = getReminderFor(entityType, entity.id)
  if (existing) {
    toggleReminder(entityType, entity)
    return
  }
  reminderSetup.value = { entityType, entity }
}

async function createReminderWithOffset(offset) {
  if (!reminderSetup.value) return
  const { entityType, entity } = reminderSetup.value
  reminderSetup.value = null
  const key = `${entityType}-${entity.id}`
  reminderToggling.value = key
  try {
    const notify_at = notifyAtFromOffset(entity, offset)
    const payload = {
      title: entity.title,
      entity_type: entityType,
      entity_id: entity.id,
      notify_at,
      frequency: 'none',
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
  form.category_id = ''
  form.type = 'result'
  form.due_date = ''
  form.priority = '0'
  formError.value = null
  showAddModal.value = true
  fetchCategories()
}

function closeAddModal() {
  showAddModal.value = false
}

async function submitGoal() {
  saving.value = true
  formError.value = null
  try {
    const payload = {
      title: form.title.trim(),
      description: form.description?.trim() || null,
      category_id: form.category_id ? Number(form.category_id) : null,
      type: form.type || 'result',
      due_date: (form.due_date && form.due_date.trim()) ? form.due_date.trim().split('T')[0] : '',
      priority: form.priority !== '' ? Number(form.priority) : 0,
    }
    const response = await apiPost(API_ENDPOINTS.GOALS.CREATE, payload)
    await handleResponse(response)
    closeAddModal()
    await fetchGoals()
  } catch (e) {
    formError.value = e.message || 'Ошибка при создании цели'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchGoals()
  fetchReminders()
})
</script>
