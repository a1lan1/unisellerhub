<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import type { User } from '@/types'

interface Props {
  activeUser?: User;
  users: User[];
}

withDefaults(defineProps<Props>(), {
  activeUser: undefined,
  users: () => []
})
</script>

<template>
  <v-list
    lines="two"
    class="overflow-y-auto grow"
    density="compact"
  >
    <v-list-item
      v-for="user in users"
      :key="user.id"
      :title="user.name"
      :subtitle="user.email"
      density="compact"
      :active="activeUser?.id === user.id"
      @click="router.visit(`/sellers/${user.id}`)"
    >
      <template #prepend>
        <v-avatar size="small">
          <v-img
            v-if="user.avatar"
            :src="user.avatar"
            :alt="user.name"
          />
          <span v-else>
            {{ user.name[0] }}
          </span>
        </v-avatar>
      </template>
    </v-list-item>

    <v-list-item
      v-if="users.length === 0"
      class="text-center py-0"
    >
      <v-list-item-title class="text-neutral-400">
        No users yet
      </v-list-item-title>
    </v-list-item>
  </v-list>
</template>
