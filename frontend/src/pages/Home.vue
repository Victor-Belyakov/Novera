<template>
  <div class="h-screen flex flex-col">
    <AppNavbar />

    <div class="flex flex-1 overflow-hidden">
      <AppSidebar
        :active-menu="activeMenu"
        :is-collapsed="isCollapsed"
        :menu-items="menuItems"
        @update-active="activeMenu = $event"
        @toggle-collapse="isCollapsed = !isCollapsed"
      />

      <main :class="['flex-1 bg-gray-50', activeMenu === 'home' ? 'flex flex-col overflow-hidden' : 'p-6 overflow-y-auto']">
          <h2 v-if="activeMenu !== 'home'" class="text-2xl font-semibold text-gray-900 mb-6">{{ pageTitle }}</h2>
          <component :is="currentComponent" v-bind="componentProps" />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, provide } from 'vue'
import { useRoute } from 'vue-router'
import AppSidebar from '@/components/AppSidebar.vue'
import AppNavbar from '@/components/AppNavbar.vue'
import HomeContent from './Home/HomeContent.vue'
import UserContent from './Home/UserContent.vue'
import SettingsContent from './Home/SettingsContent.vue'
import { useAuth } from '@/composables/useAuth'
import { MENU_ITEMS } from '@/constants/menu'
import { ROUTES } from '@/constants/routes'

const route = useRoute()

const activeMenu = ref('home')
const isCollapsed = ref(false)

// Функция для сброса activeMenu (предоставляется дочерним компонентам)
const resetActiveMenu = (menu = 'home') => {
  activeMenu.value = menu
}

// Предоставляем функцию дочерним компонентам
provide('resetActiveMenu', resetActiveMenu)

// Отслеживаем изменения роута и обновляем activeMenu
watch(
  () => route.path,
  (newPath) => {
    if (newPath === ROUTES.HOME) {
      activeMenu.value = 'home'
    } else if (newPath === ROUTES.USER) {
      activeMenu.value = 'profile'
    }
  },
  { immediate: true }
)

const menuItems = MENU_ITEMS

// Маппинг компонентов для динамической загрузки
const components = {
  home: HomeContent,
  profile: UserContent,
  settings: SettingsContent,
}

// Текущий компонент на основе activeMenu
const currentComponent = computed(() => {
  return components[activeMenu.value] || HomeContent
})

// Props для компонентов
const componentProps = computed(() => {
  return {}
})

const pageTitle = computed(() => {
  const titles = {
    home: 'Главная',
    profile: 'Пользователь',
    settings: 'Настройки',
  }
  return titles[activeMenu.value] || 'Главная'
})
</script>
