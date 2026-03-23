<template>
  <div class="flex flex-col h-full min-h-0 w-full -mx-6">
    <!-- Кнопка добавить и фильтр по сроку -->
    <div class="mb-4 flex shrink-0 px-6 flex flex-wrap items-center gap-3">
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
            У задачи нет срока — напомним завтра в 09:00.
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

    <!-- Модальное окно: новая задача -->
    <Teleport to="body">
      <div
        v-if="showAddModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="closeAddModal"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between bg-blue-950 rounded-t-xl shrink-0 overflow-hidden">
            <h3 class="text-lg font-semibold text-white">Новая задача</h3>
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
          <form @submit.prevent="submitTask" class="p-6 space-y-5 block">
            <div>
              <label class="app-label app-label--required">Название</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="app-input"
                placeholder="Название задачи"
              />
            </div>
            <div>
              <label class="app-label">Описание</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="app-input"
                placeholder="Описание"
              />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="app-label">Статус</label>
                <AppSelect v-model="form.status" :options="statusOptions" placeholder="Статус" />
              </div>
              <div>
                <label class="app-label">Приоритет</label>
                <AppSelect v-model="form.priority" :options="priorityOptions" placeholder="Приоритет" />
              </div>
            </div>
            <div>
              <label class="app-label">Срок выполнения</label>
              <AppDatePicker v-model="form.due_date" placeholder="Выберите срок" />
            </div>
            <div>
              <label class="app-label">Ответственный</label>
              <AppSelect
                v-model="form.assignee_id"
                :options="assigneeOptions"
                placeholder="Выберите"
              />
            </div>
            <div>
              <label class="app-label">Родительская задача</label>
              <AppSelect v-model="form.parent_id" :options="parentTaskOptions" placeholder="Выберите" />
            </div>
            <div>
              <label class="app-label">Цель</label>
              <AppSelect v-model="form.goal_id" :options="goalOptions" placeholder="Выберите" />
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

    <!-- Модальное окно: просмотр задачи -->
    <Teleport to="body">
      <div
        v-if="viewTask"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="viewTask = null"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between bg-blue-950 rounded-t-xl shrink-0 overflow-hidden">
            <h3 class="text-lg font-semibold text-white truncate pr-8">{{ viewTask.title }}</h3>
            <button
              type="button"
              @click="viewTask = null"
              class="p-2 rounded-lg hover:bg-white/20 text-white transition cursor-pointer shrink-0"
              aria-label="Закрыть"
            >
              ✕
            </button>
          </div>
          <div class="overflow-y-auto flex-1 min-h-0 p-6 space-y-4">
            <div v-if="viewTask.description">
              <div class="app-label">Описание</div>
              <p class="text-gray-900 whitespace-pre-wrap">{{ viewTask.description }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="app-label">Статус</div>
                <p class="text-gray-900">{{ getStatusLabel(viewTask.status) }}</p>
              </div>
              <div>
                <div class="app-label">Приоритет</div>
                <p class="text-gray-900">{{ getPriorityLabel(viewTask.priority) }}</p>
              </div>
            </div>
            <div v-if="viewTask.due_date">
              <div class="app-label">Срок выполнения</div>
              <p class="text-gray-900">{{ formatDate(viewTask.due_date) }}</p>
            </div>
            <div v-if="viewTask.assignee_name">
              <div class="app-label">Ответственный</div>
              <p class="text-gray-900">{{ viewTask.assignee_name }}</p>
            </div>
            <div v-if="viewTask.created_by_name">
              <div class="app-label">Кто поставил</div>
              <p class="text-gray-900">{{ viewTask.created_by_name }}</p>
            </div>
            <div v-if="viewTask.parent_id">
              <div class="app-label">Родительская задача</div>
              <p class="text-gray-900">{{ getParentTitle(viewTask.parent_id) }}</p>
            </div>
            <div v-if="viewTask.goal_title">
              <div class="app-label">Цель</div>
              <p class="text-gray-900">{{ viewTask.goal_title }}</p>
            </div>
            <div class="pt-2 border-t border-gray-100 text-sm text-gray-500">
              <span>Создано: {{ formatDateTime(viewTask.created_at) }}</span>
              <span class="ml-4">Обновлено: {{ formatDateTime(viewTask.updated_at) }}</span>
            </div>
            <div class="pt-4 flex justify-end">
              <button
                type="button"
                @click="openEditModal(viewTask)"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition cursor-pointer"
              >
                Редактировать
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Модальное окно: редактирование задачи -->
    <Teleport to="body">
      <div
        v-if="editTask"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
        @click.self="closeEditModal"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between bg-blue-950 rounded-t-xl shrink-0 overflow-hidden">
            <h3 class="text-lg font-semibold text-white">Редактирование задачи</h3>
            <button
              type="button"
              @click="closeEditModal"
              class="p-2 rounded-lg hover:bg-white/20 text-white transition cursor-pointer shrink-0"
              aria-label="Закрыть"
            >
              ✕
            </button>
          </div>
          <div class="overflow-y-auto flex-1 min-h-0">
            <form @submit.prevent="submitEdit" class="p-6 space-y-5 block">
              <div>
                <label class="app-label app-label--required">Название</label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="app-input"
                  placeholder="Название задачи"
                />
              </div>
              <div>
                <label class="app-label">Описание</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="app-input"
                  placeholder="Описание"
                />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="app-label">Статус</label>
                  <AppSelect v-model="form.status" :options="statusOptions" placeholder="Статус" />
                </div>
                <div>
                  <label class="app-label">Приоритет</label>
                  <AppSelect v-model="form.priority" :options="priorityOptions" placeholder="Приоритет" />
                </div>
              </div>
              <div>
                <label class="app-label">Срок выполнения</label>
                <AppDatePicker v-model="form.due_date" placeholder="Выберите срок" />
              </div>
              <div>
                <label class="app-label">Ответственный</label>
                <AppSelect
                  v-model="form.assignee_id"
                  :options="assigneeOptions"
                  placeholder="Выберите"
                />
              </div>
              <div>
                <label class="app-label">Родительская задача</label>
                <AppSelect v-model="form.parent_id" :options="parentTaskOptions" placeholder="Выберите" />
              </div>
              <div>
                <label class="app-label">Цель</label>
                <AppSelect v-model="form.goal_id" :options="goalOptions" placeholder="Выберите" />
              </div>
              <div class="flex gap-3 pt-3">
                <button
                  type="submit"
                  :disabled="saving"
                  class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 text-sm font-medium shadow-sm transition"
                >
                  {{ saving ? 'Сохранение...' : 'Сохранить' }}
                </button>
                <button
                  type="button"
                  @click="closeEditModal"
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

    <!-- Состояние загрузки / ошибки -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <AppSpinner block label="Загрузка задач..." />
    </div>
    <div v-else-if="error" class="flex-1 flex items-center justify-center py-12 text-red-600">
      {{ error }}
    </div>

    <!-- Канбан по статусам на всю ширину -->
    <div v-else class="flex gap-4 flex-1 min-h-0 px-6 pb-4 w-full overflow-x-auto">
      <div
        v-for="column in columns"
        :key="column.id"
        class="kanban-column flex-1 min-w-[16rem] flex flex-col rounded-lg border border-gray-200 bg-gray-100/50 overflow-hidden"
      >
        <div
          class="px-4 py-3 font-medium text-gray-900 border-b border-gray-200"
          :class="column.headerClass"
        >
          {{ column.label }}
          <span v-if="getCount(column.id) > 0" class="ml-2 text-gray-500 text-sm font-normal">
            ({{ getCount(column.id) }})
          </span>
        </div>
        <div
          class="flex-1 p-3 overflow-y-auto min-h-[200px]"
          :class="{ 'ring ring-blue-950/20 ring-inset': dragOverColumn === column.id }"
          @dragover.prevent="dragOverColumn = column.id"
          @dragleave="dragOverColumn = null"
          @drop.prevent="onDrop($event, column.id)"
        >
          <div
            v-for="task in getTasksByStatus(column.id)"
            :key="task.id"
            draggable="true"
            class="bg-white rounded-lg border border-gray-200 p-3 mb-2 shadow-sm hover:shadow cursor-grab active:cursor-grabbing transition relative"
            @dragstart="onDragStart($event, task)"
            @dragend="draggingTaskId = null"
            @click="openViewTask(task)"
          >
            <button
              type="button"
              class="absolute top-2 right-2 p-1 rounded transition-colors cursor-pointer"
              :class="hasReminder('task', task.id) ? 'text-amber-500 hover:text-amber-600' : 'text-gray-400 hover:text-gray-500'"
              :disabled="reminderToggling === `task-${task.id}`"
              :title="hasReminder('task', task.id) ? 'Выключить уведомление' : 'Включить уведомление'"
              @click.stop="onBellClick('task', task)"
            >
              <IconBell />
            </button>
            <div class="font-medium text-gray-900 text-sm truncate pr-8" :title="task.title">
              {{ task.title }}
            </div>
            <div v-if="task.description" class="text-xs text-gray-500 mt-1 line-clamp-2">
              {{ task.description }}
            </div>
          </div>
          <div
            v-if="getTasksByStatus(column.id).length === 0"
            class="text-center text-gray-400 text-sm py-8"
          >
            Нет задач
          </div>
        </div>
      </div>
    </div>
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

// Колонки канбана — соответствуют TaskStatusEnum на бэке. Цвета: Новая = серый, В работе = голубой, Выполнена = зеленый, Закрыта = красный
const columns = [
  { id: 'new', label: 'Новая', headerClass: 'bg-gray-200 text-gray-800' },
  { id: 'in_progress', label: 'В работе', headerClass: 'bg-sky-200 text-sky-900' },
  { id: 'done', label: 'Выполнена', headerClass: 'bg-green-200 text-green-900' },
  { id: 'closed', label: 'Закрыта', headerClass: 'bg-red-200 text-red-900' },
]

const statusOptions = [
  { value: 'new', label: 'Новая' },
  { value: 'in_progress', label: 'В работе' },
  { value: 'done', label: 'Выполнена' },
  { value: 'closed', label: 'Закрыта' },
]

const priorityOptions = [
  { value: 'low', label: 'Низкий' },
  { value: 'medium', label: 'Средний' },
  { value: 'high', label: 'Высокий' },
]

const assigneeOptions = computed(() =>
  usersList.value.map((u) => ({ value: String(u.id), label: `${u.fio} (${u.email})` }))
)

const tasksFiltered = computed(() => {
  const list = tasks.value
  const from = filterDateFrom.value.trim()
  const to = filterDateTo.value.trim()
  if (!from && !to) return list
  return list.filter((t) => {
    const d = t.due_date
    if (!d) return false
    if (from && d < from) return false
    if (to && d > to) return false
    return true
  })
})

const parentTaskOptions = computed(() =>
  tasks.value.map((t) => ({ value: String(t.id), label: t.title }))
)

const goalOptions = computed(() =>
  goalsList.value.map((g) => ({
    value: String(g.id),
    label: g.category_title ? `${g.title} (${g.category_title})` : g.title,
  }))
)

const tasks = ref([])
const usersList = ref([])
const goalsList = ref([])
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const saving = ref(false)
const formError = ref(null)
const viewTask = ref(null)
const editTask = ref(null)
const dragOverColumn = ref(null)
const draggingTaskId = ref(null)
const filterDateFrom = ref('')
const filterDateTo = ref('')
const currentUserId = ref(null)
const reminders = ref([])
const reminderToggling = ref(null)
const reminderSetup = ref(null)

const reminderWhenOptions = [
  { value: '15m', label: 'За 15 минут' },
  { value: '1h', label: 'За 1 час' },
  { value: '3h', label: 'За 3 часа' },
  { value: '1d', label: 'За 1 день' },
  { value: 'day09', label: 'В день срока в 09:00' },
]

const form = reactive({
  title: '',
  description: '',
  status: 'new',
  priority: 'medium',
  due_date: '',
  assignee_id: '',
  parent_id: '',
  goal_id: '',
})

function getTasksByStatus(status) {
  return tasksFiltered.value.filter((t) => t.status === status)
}

function getCount(status) {
  return tasksFiltered.value.filter((t) => t.status === status).length
}

function clearDateFilter() {
  filterDateFrom.value = ''
  filterDateTo.value = ''
}

function getStatusLabel(statusId) {
  return columns.find((c) => c.id === statusId)?.label ?? statusId
}

function getPriorityLabel(priorityId) {
  return priorityOptions.find((p) => p.value === priorityId)?.label ?? priorityId
}

function getParentTitle(parentId) {
  const t = tasks.value.find((x) => x.id === parentId)
  return t ? t.title : `#${parentId}`
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
  } catch {
    return dateStr
  }
}

function formatDateTime(dateStr) {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return dateStr
  }
}

function openViewTask(task) {
  if (draggingTaskId.value !== null) return
  viewTask.value = task
}

function openEditModal(task) {
  viewTask.value = null
  editTask.value = task
  form.title = task.title
  form.description = task.description ?? ''
  form.status = task.status
  form.priority = task.priority ?? 'medium'
  form.due_date = task.due_date ?? ''
  form.assignee_id = task.assignee_id != null ? String(task.assignee_id) : ''
  form.parent_id = task.parent_id != null ? String(task.parent_id) : ''
  form.goal_id = task.goal_id != null ? String(task.goal_id) : ''
  formError.value = null
  fetchUsers()
  fetchGoals()
}

function closeEditModal() {
  editTask.value = null
}

async function submitEdit() {
  if (!editTask.value) return
  saving.value = true
  formError.value = null
  try {
    const payload = {
      title: form.title.trim(),
      description: form.description?.trim() || null,
      status: form.status,
      priority: form.priority,
      due_date: form.due_date || null,
      assignee_id: form.assignee_id ? Number(form.assignee_id) : null,
      parent_id: form.parent_id ? Number(form.parent_id) : null,
      goal_id: form.goal_id ? Number(form.goal_id) : null,
    }
    const response = await apiPatch(API_ENDPOINTS.TASKS.updateUrl(editTask.value.id), payload)
    await handleResponse(response)
    closeEditModal()
    await fetchTasks()
  } catch (e) {
    formError.value = e.message || 'Ошибка при сохранении'
  } finally {
    saving.value = false
  }
}

function onDragStart(e, task) {
  draggingTaskId.value = task.id
  e.dataTransfer.setData('application/json', JSON.stringify({ id: task.id, status: task.status }))
  e.dataTransfer.effectAllowed = 'move'
}

function onDrop(e, newStatus) {
  dragOverColumn.value = null
  draggingTaskId.value = null
  let data
  try {
    data = JSON.parse(e.dataTransfer.getData('application/json'))
  } catch {
    return
  }
  if (!data?.id || data.status === newStatus) return
  updateTaskStatus(data.id, newStatus)
}

async function updateTaskStatus(taskId, status) {
  try {
    const response = await apiPatch(API_ENDPOINTS.TASKS.updateUrl(taskId), { status })
    await handleResponse(response)
    await fetchTasks()
  } catch (err) {
    error.value = err.message || 'Не удалось обновить статус'
  }
}

async function fetchTasks() {
  loading.value = true
  error.value = null
  try {
    const response = await apiGet(API_ENDPOINTS.TASKS.LIST)
    const data = await handleResponse(response)
    tasks.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Ошибка загрузки задач'
    tasks.value = []
  } finally {
    loading.value = false
  }
}

async function fetchUsers() {
  try {
    const response = await apiGet(API_ENDPOINTS.USERS.LIST)
    const data = await handleResponse(response)
    usersList.value = Array.isArray(data) ? data : []
  } catch {
    usersList.value = []
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

function getDeadlineForTask(task) {
  if (!task.due_date) return null
  const str = task.due_date
  if (str.includes('T')) return new Date(str)
  return new Date(str + 'T09:00:00')
}

function notifyAtFromOffsetTask(task, offset) {
  const deadline = getDeadlineForTask(task)
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
    const notify_at = notifyAtFromOffsetTask(entity, offset)
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
  form.status = 'new'
  form.priority = 'medium'
  form.due_date = ''
  form.assignee_id = currentUserId.value != null ? String(currentUserId.value) : ''
  form.parent_id = ''
  form.goal_id = ''
  formError.value = null
  showAddModal.value = true
  fetchUsers()
  fetchGoals()
}

function closeAddModal() {
  showAddModal.value = false
}

async function submitTask() {
  saving.value = true
  formError.value = null
  try {
    const payload = {
      title: form.title.trim(),
      description: form.description?.trim() || null,
      status: form.status,
      priority: form.priority,
      due_date: form.due_date || null,
      assignee_id: form.assignee_id ? Number(form.assignee_id) : null,
      parent_id: form.parent_id ? Number(form.parent_id) : null,
      goal_id: form.goal_id ? Number(form.goal_id) : null,
    }
    const response = await apiPost(API_ENDPOINTS.TASKS.CREATE, payload)
    await handleResponse(response)
    closeAddModal()
    await fetchTasks()
  } catch (e) {
    formError.value = e.message || 'Ошибка при создании задачи'
  } finally {
    saving.value = false
  }
}

async function fetchCurrentUser() {
  try {
    const res = await apiGet(API_ENDPOINTS.USER.GET_CURRENT)
    const data = await handleResponse(res)
    currentUserId.value = data?.id ?? null
  } catch {
    currentUserId.value = null
  }
}

onMounted(() => {
  fetchTasks()
  fetchCurrentUser()
  fetchReminders()
})
</script>
