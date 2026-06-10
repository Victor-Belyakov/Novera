<template>
  <div class="w-full min-w-0 flex flex-col gap-4">
    <div class="flex justify-start">
      <button type="button" @click="openAddModal" class="px-4 py-2 app-btn-primary rounded-md transition-colors text-sm font-medium cursor-pointer flex items-center gap-2">
        <span class="text-lg leading-none">+</span>
        Добавить запись
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="text-sm font-medium text-gray-500">Последний вес</div>
        <div class="mt-2 text-2xl font-bold text-blue-950">{{ latestWeight }}</div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="text-sm font-medium text-gray-500">Последнее давление</div>
        <div class="mt-2 text-2xl font-bold text-blue-950">{{ latestPressure }}</div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Метрика</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Значение</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Комментарий</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading"><td colspan="4" class="px-4 py-8 text-center"><AppSpinner block /></td></tr>
            <tr v-else-if="error"><td colspan="4" class="px-4 py-8 text-left text-red-600">{{ error }}</td></tr>
            <tr v-else-if="!entries.length"><td colspan="4" class="px-4 py-8 text-left text-gray-500">Нет записей здоровья</td></tr>
            <tr v-else v-for="entry in entries" :key="entry.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-900">{{ formatDateTime(entry.recorded_at) }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ entry.metric_type_title }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ displayValue(entry) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ entry.note || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeAddModal">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between rounded-t-xl shrink-0 app-modal-header">
            <h3 class="text-lg font-semibold">Новая запись здоровья</h3>
            <button type="button" @click="closeAddModal" class="p-2 rounded-lg hover:bg-white/80 text-blue-700 transition cursor-pointer" aria-label="Закрыть">✕</button>
          </div>
          <form @submit.prevent="submitEntry" class="p-6 space-y-5">
            <div>
              <label class="app-label app-label--required">Метрика</label>
              <AppSelect v-model="form.metric_type_id" :options="metricOptions" placeholder="Выберите метрику" />
            </div>
            <div>
              <label class="app-label">Дата записи</label>
              <AppDatePicker v-model="form.recorded_at" placeholder="Выберите дату" />
            </div>
            <div v-if="selectedMetric?.value_kind === 'text'">
              <label class="app-label app-label--required">Текстовое значение</label>
              <textarea v-model="form.value_text" rows="3" class="app-input" placeholder="Результат анализа" />
            </div>
            <div v-else>
              <label class="app-label app-label--required">Числовое значение</label>
              <input v-model="form.value_number" type="number" step="0.01" class="app-input" :placeholder="selectedMetric?.unit || 'Введите значение'" />
            </div>
            <div>
              <label class="app-label">Комментарий</label>
              <textarea v-model="form.note" rows="3" class="app-input" placeholder="Комментарий" />
            </div>
            <div class="flex gap-3 pt-3">
              <button type="submit" :disabled="saving" class="px-5 py-2.5 app-btn-primary rounded-lg disabled:opacity-50 text-sm font-medium transition">{{ saving ? 'Сохранение...' : 'Создать' }}</button>
              <button type="button" @click="closeAddModal" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition">Отмена</button>
            </div>
            <p v-if="formError" class="text-sm text-red-600">{{ formError }}</p>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { apiGet, apiPost, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import AppSpinner from '@/components/AppSpinner.vue'
import AppSelect from '@/components/AppSelect.vue'
import AppDatePicker from '@/components/AppDatePicker.vue'

const entries = ref([])
const metricTypes = ref([])
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const saving = ref(false)
const formError = ref(null)

const form = reactive({
  metric_type_id: null,
  recorded_at: '',
  value_number: '',
  value_text: '',
  note: '',
})

const metricOptions = computed(() => metricTypes.value.map((item) => ({
  value: item.id,
  label: item.unit ? `${item.title} (${item.unit})` : item.title,
})))

const selectedMetric = computed(() => metricTypes.value.find((item) => item.id === form.metric_type_id) || null)

const latestWeight = computed(() => {
  const entry = entries.value.find((item) => item.metric_type_slug === 'weight')
  return entry ? displayValue(entry) : '—'
})

const latestPressure = computed(() => {
  const systolic = entries.value.find((item) => item.metric_type_slug === 'blood_pressure_systolic')
  const diastolic = entries.value.find((item) => item.metric_type_slug === 'blood_pressure_diastolic')
  if (!systolic && !diastolic) return '—'
  return `${systolic?.value_number || '—'} / ${diastolic?.value_number || '—'}`
})

function displayValue(entry) {
  if (entry.value_kind === 'text') return entry.value_text || '—'
  return entry.value_number ? `${Number(entry.value_number).toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}${entry.unit ? ` ${entry.unit}` : ''}` : '—'
}

function formatDateTime(value) {
  if (!value) return '—'
  try {
    return new Date(value).toLocaleString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return value
  }
}

async function fetchEntries() {
  loading.value = true
  error.value = null
  try {
    const response = await apiGet(API_ENDPOINTS.HEALTH_METRICS.LIST)
    const data = await handleResponse(response)
    entries.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Ошибка загрузки здоровья'
    entries.value = []
  } finally {
    loading.value = false
  }
}

async function fetchMetricTypes() {
  try {
    const response = await apiGet(API_ENDPOINTS.HEALTH_METRIC_TYPES.ACTIVE_LIST)
    const data = await handleResponse(response)
    metricTypes.value = Array.isArray(data) ? data : []
  } catch {
    metricTypes.value = []
  }
}

function openAddModal() {
  form.metric_type_id = null
  form.recorded_at = ''
  form.value_number = ''
  form.value_text = ''
  form.note = ''
  formError.value = null
  showAddModal.value = true
}

function closeAddModal() {
  showAddModal.value = false
}

async function submitEntry() {
  saving.value = true
  formError.value = null
  try {
    const payload = {
      metric_type_id: form.metric_type_id,
      recorded_at: form.recorded_at || '',
      value_number: selectedMetric.value?.value_kind === 'number' ? form.value_number : null,
      value_text: selectedMetric.value?.value_kind === 'text' ? form.value_text?.trim() || null : null,
      note: form.note?.trim() || null,
    }
    const response = await apiPost(API_ENDPOINTS.HEALTH_METRICS.CREATE, payload)
    await handleResponse(response)
    closeAddModal()
    await fetchEntries()
  } catch (e) {
    formError.value = e.message || 'Ошибка при создании записи'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await Promise.all([fetchEntries(), fetchMetricTypes()])
})
</script>
