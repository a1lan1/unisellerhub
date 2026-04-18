<script setup lang="ts">
import type { AvitoCredentials } from '@/types'

const credentials = defineModel<Partial<AvitoCredentials>>({ required: true })

defineProps<{ errors: Record<string, string> }>()
</script>

<template>
  <v-text-field
    v-model="credentials.client_id"
    label="Avito Client ID"
    variant="outlined"
    density="compact"
    :error-messages="errors['credentials.client_id']"
    class="mb-4"
  />
  <v-text-field
    v-model="credentials.client_secret"
    label="Avito Client Secret"
    variant="outlined"
    density="compact"
    type="password"
    :error-messages="errors['credentials.client_secret']"
  />
</template>
