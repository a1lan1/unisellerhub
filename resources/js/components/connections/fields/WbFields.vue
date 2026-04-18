<script setup lang="ts">
import type { WbCredentials } from '@/types'

const credentials = defineModel<Partial<WbCredentials>>({ required: true })

defineProps<{ errors: Record<string, string> }>()
</script>

<template>
  <v-textarea
    v-model="credentials.token"
    label="Wildberries API Token"
    variant="outlined"
    density="compact"
    rows="3"
    :error-messages="errors['credentials.token']"
  />
</template>
