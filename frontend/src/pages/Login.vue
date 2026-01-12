<template>
  <div class="min-h-screen min-w-screen bg-blue-950 flex flex-col items-center justify-center">
    <div class="bg-gray-300 p-10 rounded-2xl shadow-xl w-full max-w-lg">
      <div class="flex justify-center mb-4">
        <img :src="logoImg" alt="Logo" class="max-w-full h-auto" />
      </div>
      <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">
          {{ isRegisterMode ? 'Регистрация' : 'Вход' }}
        </h2>

        <template v-if="isRegisterMode">
          <input
            v-model="formData.name"
            type="text"
            placeholder="Имя"
            class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
            required
          />
          <input
            v-model="formData.lastName"
            type="text"
            placeholder="Фамилия"
            class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
            required
          />
          <input
            v-model="formData.middleName"
            type="text"
            placeholder="Отчество (необязательно)"
            class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
          />
          <input
            v-model="formData.dateOfBirth"
            type="date"
            placeholder="Дата рождения"
            class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
            required
          />
        </template>

        <input
          v-model="formData.email"
          type="email"
          placeholder="Email"
          class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
          required
        />
        <input
          v-model="formData.password"
          type="password"
          placeholder="Пароль"
          class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
          required
        />

        <button
          type="submit"
          class="bg-blue-950 text-white py-2 rounded-md font-semibold hover:bg-blue-900 transition cursor-pointer"
        >
          {{ isRegisterMode ? 'Зарегистрироваться' : 'Войти' }}
        </button>
        <button
          type="button"
          @click="toggleRegisterMode"
          class="text-blue-950 py-2 rounded-md font-semibold hover:underline transition cursor-pointer"
        >
          {{ isRegisterMode ? 'Уже есть аккаунт? Войти' : 'Нет аккаунта? Зарегистрироваться' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import logoImg from '@/assets/logo.png'
import { apiPost, handleResponse, setToken, setRefreshToken, getToken } from '@/api'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'
import { API_ENDPOINTS } from '@/constants/api'
import { ROUTES } from '@/constants/routes'

const router = useRouter()
const { login: loginAuth } = useAuth()
const { error: showError, success: showSuccess, warning: showWarning } = useNotifications()

const isRegisterMode = ref(false)
const formData = reactive({
  email: '',
  password: '',
  name: '',
  lastName: '',
  middleName: '',
  dateOfBirth: '',
})

const toggleRegisterMode = () => {
  isRegisterMode.value = !isRegisterMode.value
  if (!isRegisterMode.value) {
    resetForm()
  }
}

const resetForm = () => {
  formData.name = ''
  formData.lastName = ''
  formData.middleName = ''
  formData.dateOfBirth = ''
}

const handleSubmit = () => {
  if (isRegisterMode.value) {
    register()
  } else {
    login()
  }
}

const login = async () => {
  try {
    const response = await apiPost(API_ENDPOINTS.AUTH.LOGIN, {
      email: formData.email,
      password: formData.password,
    })

    const data = await handleResponse(response)

    if (data?.token) {
      setToken(data.token)
      if (data.refresh_token) {
        setRefreshToken(data.refresh_token)
      }

      loginAuth(data.token)

      const savedToken = getToken()
      if (!savedToken) {
        showError('Ошибка: не удалось сохранить токен')
        return
      }

      router.replace(ROUTES.HOME).catch(() => {
        window.location.href = ROUTES.HOME
      })
    } else {
      showError('Ошибка: токен не получен')
    }
  } catch (err) {
    showError(err.message || 'Ошибка при входе')
  }
}

const register = async () => {
  if (!isRegisterMode.value) {
    isRegisterMode.value = true
    return
  }

  if (!formData.name || !formData.lastName || !formData.dateOfBirth || !formData.email || !formData.password) {
    showWarning('Заполните все обязательные поля')
    return
  }

  try {
    const dateOfBirthString = formData.dateOfBirth
      ? new Date(formData.dateOfBirth).toISOString().split('T')[0]
      : null

    const response = await apiPost(API_ENDPOINTS.AUTH.REGISTER, {
      name: formData.name,
      last_name: formData.lastName,
      middle_name: formData.middleName || null,
      date_birth: dateOfBirthString,
      email: formData.email,
      password: formData.password,
    })

    await handleResponse(response)
    showSuccess('Регистрация успешна! Теперь вы можете войти.')
    isRegisterMode.value = false
    resetForm()
  } catch (err) {
    showError(err.message || 'Ошибка при регистрации')
  }
}
</script>
