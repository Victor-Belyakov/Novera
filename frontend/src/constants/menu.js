import { ROUTES } from './routes'

export const MENU_ITEMS = [
  {
    name: 'home',
    label: 'Домашняя',
    icon: 'home',
    path: ROUTES.HOME,
  },
  {
    name: 'tasks',
    label: 'Задачи',
    icon: 'tasks',
    path: ROUTES.TASKS,
  },
  {
    name: 'finances',
    label: 'Финансы',
    icon: 'finances',
    path: ROUTES.FINANCES,
  },
  {
    name: 'health',
    label: 'Здоровье',
    icon: 'health',
    path: ROUTES.HEALTH,
  },
  {
    name: 'users',
    label: 'Пользователи',
    icon: 'users',
    path: ROUTES.USERS,
  },
  {
    name: 'goals',
    label: 'Цели',
    icon: 'goals',
    path: ROUTES.GOALS,
  },
  {
    name: 'habits',
    label: 'Привычки',
    icon: 'habits',
    path: ROUTES.HABITS,
  },
  {
    name: 'settings',
    label: 'Настройки',
    icon: 'settings',
    path: ROUTES.SETTINGS,
    children: [
      { name: 'settings-categories', label: 'Категории целей', path: ROUTES.SETTINGS_CATEGORIES, icon: 'menu' },
      { name: 'settings-finance-categories', label: 'Категории финансов', path: ROUTES.SETTINGS_FINANCE_CATEGORIES, icon: 'menu' },
      { name: 'settings-health-metrics', label: 'Метрики здоровья', path: ROUTES.SETTINGS_HEALTH_METRICS, icon: 'menu' },
    ],
  },
]
