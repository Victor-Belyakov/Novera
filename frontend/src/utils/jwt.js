/**
 * Парсит JWT токен и возвращает payload
 * @param {string} jwt - JWT токен
 * @returns {object|null} - Payload токена или null при ошибке
 */
export function parseJwt(jwt) {
  try {
    const payload = jwt.split('.')[1]
    return JSON.parse(atob(payload))
  } catch {
    return null
  }
}

/**
 * Проверяет, истек ли JWT токен
 * @param {object} payload - Payload токена
 * @returns {boolean} - true если токен истек
 */
export function isTokenExpired(payload) {
  if (!payload?.exp) {
    return false
  }
  const now = Math.floor(Date.now() / 1000)
  return payload.exp < now
}



