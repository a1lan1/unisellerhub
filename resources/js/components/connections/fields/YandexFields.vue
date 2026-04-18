<script setup lang="ts">
import type { YandexCredentials } from '@/types'

const credentials = defineModel<Partial<YandexCredentials>>({ required: true })

defineProps<{ errors: Record<string, string> }>()
</script>

<template>
  <v-text-field
    v-model="credentials.api_key"
    label="Yandex API Key"
    variant="outlined"
    density="compact"
    :error-messages="errors['credentials.api_key']"
    class="mb-4"
  />
  <v-text-field
    v-model="credentials.campaign_id"
    label="Campaign ID"
    variant="outlined"
    density="compact"
    :error-messages="errors['credentials.campaign_id']"
    class="mb-4"
  />
  <v-text-field
    v-model="credentials.business_id"
    label="Business ID"
    variant="outlined"
    density="compact"
    :error-messages="errors['credentials.business_id']"
  />
</template>
