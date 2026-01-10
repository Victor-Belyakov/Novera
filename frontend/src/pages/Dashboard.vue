<template>
    <div class="h-screen flex flex-col">
        <!-- Navbar -->
        <Navbar />

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar -->
            <Sidebar
                :activeMenu="activeMenu"
                :collapsed="collapsed"
                :menuItems="menuItems"
                @updateActive="activeMenu = $event"
                @toggleCollapse="collapsed = !collapsed"
            />

            <!-- Основной контент -->
            <main class="flex-1 p-6 overflow-y-auto bg-gray-50">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">{{ pageTitle }}</h2>

                <div v-if="activeMenu==='dashboard'" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-gray-500 text-sm">Всего</p>
                        <p class="text-gray-900 text-2xl font-semibold">0</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-gray-500 text-sm">Активные</p>
                        <p class="text-gray-900 text-2xl font-semibold">0</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-gray-500 text-sm">Ожидают</p>
                        <p class="text-gray-900 text-2xl font-semibold">0</p>
                    </div>
                </div>

                <div v-else-if="activeMenu==='profile'" class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Профиль</h3>
                    <p class="text-gray-700">Email: {{ userEmail }}</p>
                </div>

                <div v-else-if="activeMenu==='settings'" class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Настройки</h3>
                    <p class="text-gray-600">Настройки будут доступны скоро.</p>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Sidebar from '@/components/Sidebar.vue'
import Navbar from '@/components/Navbar.vue'
import { useAuth } from '@/composables/useAuth'
import router from '@/router'

const { userEmail, logout } = useAuth()

const activeMenu = ref('dashboard')
const collapsed = ref(false)

const menuItems = [
    { name: 'dashboard', label: 'Главная', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'profile', label: 'Профиль', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { name: 'settings', label: 'Настройки', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' }
]

const pageTitle = computed(() => {
    const titles = { dashboard: 'Главная', profile: 'Профиль', settings: 'Настройки' }
    return titles[activeMenu.value] || 'Главная'
})

const handleLogout = () => {
    logout()
    router.push('/')
}
</script>
