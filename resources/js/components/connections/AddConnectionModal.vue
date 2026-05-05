<script setup lang="ts">
import { ref, watch, markRaw, computed  } from 'vue'
import type { Component } from 'vue'
import { useZodValidation } from '@/composables/useZodValidation'
import { MarketplaceEnum, marketplaceConnectionSchema  } from '@/types'
import type { MarketplaceConnectionForm } from '@/types'
import { marketplaceOptions } from '@/utils/marketplace'
import AvitoFields from './fields/AvitoFields.vue'
import MsFields from './fields/MsFields.vue'
import OzonFields from './fields/OzonFields.vue'
import WbFields from './fields/WbFields.vue'
import YandexFields from './fields/YandexFields.vue'

const props = defineProps<{
  isSaving: boolean;
  errors: Record<string, string>;
}>()

const modelValue = defineModel<boolean>({ required: true })
const emit = defineEmits(['save'])

const connectionMarketplaceOptions = marketplaceOptions.filter(o => o.value !== null)

const fieldsMap: Record<MarketplaceEnum, Component> = {
  [MarketplaceEnum.WB]: markRaw(WbFields),
  [MarketplaceEnum.OZON]: markRaw(OzonFields),
  [MarketplaceEnum.YANDEX]: markRaw(YandexFields),
  [MarketplaceEnum.MOYSKLAD]: markRaw(MsFields),
  [MarketplaceEnum.AVITO]: markRaw(AvitoFields)
}

const createEmptyForm = (mp: MarketplaceEnum = MarketplaceEnum.WB): MarketplaceConnectionForm => ({
  marketplace: mp,
  name: '',
  credentials: {} as any
})

const form = ref<MarketplaceConnectionForm>(createEmptyForm())

const { errors: clientErrors, validate } = useZodValidation(marketplaceConnectionSchema, form)

// Sync server-side errors into clientErrors
watch(() => props.errors, (newVal) => {
  if (newVal) {
    Object.assign(clientErrors.value, newVal)
  }
}, { deep: true })

// Reset form and errors when modal is opened/closed
watch(modelValue, (val) => {
  if (!val) {
    form.value = createEmptyForm()
    clientErrors.value = {}
  }
})

// Update credentials structure when marketplace changes
watch(() => form.value.marketplace, () => {
  form.value.credentials = {} as any
  clientErrors.value = {}
})

const activeFieldsComponent = computed(() => fieldsMap[form.value.marketplace])

const submit = () => {
  if (validate()) {
    emit('save', form.value)
  }
}

const close = () => {
  modelValue.value = false
}
</script>

<template>
  <v-dialog
    max-width="600px"
    :model-value="modelValue"
    @update:model-value="modelValue = $event"
  >
    <v-card title="Add Connection">
      <v-card-text>
        <v-form @submit.prevent="submit">
          <v-select
            v-model="form.marketplace"
            :items="connectionMarketplaceOptions"
            label="Select System"
            variant="outlined"
            density="compact"
            class="mb-4"
            :error-messages="clientErrors.marketplace"
          />

          <v-text-field
            v-model="form.name"
            label="Connection Name"
            variant="outlined"
            density="compact"
            class="mb-4"
            :error-messages="clientErrors.name"
          />

          <v-divider class="mb-6" />

          <!-- Dynamic Marketplace Fields -->
          <component
            :is="activeFieldsComponent"
            v-model="form.credentials"
            :errors="clientErrors"
          />
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          variant="text"
          @click="close"
        >
          Cancel
        </v-btn>
        <v-btn
          color="primary"
          variant="elevated"
          :loading="isSaving"
          @click="submit"
        >
          Save Connection
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
