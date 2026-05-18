import { ref, computed, readonly } from 'vue'
import { useInertiaLoading } from './useInertiaLoading'

const speed = ref(111)
const showFull = ref(false)
const dots = ref(true)
const interactive = ref(true)
const gradientBg = ref(true)

const { loading: inertiaLoading } = useInertiaLoading()
const gSize = computed(() => inertiaLoading.value ? '15%' : '8%')

export function useBackground() {
  return {
    speed: readonly(speed),
    showFull: readonly(showFull),
    dots: readonly(dots),
    interactive: readonly(interactive),
    gradientBg: readonly(gradientBg),
    gSize: readonly(gSize),

    setSpeed: (value: number) => {
      speed.value = value
    },
    toggleShowFull: () => {
      showFull.value = !showFull.value
    },
    toggleDots: () => {
      dots.value = !dots.value
    },
    toggleInteractive: () => {
      interactive.value = !interactive.value
    },
    toggleGradientBg: () => {
      gradientBg.value = !gradientBg.value
    }
  }
}
