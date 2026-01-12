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

        <div v-else-if="loading" class="text-white">
          <div class="h-8 w-48 bg-white/20 rounded animate-pulse mb-2"></div>
          <div class="h-5 w-64 bg-white/20 rounded animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- Контент -->
    <div class="p-8">
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 5" :key="i" class="h-16 bg-gray-100 rounded-lg animate-pulse"></div>
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

      <div v-else-if="user" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- ID -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
          <div class="text-sm text-gray-500 font-medium mb-2">ID</div>
          <div class="text-xl font-semibold text-gray-900">{{ user.id }}</div>
        </div>

        <!-- Email -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
          <div class="text-sm text-gray-500 font-medium mb-2">Email</div>
          <div class="text-xl font-semibold text-gray-900 break-all">{{ user.email }}</div>
        </div>

        <!-- Имя -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
          <div class="text-sm text-gray-500 font-medium mb-2">Имя</div>
          <div class="text-xl font-semibold text-gray-900">{{ user.name }}</div>
        </div>

        <!-- Фамилия -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
          <div class="text-sm text-gray-500 font-medium mb-2">Фамилия</div>
          <div class="text-xl font-semibold text-gray-900">{{ user.last_name }}</div>
        </div>

        <!-- Отчество -->
        <div v-if="user.middle_name" class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
          <div class="text-sm text-gray-500 font-medium mb-2">Отчество</div>
          <div class="text-xl font-semibold text-gray-900">{{ user.middle_name }}</div>
        </div>

        <!-- Дата рождения -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 hover:border-blue-300 transition-colors">
          <div class="text-sm text-gray-500 font-medium mb-2">Дата рождения</div>
          <div class="text-xl font-semibold text-gray-900">{{ formatDate(user.date_of_birth) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiGet, handleResponse } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'

const user = ref(null)
const loading = ref(true)
const error = ref(null)

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
