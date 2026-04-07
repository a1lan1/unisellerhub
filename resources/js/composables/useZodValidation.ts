import { computed, ref, watch  } from 'vue'
import type { Ref } from 'vue'
import { z  } from 'zod'
import type { ZodType } from 'zod'

export function useZodValidation<T extends ZodType>(
  schema: T,
  formData: Ref<z.infer<T>>
) {
  const errors = ref<Record<string, string>>({})

  const validate = () => {
    errors.value = {}

    try {
      schema.parse(formData.value)

      return true
    } catch (e) {
      if (e instanceof z.ZodError) {
        e.issues.forEach((error) => {
          const path = error.path.join('.')
          errors.value[path] = error.message
        })
      }

      return false
    }
  }

  const hasErrors = computed(() => Object.keys(errors.value).length > 0)

  watch(
    formData,
    () => {
      errors.value = {}
    },
    { deep: true }
  )

  return {
    errors,
    validate,
    hasErrors
  }
}
