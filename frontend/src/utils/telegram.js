export const getTelegramWebApp = () => {
  return window.Telegram?.WebApp ?? null
}

export const getTelegramInitData = () => {
  const webApp = getTelegramWebApp()
  return webApp?.initData || ''
}

export const isTelegramWebApp = () => {
  return getTelegramInitData() !== ''
}
