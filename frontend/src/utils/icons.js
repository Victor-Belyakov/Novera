import IconProfile from '@/components/icons/IconProfile.vue'
import IconSettings from '@/components/icons/IconSettings.vue'
import IconDashboard from '@/components/icons/IconDashboard.vue'
import IconUsers from '@/components/icons/IconUsers.vue'
import IconTasks from '@/components/icons/IconTasks.vue'
import IconCheck from '@/components/icons/IconCheck.vue'
import IconMenu from '@/components/icons/IconMenu.vue'

export const ICON_COMPONENTS = {
  profile: IconProfile,
  settings: IconSettings,
  home: IconDashboard,
  users: IconUsers,
  tasks: IconTasks,
  goals: IconCheck,
  habits: IconTasks,
  menu: IconMenu,
}

export function getIconComponent(iconName) {
  return ICON_COMPONENTS[iconName] || null
}

