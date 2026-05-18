<script setup lang="ts">
import { useBackground } from '@/composables/useBackground'
import { useInertiaLoading } from '@/composables/useInertiaLoading'

const {
  showFull,
  dots,
  interactive,
  gradientBg,
  toggleShowFull,
  toggleDots,
  toggleInteractive,
  toggleGradientBg
} = useBackground()

const {
  loading,
  setLoading
} = useInertiaLoading()
</script>

<template>
  <v-btn-group
    variant="text"
    :color="loading ? 'success' : undefined"
    @dblclick="toggleShowFull"
  >
    <v-btn
      v-tooltip="`Inertia Loading: ${loading}`"
      :active="loading"
      :icon="loading ? 'mdi-lightbulb-on' : 'mdi-lightbulb-variant'"
      variant="tonal"
      @click="setLoading(!loading)"
    />
    <template v-if="showFull">
      <v-btn
        v-tooltip="`Dots: ${dots}`"
        :active="dots"
        icon="mdi-dots-vertical"
        variant="tonal"
        @click="toggleDots"
      />
      <v-btn
        v-tooltip="`Interactive: ${interactive}`"
        :active="interactive"
        icon="mdi-check-circle"
        variant="tonal"
        @click="toggleInteractive"
      />
      <v-btn
        v-tooltip="`GradientBg: ${gradientBg}`"
        :active="gradientBg"
        icon="mdi-information"
        variant="tonal"
        @click="toggleGradientBg"
      />
    </template>
  </v-btn-group>
</template>
