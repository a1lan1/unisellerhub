<script setup lang="ts">
import type { OzonCredentials } from '@/types'

const credentials = defineModel<Partial<OzonCredentials>>({ required: true })

defineProps<{ errors: Record<string, string> }>()
</script>

<template>
  <v-text-field
    v-model="credentials.client_id"
    label="Ozon Client ID"
    variant="outlined"
    density="compact"
    :error-messages="errors['credentials.client_id']"
    class="mb-4"
  />
  <v-text-field
    v-model="credentials.api_key"
    label="Ozon API Key"
    variant="outlined"
    density="compact"
    :error-messages="errors['credentials.api_key']"
  />
</template>
