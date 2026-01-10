import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import Dashboard from '../pages/Dashboard.vue'
import { hasToken } from '@/api'

const routes = [
    { 
        path: '/', 
        component: Home,
        meta: { requiresAuth: false }
    },
    { 
        path: '/dashboard', 
        component: Dashboard,
        meta: { requiresAuth: true }
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// Защита маршрутов, требующих авторизации
router.beforeEach((to, from, next) => {
    const token = hasToken()
    
    console.log('Router guard:', { 
        to: to.path, 
        from: from.path, 
        requiresAuth: to.meta.requiresAuth, 
        hasToken: token 
    })
    
    // Если пытаемся зайти на защищенный маршрут без токена
    if (to.meta.requiresAuth && !token) {
        console.log('Доступ запрещен: нет токена, редирект на /')
        next('/')
        return
    }
    
    console.log('Доступ разрешен, переход на:', to.path)
    next()
})

export default router
