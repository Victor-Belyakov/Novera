import IconProfile from '@/components/icons/IconProfile.vue'
import IconSettings from '@/components/icons/IconSettings.vue'
import IconDashboard from '@/components/icons/IconDashboard.vue'

export const ICON_COMPONENTS = {
  profile: IconProfile,
  settings: IconSettings,
  home: IconDashboard,
}

export function getIconComponent(iconName) {
  return ICON_COMPONENTS[iconName] || null
}

