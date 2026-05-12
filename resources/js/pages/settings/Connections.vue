<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import AddConnectionModal from '@/components/connections/AddConnectionModal.vue'
import Heading from '@/components/Heading.vue'
import { useConnections } from '@/composables/useConnections'
import { snackbar } from '@/plugins/snackbar'
import type { MarketplaceConnectionForm } from '@/types'
import { getMarketplaceColor, getMarketplaceLabel } from '@/utils/marketplace'

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Marketplace connections',
        href: '/settings/connections'
      }
    ]
  }
})

const {
  connections,
  isLoading,
  isSaving,
  isDeleting,
  errors,
  deleteConnection,
  saveConnection
} = useConnections()

const showAddModal = ref(false)

const handleSave = async(form: MarketplaceConnectionForm) => {
  try {
    await saveConnection(form)
    showAddModal.value = false
  } catch (e: any) {
    snackbar?.error({
      text: e?.message || 'An unexpected error occurred while saving'
    })
  }
}
</script>

<template>
  <Head title="Marketplace Connections" />

  <div class="flex flex-col space-y-6">
    <div class="flex items-center justify-between">
      <Heading
        variant="small"
        title="Marketplace Connections"
        description="Manage your API keys and tokens for different marketplaces and systems."
      />
      <v-btn
        color="primary"
        variant="tonal"
        prepend-icon="mdi-plus"
        @click="showAddModal = true"
      >
        Add Connection
      </v-btn>
    </div>

    <v-divider />

    <div
      v-if="isLoading"
      class="flex justify-center p-10"
    >
      <v-progress-circular
        indeterminate
        color="primary"
      />
    </div>
    <div
      v-else-if="connections.length === 0"
      class="text-center p-10 bg-neutral-50 rounded-lg border border-dashed"
    >
      <p class="text-neutral-500 mb-4">
        No active connections found.
      </p>
      <v-btn
        variant="outlined"
        color="primary"
        @click="showAddModal = true"
      >
        Set up your first connection
      </v-btn>
    </div>
    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 gap-4"
    >
      <v-card
        v-for="conn in connections"
        :key="conn.id"
        variant="outlined"
        class="relative overflow-visible!"
      >
        <v-card-item>
          <template #prepend>
            <v-avatar
              :color="getMarketplaceColor(conn.marketplace)"
              size="40"
              class="text-white font-weight-bold"
            >
              {{ conn.marketplace.toUpperCase() }}
            </v-avatar>
          </template>
          <v-card-title>{{ conn.name }}</v-card-title>
          <v-card-subtitle>{{ getMarketplaceLabel(conn.marketplace) }}</v-card-subtitle>
        </v-card-item>

        <v-card-text>
          <div
            v-for="(val, key) in conn.credentials"
            :key="key"
            class="text-caption mb-1"
          >
            <span class="font-weight-bold text-uppercase">{{ key }}:</span>
            <span class="font-mono ml-2">{{ val }}</span>
          </div>
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn
            color="error"
            variant="text"
            size="small"
            icon="mdi-delete"
            :loading="isDeleting"
            @click="deleteConnection(conn.id)"
          />
        </v-card-actions>
      </v-card>
    </div>
  </div>

  <AddConnectionModal
    v-model="showAddModal"
    :is-saving="isSaving"
    :errors="errors"
    @save="handleSave"
  />
</template>
