<template>
  <div class="min-h-screen min-w-screen bg-blue-950 flex flex-col items-center justify-center">
    <div class="bg-gray-300 p-10 rounded-2xl shadow-1xl w-full max-w-md">
      <div class="flex justify-center mb-4">
        <img :src="logoImg" alt="Logo" class="max-w-full h-auto" />
      </div>
      <form @submit.prevent="isRegisterMode ? register() : login()" class="flex flex-col gap-4">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">
          {{ isRegisterMode ? 'Регистрация' : 'Вход' }}
        </h2>
        <input
            v-if="isRegisterMode"
            v-model="name"
            type="text"
            placeholder="Имя"
            class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
            required
        />
        <input
            v-model="email"
            type="email"
            placeholder="Email"
            class="bg-white border border-gray-400 text-gray-500 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-950 placeholder-gray-500 transition"
            required
        />
        <input
            v-model="password"
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
            @click="isRegisterMode = !isRegisterMode; name = ''"
            class="text-blue-950 py-2 rounded-md font-semibold hover:underline transition cursor-pointer"
        >
          {{ isRegisterMode ? 'Уже есть аккаунт? Войти' : 'Нет аккаунта? Зарегистрироваться' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import logoImg from '@/assets/logo.png'
import { apiPost, handleResponse, setToken, setRefreshToken } from '@/api'

const router = useRouter()

const email = ref('')
const password = ref('')
const name = ref('')
const isRegisterMode = ref(false)

const login = async () => {
  try {
    const response = await apiPost('/api/auth/login', {
      email: email.value,
      password: password.value
    })

    const data = await handleResponse(response)
    console.log('Успешно вошли:', data)

    if (data && data.token) {
      // Сохраняем токены
      setToken(data.token)
      if (data.refresh_token) {
        setRefreshToken(data.refresh_token)
      }

      // Проверяем, что токен действительно сохранен
      const savedToken = localStorage.getItem('token')
      console.log('Токен сохранен после setToken:', !!savedToken, savedToken ? savedToken.substring(0, 30) + '...' : 'null')

      if (!savedToken) {
        console.error('Критическая ошибка: токен не был сохранен')
        alert('Ошибка: не удалось сохранить токен')
        return
      }

      // Небольшая задержка, чтобы убедиться, что токен точно сохранен
      await nextTick()

      // Проверяем токен еще раз перед редиректом
      const tokenBeforeRedirect = localStorage.getItem('token')
      console.log('Токен перед редиректом:', !!tokenBeforeRedirect)

      if (tokenBeforeRedirect) {
        // Используем router.replace для программной навигации
        // Роутер должен увидеть токен, так как он уже сохранен в localStorage
        console.log('Используем router.replace для перехода на /dashboard')
        router.replace('/dashboard').then(() => {
          console.log('Редирект выполнен успешно через router')
        }).catch((error) => {
          console.error('Ошибка при редиректе через router:', error)
          // Запасной вариант - полная перезагрузка страницы
          window.location.href = '/dashboard'
        })
      } else {
        console.error('Токен исчез после сохранения!')
        alert('Ошибка: токен не был сохранен')
      }
    } else {
      console.error('Токен не найден в ответе:', data)
      alert('Ошибка: токен не получен')
    }
  } catch (err) {
    console.error('Ошибка при входе:', err)
    alert(err.message || 'Ошибка при входе')
  }
}

const register = async () => {
  if (!isRegisterMode.value) {
    isRegisterMode.value = true
    return
  }

  if (!name.value || !email.value || !password.value) {
    alert('Заполните все поля')
    return
  }

  try {
    const response = await apiPost('/api/auth/register', {
      name: name.value,
      email: email.value,
      password: password.value
    })

    await handleResponse(response)
    alert('Регистрация успешна! Теперь вы можете войти.')
    isRegisterMode.value = false
    name.value = ''
  } catch (err) {
    alert(err.message || 'Ошибка при регистрации')
  }
}
</script>
