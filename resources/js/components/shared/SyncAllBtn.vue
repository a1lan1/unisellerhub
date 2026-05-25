<script setup lang="ts">
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { useIdempotencyStore } from '@/stores/idempotency'
import { IdempotencyOperation } from '@/types/enums'

const syncing = ref(false)
const idempotencyStore = useIdempotencyStore()
const OPERATION_NAME = IdempotencyOperation.SyncAll

const syncAll = async() => {
  try {
    syncing.value = true

    const idempotencyKey = idempotencyStore.getIdempotencyKey(OPERATION_NAME)

    await api.post('/api/sync/all', {}, {
      headers: { 'Idempotency-Key': idempotencyKey }
    })

    snackbar.success({ text: 'Sync jobs have been dispatched successfully.' })
  } catch (error) {
    snackbar.error({ text: 'Failed to dispatch sync jobs.' })
    console.error('Sync error:', error)
  } finally {
    syncing.value = false
  }
}
</script>

<template>
  <v-btn
    variant="tonal"
    color="success"
    prepend-icon="mdi-sync"
    density="compact"
    :loading="syncing"
    @click="syncAll"
  >
    Sync All
  </v-btn>
</template>
