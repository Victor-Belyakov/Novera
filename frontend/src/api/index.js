// API base URL: в dev режиме используем относительный путь (Vite проксирует),
// в production используем переменную окружения или относительный путь (nginx проксирует)
const API_URL = import.meta.env.VITE_API_URL || ''

/**
 * Получает токен из localStorage
 */
const getToken = () => {
  return localStorage.getItem('token')
}

/**
 * Сохраняет токен в localStorage
 */
export const setToken = (token) => {
  if (token) {
    localStorage.setItem('token', token)
  } else {
    localStorage.removeItem('token')
  }
}

/**
 * Сохраняет refresh_token в localStorage
 */
export const setRefreshToken = (refreshToken) => {
  if (refreshToken) {
    localStorage.setItem('refresh_token', refreshToken)
  } else {
    localStorage.removeItem('refresh_token')
  }
}

/**
 * Удаляет токен из localStorage (logout)
 */
export const removeToken = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('refresh_token')
}

/**
 * Проверяет, есть ли токен
 */
export const hasToken = () => {
  return !!getToken()
}

/**
 * Выполняет API запрос с автоматическим добавлением токена
 * @param {string} endpoint - API endpoint (например: '/api/auth/login')
 * @param {object} options - Опции для fetch (method, body, headers и т.д.)
 * @returns {Promise<Response>}
 */
export const apiRequest = async (endpoint, options = {}) => {
  const token = getToken()
  
  // Подготавливаем заголовки
  const headers = {
    'Content-Type': 'application/json',
    ...options.headers,
  }
  
  // Добавляем токен в заголовки, если он есть (кроме публичных эндпоинтов)
  if (token && !endpoint.includes('/auth/login') && !endpoint.includes('/auth/register')) {
    headers['Authorization'] = `Bearer ${token}`
  }
  
  // Выполняем запрос
  const response = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers,
  })
  
  // Если токен истек или недействителен, удаляем его
  if (response.status === 401 && token) {
    removeToken()
    // Можно добавить редирект на страницу логина
    // window.location.href = '/login'
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
      const errorData = await response.json()
      errorMessage = errorData.message || errorData.error || errorMessage
    } catch (e) {
      const errorText = await response.text()
      errorMessage = errorText || errorMessage
    }
    throw new Error(errorMessage)
  }
  
  // Если ответ пустой (например, 204 No Content), возвращаем null
  if (response.status === 204 || response.headers.get('content-length') === '0') {
    return null
  }
  
  return response.json()
}

