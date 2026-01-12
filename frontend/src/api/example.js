/**
 * Пример использования API для авторизованных запросов
 * Этот файл показывает, как использовать API утилиту для различных запросов
 */

import { apiGet, apiPost, apiPut, apiPatch, apiDelete, handleResponse, removeToken } from '@/api'

/**
 * Пример: Выход из системы (logout)
 */
export const logout = async () => {
  try {
    // Запрос на logout (требует токен)
    const response = await apiPost('/api/auth/logout')
    await handleResponse(response)
    
    // Удаляем токен из localStorage
    removeToken()
    
    // Редирект на страницу логина
    window.location.href = '/'
  } catch (error) {
    console.error('Ошибка при выходе:', error)
    // Даже если запрос не удался, удаляем токен локально
    removeToken()
    window.location.href = '/'
  }
}

/**
 * Пример: Обновление refresh token
 */
export const refreshToken = async () => {
  try {
    const refreshToken = localStorage.getItem('refresh_token')
    if (!refreshToken) {
      throw new Error('Refresh token не найден')
    }

    const response = await apiPost('/api/auth/refresh', {
      refresh_token: refreshToken
    })
    
    const data = await handleResponse(response)
    
    if (data.token) {
      localStorage.setItem('token', data.token)
      if (data.refresh_token) {
        localStorage.setItem('refresh_token', data.refresh_token)
      }
      return data.token
    }
  } catch (error) {
    console.error('Ошибка при обновлении токена:', error)
    removeToken()
    throw error
  }
}

/**
 * Пример: GET запрос к защищенному API
 */
export const getUserProfile = async () => {
  try {
    // Токен автоматически добавится в заголовки
    const response = await apiGet('/api/user/profile')
    return await handleResponse(response)
  } catch (error) {
    console.error('Ошибка при получении профиля:', error)
    throw error
  }
}

/**
 * Пример: POST запрос с данными
 */
export const updateUserProfile = async (userData) => {
  try {
    const response = await apiPut('/api/user/profile', userData)
    return await handleResponse(response)
  } catch (error) {
    console.error('Ошибка при обновлении профиля:', error)
    throw error
  }
}

/**
 * Пример: DELETE запрос
 */
export const deleteUser = async (userId) => {
  try {
    const response = await apiDelete(`/api/user/${userId}`)
    return await handleResponse(response)
  } catch (error) {
    console.error('Ошибка при удалении пользователя:', error)
    throw error
  }
}





