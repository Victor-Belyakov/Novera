<template>
  <nav class="w-full bg-blue-950 text-white flex items-center justify-between px-6 py-3 shadow-lg z-10">
    <div class="flex items-center gap-3">
      <router-link :to="ROUTES.HOME" @click="handleLogoClick">
        <img :src="logoImg" alt="Logo" class="h-10 w-auto cursor-pointer" />
      </router-link>
    </div>

    <div class="flex items-center gap-4">
      <button
        @click="handleProfileClick"
        class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 transition-colors cursor-pointer flex items-center justify-center text-white font-semibold text-lg"
        :title="userEmail || 'Профиль'"
      >
        {{ getInitials() }}
      </button>
      <button
        @click="handleLogout"
        class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 transition-colors cursor-pointer flex items-center justify-center text-white"
        title="Выход"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
        </svg>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import logoImg from '@/assets/logo-navbar.png'
import { ROUTES } from '@/constants/routes'

const router = useRouter()
const { userEmail, logout, loadUserFromToken } = useAuth()

// Получаем функцию для изменения activeMenu из родительского компонента
const resetActiveMenu = inject('resetActiveMenu', null)

onMounted(() => {
  loadUserFromToken()
})

const handleLogoClick = () => {
  // Переходим на главную при клике на логотип
  router.push(ROUTES.HOME)
  if (resetActiveMenu) {
    resetActiveMenu('home')
  }
}

const handleProfileClick = () => {
  // Переходим на страницу пользователя при клике на аватар
  router.push(ROUTES.USER)
  if (resetActiveMenu) {
    resetActiveMenu('profile')
  }
}

const getInitials = () => {
  if (!userEmail.value) return '?'
  
  // Получаем первую букву email или первые две буквы
  const email = userEmail.value.trim()
  if (email.length === 0) return '?'
  
  // Берем первую букву до символа @
  const firstLetter = email.charAt(0).toUpperCase()
  return firstLetter
}

const handleLogout = () => {
  logout()
}
</script>

