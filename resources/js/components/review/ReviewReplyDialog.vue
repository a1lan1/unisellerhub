<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { onMounted, ref, watch } from 'vue'
import { snackbar } from '@/plugins/snackbar'
import { useGeoStore } from '@/stores/geo'
import type { Review } from '@/types/geo'

defineProps<{
  review: Review | null;
}>()

const model = defineModel<boolean>()

const storing = ref(false)
const replyText = ref('')
const selectedTemplateId = ref<number | null>(null)

const geoStore = useGeoStore()
const { templates } = storeToRefs(geoStore)
const { fetchTemplates } = geoStore

watch(selectedTemplateId, (newId) => {
  if (newId) {
    const template = templates.value.find((t) => t.id === newId)

    if (template) {
      replyText.value = template.body
    }
  }
})

watch(model, (isOpen) => {
  if (isOpen) {
    replyText.value = ''
    selectedTemplateId.value = null
  }
})

const handleSendReply = () => {
  storing.value = true

  setTimeout(() => {
    snackbar.info({ text: 'Reply functionality is in development.' })
    storing.value = false
    model.value = false
  }, 2000)
}

onMounted(fetchTemplates)
</script>

<template>
  <VDialog
    v-model="model"
    max-width="600px"
  >
    <VCard>
      <VCardTitle>Reply to {{ review?.author_name }}</VCardTitle>
      <VCardText>
        <VSelect
          v-model="selectedTemplateId"
          :items="templates"
          item-title="title"
          item-value="id"
          label="Use a template"
          clearable
          variant="outlined"
          density="compact"
        />
        <VTextarea
          v-model="replyText"
          label="Your Reply"
          rows="5"
          variant="outlined"
          auto-grow
          hide-details
        />
      </VCardText>
      <div class="flex justify-end gap-2 px-4 pb-4">
        <VBtn
          color="grey"
          variant="text"
          @click="model = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="primary"
          variant="tonal"
          :loading="storing"
          @click="handleSendReply"
        >
          Send Reply
        </VBtn>
      </div>
    </VCard>
  </VDialog>
</template>
