import { createRouter, createWebHistory } from 'vue-router'
import Login from '../pages/Login.vue'
import Home from '../pages/Home.vue'
import { hasToken } from '@/api'
import { ROUTES, ROUTE_NAMES } from '@/constants/routes'

const routes = [
  {
    path: ROUTES.LOGIN,
    name: ROUTE_NAMES.LOGIN,
    component: Login,
    meta: { requiresAuth: false },
  },
  {
    path: ROUTES.HOME,
    name: ROUTE_NAMES.HOME,
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: ROUTES.USER,
    name: ROUTE_NAMES.USER,
    component: Home,
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = hasToken()

  if (to.meta.requiresAuth && !token) {
    next(ROUTES.LOGIN)
    return
  }

  next()
})

export default router
