import { ROUTES } from './routes'

export const MENU_ITEMS = [
  {
    name: 'tasks',
    label: 'Задачи',
    icon: 'tasks',
    path: ROUTES.TASKS,
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
    ],
  },
]



