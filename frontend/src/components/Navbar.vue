<template>
    <nav class="w-full bg-blue-950 text-white flex items-center justify-between px-6 py-3 shadow-lg z-10">
        <!-- Левая часть: логотип -->
        <div class="flex items-center gap-3">
            <router-link to="/dashboard">
                <img :src="logoImg" alt="Logo" class="h-10 w-auto cursor-pointer" />
            </router-link>
        </div>

        <!-- Правая часть: email и кнопка выхода -->
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium">{{ userEmail || 'Пользователь' }}</span>
            <button
                @click="handleLogout"
                class="px-3 py-1.5 rounded-md text-sm font-medium hover:bg-white hover:text-blue-950 transition-colors cursor-pointer"
            >
                Выход
            </button>
        </div>
    </nav>
</template>

<script setup>
import { useAuth } from '@/composables/useAuth'
import logoImg from "@/assets/logo-navbar.png"

const { userEmail, logout } = useAuth()

const handleLogout = () => {
    logout()
}
</script>
