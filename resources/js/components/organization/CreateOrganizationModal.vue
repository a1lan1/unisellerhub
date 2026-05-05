<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { store as apiOrganizationsStore } from '@/routes/api/organizations'

defineProps<{
  modelValue: boolean;
}>()

const emit = defineEmits(['update:modelValue', 'created'])

const name = ref('')
const isLoading = ref(false)
const errors = ref<Record<string, string>>({})

const createOrganization = async() => {
  isLoading.value = true
  errors.value = {}

  try {
    await api.post(apiOrganizationsStore().url, { name: name.value })
    name.value = ''
    emit('update:modelValue', false)
    emit('created')

    // After creating an organization, redirect to marketplace connections setup
    router.visit('/settings/connections')
  } catch (e: any) {
    if (e?.response?.data?.errors) {
      errors.value = e.response.data.errors
    } else {
      console.error(e)
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <v-dialog
    max-width="500px"
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <v-card>
      <v-card-title>Create Organization</v-card-title>
      <v-card-text>
        <p class="mb-4 text-body-2 text-medium-emphasis">
          You don't have an organization yet. Please create one to start using the platform.
        </p>
        <v-text-field
          v-model="name"
          label="Organization Name"
          variant="outlined"
          :error-messages="errors.name"
          required
        />
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          color="primary"
          variant="elevated"
          :loading="isLoading"
          @click="createOrganization"
        >
          Create
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
