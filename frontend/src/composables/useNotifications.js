import { ref } from 'vue'
import { NOTIFICATION_TYPES, NOTIFICATION_DURATION } from '@/constants/api'

const notifications = ref([])

export function useNotifications() {
  const showNotification = (message, type = NOTIFICATION_TYPES.INFO) => {
    const id = Date.now() + Math.random()
    const notification = {
      id,
      message,
      type,
    }

    notifications.value.push(notification)

    setTimeout(() => {
      removeNotification(id)
    }, NOTIFICATION_DURATION)

    return id
  }

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const success = (message) => showNotification(message, NOTIFICATION_TYPES.SUCCESS)
  const error = (message) => showNotification(message, NOTIFICATION_TYPES.ERROR)
  const warning = (message) => showNotification(message, NOTIFICATION_TYPES.WARNING)
  const info = (message) => showNotification(message, NOTIFICATION_TYPES.INFO)

  return {
    notifications,
    showNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
  }
}

