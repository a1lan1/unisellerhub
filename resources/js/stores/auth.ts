import { usePage } from '@inertiajs/vue3'
import { defineStore } from 'pinia'
import { computed } from 'vue'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const page = usePage()

  const user = computed(() => page.props.auth.user as User)
  const organizationId = computed(() => user.value?.organization_id ?? null)
  const hasOrganization = computed(() => !!organizationId.value)

  return {
    user,
    organizationId,
    hasOrganization
  }
})
