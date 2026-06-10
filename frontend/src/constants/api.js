export const API_ENDPOINTS = {
  AUTH: {
    LOGIN: '/api/auth/login',
    REGISTER: '/api/auth/register',
    LOGOUT: '/api/auth/logout',
    REFRESH: '/api/auth/refresh',
  },
  TELEGRAM: {
    CONNECT_LINK: '/api/telegram/connect-link',
    AUTH: '/api/telegram/auth',
    CONFIG: '/api/telegram/config',
  },
  USER: {
    GET_CURRENT: '/api/user',
  },
  PERSONAL_STATE: {
    GET: '/api/personal-state',
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
  FINANCES: {
    LIST: '/api/finances',
    CREATE: '/api/finances',
    updateUrl: (id) => `/api/finances/${id}`,
  },
  FINANCE_PLANS: {
    LIST: '/api/finance-plans',
    CREATE: '/api/finance-plans',
    SUMMARY: '/api/finance-plans/summary',
  },
  FINANCE_CATEGORIES: {
    LIST: '/api/finance-categories',
    CREATE: '/api/finance-categories',
  },
  HEALTH_METRIC_TYPES: {
    LIST: '/api/health-metric-types',
    ACTIVE_LIST: '/api/health-metric-types?active_only=true',
    CREATE: '/api/health-metric-types',
  },
  HEALTH_METRICS: {
    LIST: '/api/health-metrics',
    CREATE: '/api/health-metrics',
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
  STATISTICS: {
    LIST: '/api/statistics',
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
