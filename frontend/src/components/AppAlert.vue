<template>
  <TransitionGroup
    name="notification"
    tag="div"
    class="fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-md"
  >
    <div
      v-for="notification in notifications"
      :key="notification.id"
      :class="getNotificationClasses(notification.type)"
      class="rounded-lg shadow-xl p-4 flex items-start gap-3 animate-slide-in"
    >
      <div :class="getIconClasses(notification.type)" class="flex-shrink-0 mt-0.5">
        <NotificationIcon :type="notification.type" />
      </div>

      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium">{{ notification.message }}</p>
      </div>

      <button
        @click="removeNotification(notification.id)"
        class="flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity"
        aria-label="Закрыть уведомление"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>
  </TransitionGroup>
</template>

<script setup>
import { useNotifications } from '@/composables/useNotifications'
import { NOTIFICATION_TYPES } from '@/constants/api'
import NotificationIcon from './NotificationIcon.vue'

const { notifications, removeNotification } = useNotifications()

const getNotificationClasses = (type) => {
  const baseClasses = 'border-l-4'
  const typeClasses = {
    [NOTIFICATION_TYPES.SUCCESS]: 'bg-green-50 border-green-500 text-green-800',
    [NOTIFICATION_TYPES.ERROR]: 'bg-red-50 border-red-500 text-red-800',
    [NOTIFICATION_TYPES.WARNING]: 'bg-yellow-50 border-yellow-500 text-yellow-800',
    [NOTIFICATION_TYPES.INFO]: 'bg-blue-50 border-blue-950 text-blue-900',
  }
  return `${baseClasses} ${typeClasses[type] || typeClasses[NOTIFICATION_TYPES.INFO]}`
}

const getIconClasses = (type) => {
  const typeClasses = {
    [NOTIFICATION_TYPES.SUCCESS]: 'text-green-500',
    [NOTIFICATION_TYPES.ERROR]: 'text-red-500',
    [NOTIFICATION_TYPES.WARNING]: 'text-yellow-500',
    [NOTIFICATION_TYPES.INFO]: 'text-blue-950',
  }
  return typeClasses[type] || typeClasses[NOTIFICATION_TYPES.INFO]
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}

@keyframes slide-in {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}
</style>
