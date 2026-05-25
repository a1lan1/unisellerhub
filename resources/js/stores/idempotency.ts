import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import type { IdempotencyOperation } from '@/types/enums'
import { generateUUID } from '@/utils/uuid'

interface IdempotencyKeyData {
  value: string;
  timestamp: number;
}

const KEY_EXPIRATION_MS = 10 * 60 * 1000 // 10 minutes in milliseconds

export const useIdempotencyStore = defineStore('idempotency', () => {
  const keys = ref<Record<IdempotencyOperation, IdempotencyKeyData>>(
    JSON.parse(localStorage.getItem('idempotencyKeys') || '{}')
  )

  watch(keys, (newKeys) => {
    localStorage.setItem('idempotencyKeys', JSON.stringify(newKeys))
  }, { deep: true })

  /**
   * Retrieves the idempotency key for a given operation.
   * If no key exists for the operation, or if the existing key has expired,
   * a new one is generated and stored.
   * @param operationName - A unique name for the idempotent operation.
   * @returns The idempotency key for the operation.
   */
  function getIdempotencyKey(operationName: IdempotencyOperation): string {
    const now = Date.now()
    const storedKeyData = keys.value[operationName]

    if (storedKeyData && (now - storedKeyData.timestamp < KEY_EXPIRATION_MS)) {
      // Key exists and is still valid, return its value
      return storedKeyData.value
    } else {
      // Key does not exist or has expired, generate a new one
      const newKey = generateUUID()
      keys.value[operationName] = {
        value: newKey,
        timestamp: now
      }

      return newKey
    }
  }

  /**
   * Generates a new idempotency key for a given operation.
   * This should typically be called after a successful idempotent operation
   * if a new logical operation is intended.
   * @param operationName - The unique name for the idempotent operation.
   */
  function generateNewIdempotencyKey(operationName: IdempotencyOperation) {
    keys.value[operationName] = {
      value: generateUUID(),
      timestamp: Date.now()
    }
  }

  /**
   * Clears the idempotency key for a given operation from storage.
   * @param operationName - The unique name for the idempotent operation.
   */
  function clearIdempotencyKey(operationName: IdempotencyOperation) {
    delete keys.value[operationName]
  }

  return {
    getIdempotencyKey,
    generateNewIdempotencyKey,
    clearIdempotencyKey
  }
})
