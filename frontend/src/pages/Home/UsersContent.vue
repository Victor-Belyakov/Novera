<template>
  <div class="w-full min-w-0 bg-white rounded-lg shadow overflow-hidden">
    <!-- Фильтры -->
    <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-wrap gap-3 items-start">
      <div class="w-48">
        <label class="app-label">ФИО</label>
        <input
          v-model="filters.fio"
          type="text"
          placeholder="Поиск по ФИО (от 3 символов)"
          class="app-input"
          @input="debouncedFetch"
        />
      </div>
      <div class="w-48">
        <label class="app-label">Телефон</label>
        <input
          v-model="filters.phone"
          type="text"
          placeholder="Поиск по номеру (от 3 символов)"
          class="app-input"
          @input="debouncedFetch"
        />
      </div>
      <div class="flex flex-col">
        <span class="block text-sm mb-1 h-5 leading-5 invisible select-none">Место</span>
        <button
          type="button"
          @click="resetFilters"
          class="h-10 px-4 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors text-sm font-medium cursor-pointer flex items-center shrink-0"
        >
          Сбросить
        </button>
      </div>
    </div>

    <!-- Таблица -->
    <div class="overflow-x-auto w-full users-table-wrap">
      <table class="w-full divide-y divide-gray-200 border-collapse">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="users-table-cell py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
              :class="{ 'text-blue-950': sortBy === 'id' }"
              @click="sortByColumn('id')"
            >
              ID {{ sortBy === 'id' ? (sortOrder === 'ASC' ? '↑' : '↓') : '' }}
            </th>
            <th
              scope="col"
              class="users-table-cell py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
              :class="{ 'text-blue-950': sortBy === 'fio' }"
              @click="sortByColumn('fio')"
            >
              ФИО {{ sortBy === 'fio' ? (sortOrder === 'ASC' ? '↑' : '↓') : '' }}
            </th>
            <th
              scope="col"
              class="users-table-cell py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
              :class="{ 'text-blue-950': sortBy === 'email' }"
              @click="sortByColumn('email')"
            >
              Email {{ sortBy === 'email' ? (sortOrder === 'ASC' ? '↑' : '↓') : '' }}
            </th>
            <th
              scope="col"
              class="users-table-cell py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
              :class="{ 'text-blue-950': sortBy === 'phone' }"
              @click="sortByColumn('phone')"
            >
              Телефон {{ sortBy === 'phone' ? (sortOrder === 'ASC' ? '↑' : '↓') : '' }}
            </th>
            <th
              scope="col"
              class="users-table-cell py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100"
              :class="{ 'text-blue-950': sortBy === 'created_at' }"
              @click="sortByColumn('created_at')"
            >
              Дата создания {{ sortBy === 'created_at' ? (sortOrder === 'ASC' ? '↑' : '↓') : '' }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="5" class="users-table-cell py-8 text-center">
              <AppSpinner block />
            </td>
          </tr>
          <tr v-else-if="error">
            <td colspan="5" class="users-table-cell py-8 text-left text-red-600">
              {{ error }}
            </td>
          </tr>
          <tr v-else-if="!users.length">
            <td colspan="5" class="users-table-cell py-8 text-left text-gray-500">
              Нет пользователей
            </td>
          </tr>
          <tr
            v-else
            v-for="u in users"
            :key="u.id"
            class="hover:bg-gray-50 cursor-pointer"
            @click="openUserModal(u)"
          >
            <td class="users-table-cell py-3 whitespace-nowrap text-sm text-gray-900 text-left">{{ u.id }}</td>
            <td class="users-table-cell py-3 text-sm text-gray-900 text-left">{{ u.fio }}</td>
            <td class="users-table-cell py-3 text-sm text-gray-900 text-left">{{ u.email }}</td>
            <td class="users-table-cell py-3 text-sm text-gray-900 text-left">{{ formatPhone(u.phone) }}</td>
            <td class="users-table-cell py-3 whitespace-nowrap text-sm text-gray-900 text-left">{{ formatDate(u.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Модалка: информация о пользователе -->
    <Teleport to="body">
      <div
        v-if="userModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click.self="userModal = null"
      >
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full border border-gray-100 overflow-hidden">
          <div class="p-6 flex items-center justify-between bg-blue-950 rounded-t-xl shrink-0">
            <h3 class="text-lg font-semibold text-white">Пользователь</h3>
            <button
              type="button"
              @click="userModal = null"
              class="p-2 rounded-lg hover:bg-white/20 text-white transition cursor-pointer"
              aria-label="Закрыть"
            >
              ✕
            </button>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <span class="text-sm text-gray-500 block mb-0.5">ID</span>
              <span class="text-gray-900">{{ userModal.id }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-500 block mb-0.5">ФИО</span>
              <span class="text-gray-900">{{ userModal.fio }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-500 block mb-0.5">Email</span>
              <span class="text-gray-900">{{ userModal.email }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-500 block mb-0.5">Телефон</span>
              <span class="text-gray-900">{{ formatPhone(userModal.phone) }}</span>
            </div>
            <div v-if="userModal.telegram_id">
              <span class="text-sm text-gray-500 block mb-0.5">Telegram ID</span>
              <span class="text-gray-900">{{ userModal.telegram_id }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-500 block mb-0.5">Дата создания</span>
              <span class="text-gray-900">{{ formatDate(userModal.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { apiGet, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import AppSpinner from '@/components/AppSpinner.vue'

const users = ref([])
const loading = ref(false)
const error = ref(null)
const userModal = ref(null)

const filters = reactive({
  fio: '',
  phone: '',
})

const sortBy = ref('id')
const sortOrder = ref('ASC')

const MIN_FILTER_LENGTH = 3
let debounceTimer = null

function shouldFetchWithFilters() {
  const fio = filters.fio.trim()
  const phone = filters.phone.trim()
  const bothEmpty = fio === '' && phone === ''
  const hasEnoughFio = fio.length >= MIN_FILTER_LENGTH
  const hasEnoughPhone = phone.length >= MIN_FILTER_LENGTH
  return bothEmpty || hasEnoughFio || hasEnoughPhone
}

function debouncedFetch() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    if (shouldFetchWithFilters()) {
      fetchUsers()
    }
  }, 300)
}

function resetFilters() {
  filters.fio = ''
  filters.phone = ''
  fetchUsers()
}

function sortByColumn(column) {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'ASC' ? 'DESC' : 'ASC'
  } else {
    sortBy.value = column
    sortOrder.value = 'ASC'
  }
  fetchUsers()
}

async function fetchUsers() {
  loading.value = true
  error.value = null
  try {
    const params = new URLSearchParams()
    const fio = filters.fio.trim()
    const phone = filters.phone.trim()
    if (fio.length >= MIN_FILTER_LENGTH) params.set('fio', fio)
    if (phone.length >= MIN_FILTER_LENGTH) params.set('phone', phone)
    params.set('sort_by', sortBy.value)
    params.set('sort_order', sortOrder.value)
    const query = params.toString()
    const url = query ? `${API_ENDPOINTS.USERS.LIST}?${query}` : API_ENDPOINTS.USERS.LIST
    const response = await apiGet(url)
    const data = await handleResponse(response)
    users.value = Array.isArray(data) ? data : []
  } catch (e) {
    error.value = e.message || 'Ошибка загрузки списка пользователей'
    users.value = []
  } finally {
    loading.value = false
  }
}

function openUserModal(u) {
  userModal.value = u
}

function formatPhone(phone) {
  if (!phone) return '—'
  const digits = phone.replace(/\D/g, '')
  if (digits.length === 11 && digits.startsWith('7')) {
    return `+7 (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9)}`
  }
  return phone
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.users-table-wrap th.users-table-cell,
.users-table-wrap td.users-table-cell {
  padding-left: 1rem;
  padding-right: 1rem;
  box-sizing: border-box;
  text-align: left !important;
}
</style>
