<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { VSnackbarQueue } from 'vuetify/components'
import { useSnackbarStore } from '@/stores/snackbar'

const snackbarStore = useSnackbarStore()
const { messages } = storeToRefs(snackbarStore)
</script>

<template>
  <VSnackbarQueue
    v-model="messages"
    close-on-content-click
    location="top right"
  />
</template>
