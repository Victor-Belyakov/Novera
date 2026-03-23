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
  {
    path: ROUTES.USERS,
    name: ROUTE_NAMES.USERS,
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: ROUTES.TASKS,
    name: ROUTE_NAMES.TASKS,
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: ROUTES.GOALS,
    name: ROUTE_NAMES.GOALS,
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: ROUTES.HABITS,
    name: ROUTE_NAMES.HABITS,
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: ROUTES.SETTINGS,
    name: ROUTE_NAMES.SETTINGS,
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: ROUTES.SETTINGS_CATEGORIES,
    name: ROUTE_NAMES.SETTINGS_CATEGORIES,
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
