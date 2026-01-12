import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { removeToken, getToken, setToken, refreshAccessToken } from '@/api'
import { ROUTES, ROUTE_NAMES } from '@/constants/routes'
import { parseJwt, isTokenExpired } from '@/utils/jwt'

const userEmail = ref('')
const token = ref(getToken() || null)
const router = useRouter()

async function loadUserFromToken() {
  let currentToken = getToken()
  if (!currentToken) {
    token.value = null
    userEmail.value = ''
    return
  }

  const payload = parseJwt(currentToken)
  if (!payload) {
    userEmail.value = ''
    return
  }

  // Если токен истек, пытаемся обновить его
  if (isTokenExpired(payload)) {
    const newToken = await refreshAccessToken()
    if (newToken) {
      currentToken = newToken
      token.value = newToken
    } else {
      // Если обновление не удалось, выходим
      logout()
      return
    }
  }

  if (token.value !== currentToken) {
    token.value = currentToken
  }

  const updatedPayload = parseJwt(currentToken)
  if (updatedPayload) {
    userEmail.value = updatedPayload.email || ''
  }
}

function logout() {
  removeToken()
  token.value = null
  userEmail.value = ''
  
  // Принудительный редирект на страницу логина
  // Используем window.location для гарантированного редиректа
  window.location.href = ROUTES.LOGIN
}

function login(newToken) {
  setToken(newToken)
  token.value = newToken
  loadUserFromToken()
}

const isAuthenticated = computed(() => !!token.value && !!userEmail.value)

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
