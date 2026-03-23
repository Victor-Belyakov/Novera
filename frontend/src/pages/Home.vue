<template>
  <div class="h-screen flex flex-col">
    <AppNavbar />

    <div class="flex flex-1 overflow-hidden">
      <AppSidebar
        :is-collapsed="isCollapsed"
        :menu-items="menuItems"
        @update-active="onMenuActive"
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
import UsersContent from './Home/UsersContent.vue'
import TasksContent from './Home/TasksContent.vue'
import GoalsContent from './Home/GoalsContent.vue'
import HabitsContent from './Home/HabitsContent.vue'
import CategoriesContent from './Home/CategoriesContent.vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { MENU_ITEMS } from '@/constants/menu'
import { ROUTES } from '@/constants/routes'

const route = useRoute()
const router = useRouter()

const isCollapsed = ref(false)

const pathToMenu = {
  [ROUTES.HOME]: 'home',
  [ROUTES.USER]: 'profile',
  [ROUTES.USERS]: 'users',
  [ROUTES.TASKS]: 'tasks',
  [ROUTES.GOALS]: 'goals',
  [ROUTES.HABITS]: 'habits',
  [ROUTES.SETTINGS_CATEGORIES]: 'settings-categories',
  [ROUTES.SETTINGS]: 'settings',
}
const activeMenu = computed(() => pathToMenu[route.path] ?? 'home')

const resetActiveMenu = (menu = 'home') => {
  const path = Object.entries(pathToMenu).find(([, m]) => m === menu)?.[0] ?? ROUTES.HOME
  if (route.path !== path) router.push(path)
}

provide('resetActiveMenu', resetActiveMenu)

const menuItems = MENU_ITEMS

function onMenuActive(menuName) {
  let path = null
  const parent = menuItems.find((m) => m.name === menuName)
  if (parent?.path) {
    path = parent.path
  } else {
    for (const p of menuItems) {
      const child = p.children?.find((c) => c.name === menuName)
      if (child?.path) {
        path = child.path
        break
      }
    }
  }
  if (path && route.path !== path) {
    router.push(path)
  }
}

// Маппинг компонентов для динамической загрузки
const components = {
  home: HomeContent,
  profile: UserContent,
  users: UsersContent,
  tasks: TasksContent,
  goals: GoalsContent,
  habits: HabitsContent,
  settings: SettingsContent,
  'settings-categories': CategoriesContent,
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
    users: 'Пользователи',
    tasks: 'Задачи',
    goals: 'Цели',
    habits: 'Привычки',
    settings: 'Настройки',
    'settings-categories': 'Категории',
  }
  return titles[activeMenu.value] || 'Главная'
})
</script>
