<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppAlert from '@/components/AppAlert.vue'
import { apiPost, handleResponse, getToken, setRefreshToken, setToken } from '@/api'
import { API_ENDPOINTS } from '@/constants/api'
import { ROUTES } from '@/constants/routes'
import { getTelegramInitData, getTelegramWebApp } from '@/utils/telegram'

const router = useRouter()

const authenticateTelegramUser = async () => {
  if (getToken()) {
    return
  }

  const initData = getTelegramInitData()
  if (!initData) {
    return
  }

  try {
    const response = await apiPost(API_ENDPOINTS.TELEGRAM.AUTH, {
      init_data: initData,
    })
    const data = await handleResponse(response)

    if (!data?.token) {
      return
    }

    setToken(data.token)
    if (data.refresh_token) {
      setRefreshToken(data.refresh_token)
    }

    getTelegramWebApp()?.ready()

    if (window.location.pathname === ROUTES.LOGIN) {
      router.replace(ROUTES.HOME).catch(() => {
        window.location.href = ROUTES.HOME
      })
    }
  } catch (error) {
    console.error('Telegram auth failed', error)
  }
}

onMounted(() => {
  authenticateTelegramUser()
})
</script>

<template>
  <router-view />
  <AppAlert />
</template>
