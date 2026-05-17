<script setup lang="ts">
import { ref, onMounted } from 'vue'
import UserList from '@/components/user/UserList.vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import type { Seller } from '@/types'

interface Props {
  activeSeller?: Seller;
  initialSellers?: Seller[];
}

const props = withDefaults(defineProps<Props>(), {
  activeSeller: undefined,
  initialSellers: () => []
})

const sellers = ref<Seller[]>([...props.initialSellers])

const loading = ref(false)

async function fetchSellers() {
  loading.value = true

  try {
    const { data } = await api.get<Seller[]>('/api/sellers')
    sellers.value = data
  } catch (e: any) {
    snackbar.error({ text: e.response?.data?.message || 'Failed to fetch sellers' })
  } finally {
    loading.value = false
  }
}

onMounted(fetchSellers)
</script>

<template>
  <v-card
    border
    class="glass"
    style="position: fixed; right: 20px; top: 100px; z-index: 777"
  >
    <v-card-title class="flex align-center pb-0">
      <v-icon
        start
        icon="mdi-history"
        size="small"
      />
      Sellers
    </v-card-title>

    <v-divider />

    <UserList
      :active-user="activeSeller"
      :users="sellers"
      style="max-height: 575px"
    />
  </v-card>
</template>
