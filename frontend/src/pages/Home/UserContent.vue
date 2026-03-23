<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Заголовок с аватаром -->
    <div class="bg-gradient-to-r from-blue-950 to-blue-800 px-8 py-12">
      <div class="flex items-center gap-6">
        <!-- Аватар -->
        <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm border-4 border-white/30 flex items-center justify-center text-white text-4xl font-bold shadow-lg">
          {{ getInitials() }}
        </div>

        <!-- Информация о пользователе -->
        <div class="text-white" v-if="user">
          <h2 class="text-3xl font-bold mb-1">
            {{ getUserFullName() }}
          </h2>
          <p class="text-blue-100 text-lg">{{ user.email }}</p>
        </div>

        <div v-else-if="loading" class="text-white flex items-center gap-3">
          <AppSpinner size="sm" />
          <span class="text-blue-100">Загрузка...</span>
        </div>
      </div>
    </div>

    <!-- Контент -->
    <div class="p-8">
      <div v-if="loading" class="flex justify-center py-12">
        <AppSpinner block label="Загрузка профиля..." />
      </div>

      <div v-else-if="error" class="text-center py-8">
        <div class="text-red-500 text-lg mb-2">⚠️</div>
        <p class="text-red-600 font-medium">{{ error }}</p>
        <button
          @click="loadUserData"
          class="mt-4 px-4 py-2 bg-blue-950 text-white rounded-md hover:bg-blue-900 transition-colors"
        >
          Попробовать снова
        </button>
      </div>

      <div v-else-if="user">
        <!-- Кнопка Редактировать / Сохранить и Отмена -->
        <div class="flex gap-3 mb-6">
          <button
            v-if="!isEditing"
            @click="startEditing"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium cursor-pointer"
          >
            Редактировать
          </button>
          <template v-else>
            <button
              @click="saveProfile"
              :disabled="saving"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
            >
              {{ saving ? 'Сохранение...' : 'Сохранить' }}
            </button>
            <button
              @click="cancelEditing"
              :disabled="saving"
              class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors font-medium disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
            >
              Отмена
            </button>
          </template>
        </div>

        <!-- Режим просмотра -->
        <div v-if="!isEditing" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">ID</div>
            <div class="text-xl font-semibold text-gray-900">{{ user.id }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">Email</div>
            <div class="text-xl font-semibold text-gray-900 break-all">{{ user.email }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">Имя</div>
            <div class="text-xl font-semibold text-gray-900">{{ user.name }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">Фамилия</div>
            <div class="text-xl font-semibold text-gray-900">{{ user.last_name }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">Отчество</div>
            <div class="text-xl font-semibold text-gray-900">{{ user.middle_name || '—' }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">Дата рождения</div>
            <div class="text-xl font-semibold text-gray-900">{{ formatDate(user.date_of_birth) }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">Телефон</div>
            <div class="text-xl font-semibold text-gray-900">{{ formatPhoneMask(user.phone) || '—' }}</div>
          </div>
          <div v-if="user.telegram_id" class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="text-sm text-gray-500 font-medium mb-2">Telegram ID</div>
            <div class="text-xl font-semibold text-gray-900">{{ user.telegram_id }}</div>
          </div>
        </div>

        <!-- Режим редактирования -->
        <form v-else-if="editSnapshot" @submit.prevent="saveProfile" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">ID</label>
            <div class="text-xl font-semibold text-gray-500">{{ user.id }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">Email</label>
            <input
              v-model="editSnapshot.email"
              type="email"
              class="app-input"
            />
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">Имя</label>
            <input
              v-model="editSnapshot.name"
              type="text"
              class="app-input"
            />
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">Фамилия</label>
            <input
              v-model="editSnapshot.last_name"
              type="text"
              class="app-input"
            />
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">Отчество</label>
            <input
              v-model="editSnapshot.middle_name"
              type="text"
              placeholder="Необязательно"
              class="app-input"
            />
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">Дата рождения</label>
            <AppDatePicker
              v-model="editSnapshot.date_of_birth"
              :show-time="false"
              placeholder="Выберите дату"
            />
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">Телефон</label>
            <input
              :value="editSnapshot.phone"
              @input="onPhoneInput"
              type="tel"
              placeholder="+7 (999) 123-45-67"
              class="app-input"
            />
          </div>
          <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <label class="text-sm text-gray-500 font-medium mb-2 block">Telegram ID</label>
            <input
              v-model="editSnapshot.telegram_id"
              type="text"
              placeholder="Необязательно"
              class="app-input"
            />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { apiGet, apiPatch, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import { useNotifications } from '@/composables/useNotifications'
import AppDatePicker from '@/components/AppDatePicker.vue'
import AppSpinner from '@/components/AppSpinner.vue'

const { success: showSuccess, error: showError } = useNotifications()

const user = ref(null)
const loading = ref(true)
const error = ref(null)
const isEditing = ref(false)
const saving = ref(false)

/** Снимок данных для формы редактирования — создаётся при входе в режим редактирования */
const editSnapshot = ref(null)

const loadUserData = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await apiGet(API_ENDPOINTS.USER.GET_CURRENT)
    const data = await handleResponse(response)

    user.value = data
  } catch (err) {
    error.value = err.message || 'Ошибка при загрузке данных пользователя'
    console.error('Error loading user data:', err)
  } finally {
    loading.value = false
  }
}

const formatPhoneMask = (val) => {
  let digits = (val || '').replace(/\D/g, '')
  if (digits.startsWith('8')) digits = '7' + digits.slice(1)
  if (digits && !digits.startsWith('7')) digits = '7' + digits
  digits = digits.slice(0, 11)
  if (!digits) return ''
  let s = '+7'
  if (digits.length > 1) s += ' (' + digits.slice(1, 4)
  if (digits.length >= 4) s += ') ' + digits.slice(4, 7)
  if (digits.length >= 7) s += '-' + digits.slice(7, 9)
  if (digits.length >= 9) s += '-' + digits.slice(9, 11)
  return s
}

const onPhoneInput = (e) => {
  if (!editSnapshot.value) return
  editSnapshot.value.phone = formatPhoneMask(e.target.value)
}

const startEditing = () => {
  if (!user.value) return
  const u = user.value
  editSnapshot.value = reactive({
    email: u.email ?? '',
    phone: formatPhoneMask(u.phone ?? ''),
    name: u.name ?? '',
    last_name: u.last_name ?? '',
    middle_name: u.middle_name ?? '',
    date_of_birth: u.date_of_birth ?? '',
    telegram_id: u.telegram_id ?? '',
  })
  isEditing.value = true
}

const cancelEditing = () => {
  isEditing.value = false
  editSnapshot.value = null
}

const saveProfile = async () => {
  if (!user.value || !editSnapshot.value || saving.value) return
  try {
    saving.value = true
    const response = await apiPatch(API_ENDPOINTS.USER.GET_CURRENT, {
      email: editSnapshot.value.email,
      phone: (editSnapshot.value.phone || '').replace(/\D/g, '') || null,
      name: editSnapshot.value.name,
      last_name: editSnapshot.value.last_name,
      middle_name: editSnapshot.value.middle_name || null,
      date_of_birth: editSnapshot.value.date_of_birth,
      telegram_id: editSnapshot.value.telegram_id?.trim() || null,
    })
    const data = await handleResponse(response)
    user.value = data
    isEditing.value = false
    editSnapshot.value = null
    showSuccess('Профиль сохранён')
  } catch (err) {
    showError(err.message || 'Ошибка при сохранении')
    console.error('Error saving profile:', err)
  } finally {
    saving.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'

  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch {
    return dateString
  }
}

const getInitials = () => {
  if (!user.value) return '?'

  const name = user.value.name || ''
  const lastName = user.value.last_name || ''

  if (name && lastName) {
    return (name.charAt(0) + lastName.charAt(0)).toUpperCase()
  }

  if (name) {
    return name.charAt(0).toUpperCase()
  }

  if (user.value.email) {
    return user.value.email.charAt(0).toUpperCase()
  }

  return '?'
}

const getUserFullName = () => {
  if (!user.value) return 'Пользователь'

  const parts = [
    user.value.last_name,
    user.value.name,
    user.value.middle_name
  ].filter(Boolean)

  return parts.join(' ') || user.value.email || 'Пользователь'
}

onMounted(() => {
  loadUserData()
})
</script>
