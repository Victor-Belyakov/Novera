import { STORAGE_KEYS, API_ENDPOINTS } from '@/constants/api'

const API_URL = import.meta.env.VITE_API_URL || ''
const PUBLIC_ENDPOINTS = ['/auth/login', '/auth/register']

// Флаг для предотвращения множественных попыток обновления токена
let isRefreshing = false
let refreshPromise = null

/**
 * Получает токен из localStorage
 */
export const getToken = () => {
  return localStorage.getItem(STORAGE_KEYS.TOKEN)
}

/**
 * Сохраняет токен в localStorage
 */
export const setToken = (token) => {
  if (token) {
    localStorage.setItem(STORAGE_KEYS.TOKEN, token)
  } else {
    localStorage.removeItem(STORAGE_KEYS.TOKEN)
  }
}

/**
 * Сохраняет refresh_token в localStorage
 */
export const setRefreshToken = (refreshToken) => {
  if (refreshToken) {
    localStorage.setItem(STORAGE_KEYS.REFRESH_TOKEN, refreshToken)
  } else {
    localStorage.removeItem(STORAGE_KEYS.REFRESH_TOKEN)
  }
}

/**
 * Удаляет токен из localStorage (logout)
 */
export const removeToken = () => {
  localStorage.removeItem(STORAGE_KEYS.TOKEN)
  localStorage.removeItem(STORAGE_KEYS.REFRESH_TOKEN)
}

/**
 * Получает refresh token из localStorage
 */
export const getRefreshToken = () => {
  return localStorage.getItem(STORAGE_KEYS.REFRESH_TOKEN)
}

/**
 * Проверяет, есть ли токен
 */
export const hasToken = () => {
  return !!getToken()
}

/**
 * Обновляет токен через refresh token
 * @returns {Promise<string|null>} - Новый токен или null при ошибке
 */
export const refreshAccessToken = async () => {
  // Если уже идет обновление, возвращаем существующий промис
  if (isRefreshing && refreshPromise) {
    return refreshPromise
  }

  const refreshToken = getRefreshToken()
  if (!refreshToken) {
    return null
  }

  isRefreshing = true
  refreshPromise = (async () => {
    try {
      const response = await fetch(`${API_URL}${API_ENDPOINTS.AUTH.REFRESH}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          refresh_token: refreshToken,
        }),
      })

      if (!response.ok) {
        throw new Error('Failed to refresh token')
      }

      const data = await response.json()

      if (data.token) {
        setToken(data.token)
        if (data.refresh_token) {
          setRefreshToken(data.refresh_token)
        }
        return data.token
      }

      return null
    } catch (error) {
      console.error('Ошибка при обновлении токена:', error)
      removeToken()
      return null
    } finally {
      isRefreshing = false
      refreshPromise = null
    }
  })()

  return refreshPromise
}

/**
 * Выполняет API запрос с автоматическим добавлением токена
 * @param {string} endpoint - API endpoint (например: '/api/auth/login')
 * @param {object} options - Опции для fetch (method, body, headers и т.д.)
 * @param {boolean} retry - Флаг для повторной попытки после обновления токена
 * @returns {Promise<Response>}
 */
export const apiRequest = async (endpoint, options = {}, retry = true) => {
  const token = getToken()

  // Подготавливаем заголовки
  const headers = {
    'Content-Type': 'application/json',
    ...options.headers,
  }

  // Добавляем токен в заголовки, если он есть (кроме публичных эндпоинтов)
  const isPublicEndpoint = PUBLIC_ENDPOINTS.some(publicPath => endpoint.includes(publicPath))
  const currentToken = token || getToken()
  if (currentToken && !isPublicEndpoint) {
    headers['Authorization'] = `Bearer ${currentToken}`
  }

  // Выполняем запрос
  const response = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers,
  })

  // Если токен истек или недействителен, пытаемся обновить его
  if (response.status === 401 && currentToken && !isPublicEndpoint && retry) {
    const newToken = await refreshAccessToken()
    
    // Если токен обновлен, повторяем запрос
    if (newToken) {
      return apiRequest(endpoint, options, false)
    }
    
    // Если обновление не удалось, редиректим на логин
    removeToken()
    if (window.location.pathname !== '/') {
      window.location.href = '/'
    }
    return response
  }

  // Если токен истек и это публичный эндпоинт или повторная попытка не помогла
  if (response.status === 401 && currentToken && !isPublicEndpoint && !retry) {
    removeToken()
    if (window.location.pathname !== '/') {
      window.location.href = '/'
    }
  }

  return response
}

/**
 * GET запрос
 */
export const apiGet = async (endpoint, options = {}) => {
  return apiRequest(endpoint, {
    ...options,
    method: 'GET',
  })
}

/**
 * POST запрос
 */
export const apiPost = async (endpoint, data = null, options = {}) => {
  return apiRequest(endpoint, {
    ...options,
    method: 'POST',
    body: data ? JSON.stringify(data) : null,
  })
}

/**
 * PUT запрос
 */
export const apiPut = async (endpoint, data = null, options = {}) => {
  return apiRequest(endpoint, {
    ...options,
    method: 'PUT',
    body: data ? JSON.stringify(data) : null,
  })
}

/**
 * PATCH запрос
 */
export const apiPatch = async (endpoint, data = null, options = {}) => {
  return apiRequest(endpoint, {
    ...options,
    method: 'PATCH',
    body: data ? JSON.stringify(data) : null,
  })
}

/**
 * DELETE запрос
 */
export const apiDelete = async (endpoint, options = {}) => {
  return apiRequest(endpoint, {
    ...options,
    method: 'DELETE',
  })
}

/**
 * Обрабатывает ответ API и возвращает JSON или выбрасывает ошибку
 */
export const handleResponse = async (response) => {
  if (!response.ok) {
    let errorMessage = 'Произошла ошибка'

    try {
      const text = await response.text()

      if (text) {
        try {
          const errorData = JSON.parse(text)
          errorMessage = errorData.message || errorData.error || errorMessage
        } catch {
          errorMessage = text || errorMessage
        }
      } else {
        errorMessage = `Ошибка ${response.status}: ${response.statusText || 'Произошла ошибка'}`
      }
    } catch (e) {
      errorMessage = `Ошибка ${response.status}: ${response.statusText || 'Произошла ошибка'}`
    }

    throw new Error(errorMessage)
  }

  if (response.status === 204 || response.headers.get('content-length') === '0') {
    return null
  }

  return response.json()
}

