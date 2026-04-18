<script setup lang="ts">
import type { MsCredentials } from '@/types'

const credentials = defineModel<Partial<MsCredentials>>({ required: true })

defineProps<{ errors: Record<string, string> }>()
</script>

<template>
  <v-textarea
    v-model="credentials.ms_token"
    label="MoySklad API Token"
    variant="outlined"
    density="compact"
    rows="3"
    :error-messages="errors['credentials.ms_token']"
  />
</template>
