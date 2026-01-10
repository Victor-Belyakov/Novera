import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { removeToken, hasToken } from '@/api'

const userEmail = ref('')
const token = ref(localStorage.getItem('token') || null)
const router = useRouter()

function parseJwt(jwt) {
    try {
        const payload = jwt.split('.')[1]
        return JSON.parse(atob(payload))
    } catch (e) {
        return null
    }
}

// Обновление данных пользователя из токена
function loadUserFromToken() {
    if (!token.value) return
    const payload = parseJwt(token.value)
    if (!payload) return

    const now = Math.floor(Date.now() / 1000)
    if (payload.exp && payload.exp < now) {
        logout()
        return
    }

    userEmail.value = payload.email || ''
}

// Функция выхода
function logout() {
    removeToken()
    localStorage.removeItem('token')
    token.value = null
    userEmail.value = ''
    router.push({ name: 'Home' }) // редирект на страницу авторизации
}

// Функция логина: записываем токен и обновляем reactive данные
function login(newToken) {
    localStorage.setItem('token', newToken)
    token.value = newToken
    loadUserFromToken() // обновляем userEmail сразу
}

const isAuthenticated = computed(() => !!token.value && !!userEmail.value)

// Инициализация при старте
loadUserFromToken()

export function useAuth() {
    return {
        userEmail,
        token,
        isAuthenticated,
        logout,
        login,
        loadUserFromToken
    }
}
