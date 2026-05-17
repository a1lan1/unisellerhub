<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { ref } from 'vue'
import LocationFormDialog from '@/components/geo/LocationFormDialog.vue'
import { dashboard } from '@/routes'
import { index as geoLocations } from '@/routes/geo/locations'
import { useGeoStore } from '@/stores/geo'
import type { Location } from '@/types/geo'

defineProps<{
  locations: Location[];
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Geo', href: '#' },
      { title: 'Locations', href: geoLocations().url }
    ]
  }
})

const geoStore = useGeoStore()
const { loading, storing } = storeToRefs(geoStore)
const { deleteLocation, fillFormForEdit, resetForm } = geoStore

const showLocationDialog = ref(false)
const isEditingLocation = ref(false)
const showDeleteDialog = ref(false)
const locationToDelete = ref<Location | null>(null)

const openCreateDialog = () => {
  resetForm()
  isEditingLocation.value = false
  showLocationDialog.value = true
}

const openEditDialog = (location: Location) => {
  fillFormForEdit(location)
  isEditingLocation.value = true
  showLocationDialog.value = true
}

const confirmDelete = (location: Location) => {
  locationToDelete.value = location
  showDeleteDialog.value = true
}

const handleDelete = async() => {
  if (locationToDelete.value) {
    await deleteLocation(locationToDelete.value.id)
    showDeleteDialog.value = false
    locationToDelete.value = null
    window.location.reload()
  }
}

const handleSaved = () => {
  window.location.reload()
}
</script>

<template>
  <div class="m-2">
    <div
      v-if="!locations.length && !loading"
      class="mb-8"
    >
      <VEmptyState
        headline="No locations found"
        title="Start by adding your first business location"
        icon="mdi-map-marker-plus"
      >
        <VBtn
          prepend-icon="mdi-plus"
          color="primary"
          variant="tonal"
          density="compact"
          @click="openCreateDialog"
        >
          Add Location
        </VBtn>
      </VEmptyState>
    </div>
    <div
      v-else
      class="mb-2 flex items-center justify-between"
    >
      <h2 class="text-2xl font-semibold">
        My Locations
      </h2>
      <VBtn
        color="primary"
        prepend-icon="mdi-plus"
        variant="tonal"
        density="compact"
        @click="openCreateDialog"
      >
        Add Location
      </VBtn>
    </div>

    <VCard>
      <VTable>
        <thead>
          <tr>
            <th class="text-left">
              Name
            </th>
            <th class="text-left">
              Type
            </th>
            <th class="text-left">
              Address
            </th>
            <th class="text-left">
              Coordinates
            </th>
            <th class="text-right">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="location in locations"
            :key="location.id"
          >
            <td>{{ location.name }}</td>
            <td>
              <VChip
                size="small"
                color="primary"
                variant="flat"
                prepend-icon="mdi-map-marker"
                class="capitalize"
              >
                {{ location.type }}
              </VChip>
            </td>
            <td>
              <div class="text-sm">
                {{ location.address.full_address }}
              </div>
              <div class="text-xs text-gray-500">
                {{ location.address.city }}, {{ location.address.country }}
              </div>
            </td>
            <td class="text-xs text-gray-500">
              {{ location.latitude }}, {{ location.longitude }}
            </td>
            <td class="content-center">
              <VTooltip
                text="Edit"
                location="top"
              >
                <template #activator="{ props }">
                  <VBtn
                    icon
                    variant="tonal"
                    color="primary"
                    density="comfortable"
                    class="mx-1"
                    v-bind="props"
                    @click="openEditDialog(location)"
                  >
                    <VIcon>mdi-pencil</VIcon>
                  </VBtn>
                </template>
              </VTooltip>
              <VTooltip
                text="Delete"
                location="top"
              >
                <template #activator="{ props }">
                  <VBtn
                    icon
                    variant="tonal"
                    color="error"
                    density="comfortable"
                    class="mx-1"
                    v-bind="props"
                    @click="confirmDelete(location)"
                  >
                    <VIcon>mdi-delete</VIcon>
                  </VBtn>
                </template>
              </VTooltip>
            </td>
          </tr>
          <tr v-if="locations.length === 0">
            <td
              colspan="5"
              class="py-8 text-center text-gray-500"
            >
              No locations found. Click "Add Location" to create one.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>

  <LocationFormDialog
    v-model="showLocationDialog"
    :is-editing="isEditingLocation"
    @saved="handleSaved"
  />

  <VDialog
    v-model="showDeleteDialog"
    max-width="400px"
  >
    <VCard>
      <VCardTitle>Delete Location</VCardTitle>
      <VCardText>
        Are you sure you want to delete
        <strong>{{ locationToDelete?.name }}</strong>? This action cannot be undone.
      </VCardText>
      <div class="flex justify-end gap-2 p-4">
        <VBtn
          color="grey"
          variant="text"
          @click="showDeleteDialog = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="error"
          :loading="storing"
          @click="handleDelete"
        >
          Delete
        </VBtn>
      </div>
    </VCard>
  </VDialog>
</template>
