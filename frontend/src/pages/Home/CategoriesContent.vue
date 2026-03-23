<template>
  <div class="w-full min-w-0 flex flex-col gap-4">
    <div class="flex justify-start">
      <button
        type="button"
        @click="openAddModal"
        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-sm font-medium cursor-pointer flex items-center gap-2"
      >
        <span class="text-lg leading-none">+</span>
        Добавить
      </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200 border-collapse">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата создания</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading">
              <td colspan="3" class="px-4 py-8 text-center">
                <AppSpinner block />
              </td>
            </tr>
            <tr v-else-if="error">
              <td colspan="3" class="px-4 py-8 text-left text-red-600">{{ error }}</td>
            </tr>
            <tr v-else-if="!categories.length">
              <td colspan="3" class="px-4 py-8 text-left text-gray-500">Нет категорий</td>
            </tr>
            <tr v-else v-for="c in categories" :key="c.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ c.id }}</td>
              <td class="px-4 py-3 text-sm text-gray-900">{{ c.title }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(c.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Модальное окно: новая категория -->
    <Teleport to="body">
      <div
        v-if="showAddModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="closeAddModal"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-100 flex flex-col">
          <div class="p-6 flex items-center justify-between bg-blue-950 rounded-t-xl shrink-0">
            <h3 class="text-lg font-semibold text-white">Новая категория</h3>
            <button
              type="button"
              @click="closeAddModal"
              class="p-2 rounded-lg hover:bg-white/20 text-white transition cursor-pointer"
              aria-label="Закрыть"
            >
              ✕
            </button>
          </div>
          <form @submit.prevent="submitCategory" class="p-6 space-y-5">
            <div>
              <label class="app-label app-label--required">Название</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="app-input"
                placeholder="Название категории"
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
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { apiGet, apiPost, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import AppSpinner from '@/components/AppSpinner.vue'

const categories = ref([])
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const saving = ref(false)
const formError = ref(null)

const form = reactive({
  title: '',
})

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

async function fetchCategories() {
  loading.value = true
  error.value = null
  try {
    const response = await apiGet(API_ENDPOINTS.CATEGORIES.LIST)
    const data = await handleResponse(response)
    categories.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Ошибка загрузки категорий'
    categories.value = []
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  form.title = ''
  formError.value = null
  showAddModal.value = true
}

function closeAddModal() {
  showAddModal.value = false
}

async function submitCategory() {
  saving.value = true
  formError.value = null
  try {
    const payload = { title: form.title.trim() }
    const response = await apiPost(API_ENDPOINTS.CATEGORIES.CREATE, payload)
    await handleResponse(response)
    closeAddModal()
    await fetchCategories()
  } catch (e) {
    formError.value = e.message || 'Ошибка при создании категории'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchCategories()
})
</script>
