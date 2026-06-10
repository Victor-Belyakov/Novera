<template>
  <div class="w-full min-w-0 flex flex-col gap-4">
    <div class="flex justify-start">
      <button type="button" @click="openAddModal" class="px-4 py-2 app-btn-primary rounded-md transition-colors text-sm font-medium cursor-pointer flex items-center gap-2">
        <span class="text-lg leading-none">+</span>
        Добавить
      </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип значения</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Единица</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading"><td colspan="4" class="px-4 py-8 text-center"><AppSpinner block /></td></tr>
            <tr v-else-if="error"><td colspan="4" class="px-4 py-8 text-left text-red-600">{{ error }}</td></tr>
            <tr v-else-if="!items.length"><td colspan="4" class="px-4 py-8 text-left text-gray-500">Нет метрик</td></tr>
            <tr v-else v-for="item in items" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-900">{{ item.title }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ item.slug }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ valueKindLabel(item.value_kind) }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ item.unit || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeAddModal">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between rounded-t-xl shrink-0 app-modal-header">
            <h3 class="text-lg font-semibold">Новая метрика здоровья</h3>
            <button type="button" @click="closeAddModal" class="p-2 rounded-lg hover:bg-white/80 text-blue-700 transition cursor-pointer" aria-label="Закрыть">✕</button>
          </div>
          <form @submit.prevent="submitItem" class="p-6 space-y-5">
            <div>
              <label class="app-label app-label--required">Название</label>
              <input v-model="form.title" type="text" required class="app-input" placeholder="Вес" />
            </div>
            <div>
              <label class="app-label app-label--required">Slug</label>
              <input v-model="form.slug" type="text" required class="app-input" placeholder="weight" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="app-label app-label--required">Тип значения</label>
                <AppSelect v-model="form.value_kind" :options="valueKindOptions" placeholder="Выберите" />
              </div>
              <div>
                <label class="app-label">Единица</label>
                <input v-model="form.unit" type="text" class="app-input" placeholder="кг" />
              </div>
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
import { onMounted, reactive, ref } from 'vue'
import { apiGet, apiPost, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import AppSpinner from '@/components/AppSpinner.vue'
import AppSelect from '@/components/AppSelect.vue'

const items = ref([])
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const saving = ref(false)
const formError = ref(null)

const form = reactive({ title: '', slug: '', value_kind: 'number', unit: '' })

const valueKindOptions = [
  { value: 'number', label: 'Число' },
  { value: 'text', label: 'Текст' },
]

function valueKindLabel(kind) {
  return kind === 'text' ? 'Текст' : 'Число'
}

async function fetchItems() {
  loading.value = true
  error.value = null
  try {
    const response = await apiGet(API_ENDPOINTS.HEALTH_METRIC_TYPES.LIST)
    const data = await handleResponse(response)
    items.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Ошибка загрузки метрик'
    items.value = []
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  form.title = ''
  form.slug = ''
  form.value_kind = 'number'
  form.unit = ''
  formError.value = null
  showAddModal.value = true
}

function closeAddModal() {
  showAddModal.value = false
}

async function submitItem() {
  saving.value = true
  formError.value = null
  try {
    const response = await apiPost(API_ENDPOINTS.HEALTH_METRIC_TYPES.CREATE, {
      title: form.title.trim(),
      slug: form.slug.trim(),
      value_kind: form.value_kind,
      unit: form.unit.trim() || null,
    })
    await handleResponse(response)
    closeAddModal()
    await fetchItems()
  } catch (e) {
    formError.value = e.message || 'Ошибка при создании метрики'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchItems()
})
</script>
