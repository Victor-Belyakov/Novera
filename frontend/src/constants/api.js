export const API_ENDPOINTS = {
  AUTH: {
    LOGIN: '/api/auth/login',
    REGISTER: '/api/auth/register',
    LOGOUT: '/api/auth/logout',
    REFRESH: '/api/auth/refresh',
  },
  USER: {
    GET_CURRENT: '/api/user',
  },
  USERS: {
    LIST: '/api/users',
  },
  TASKS: {
    LIST: '/api/tasks',
    CREATE: '/api/tasks',
    updateUrl: (id) => `/api/tasks/${id}`,
    getUrl: (id) => `/api/tasks/${id}`,
  },
  CATEGORIES: {
    LIST: '/api/categories',
    CREATE: '/api/categories',
  },
  GOALS: {
    LIST: '/api/goals',
    CREATE: '/api/goals',
    updateUrl: (id) => `/api/goals/${id}`,
  },
  HABITS: {
    LIST: '/api/habits',
    CREATE: '/api/habits',
    logsUrl: (id) => `/api/habits/${id}/logs`,
    addLogUrl: (id) => `/api/habits/${id}/log`,
    skipLogUrl: (id) => `/api/habits/${id}/logs/skip`,
  },
  REMINDERS: {
    LIST: '/api/reminders',
    CREATE: '/api/reminders',
    getUrl: (id) => `/api/reminders/${id}`,
    updateUrl: (id) => `/api/reminders/${id}`,
    deleteUrl: (id) => `/api/reminders/${id}`,
  },
}

export const STORAGE_KEYS = {
  TOKEN: 'token',
  REFRESH_TOKEN: 'refresh_token',
}

export const NOTIFICATION_TYPES = {
  SUCCESS: 'success',
  ERROR: 'error',
  WARNING: 'warning',
  INFO: 'info',
}

export const NOTIFICATION_DURATION = 5000 // 5 seconds



