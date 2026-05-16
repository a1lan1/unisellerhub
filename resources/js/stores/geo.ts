import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import type { Pagination } from '@/types'
import type { Location, LocationForm, ResponseTemplate } from '@/types/geo'

const defaultForm: LocationForm = {
  name: '',
  type: 'store',
  address: {
    country: '',
    city: '',
    street: '',
    house_number: '',
    postal_code: '',
    full_address: ''
  },
  latitude: null,
  longitude: null
}

export const useGeoStore = defineStore('geo', () => {
  const locations = ref<Location[]>([])
  const templates = ref<ResponseTemplate[]>([])
  const form = ref<LocationForm>({ ...defaultForm })
  const loading = ref(false)
  const storing = ref(false)
  const templatesLoading = ref(false)
  const pagination = ref<Pagination<Location>['meta'] | null>(null)

  const fetchLocations = async() => {
    loading.value = true

    try {
      const { data } = await api.get<Location[]>('/api/geo/locations')
      locations.value = data
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to fetch locations' })
    } finally {
      loading.value = false
    }
  }

  const createLocation = async(locationForm: LocationForm) => {
    storing.value = true

    try {
      const { data } = await api.post<Location>('/api/geo/locations', locationForm)
      locations.value.unshift(data)
      snackbar.success({ text: 'Location created successfully' })

      return data
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to create location' })

      throw e
    } finally {
      storing.value = false
    }
  }

  const updateLocation = async(id: number, locationForm: Partial<LocationForm>) => {
    storing.value = true

    try {
      const { data } = await api.put<Location>(`/api/geo/locations/${id}`, locationForm)
      const index = locations.value.findIndex((l) => l.id === id)

      if (index !== -1) {
        locations.value[index] = data
      }

      snackbar.success({ text: 'Location updated successfully' })

      return data
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to update location' })

      throw e
    } finally {
      storing.value = false
    }
  }

  const deleteLocation = async(id: number) => {
    storing.value = true

    try {
      await api.delete(`/api/geo/locations/${id}`)
      locations.value = locations.value.filter((l) => l.id !== id)
      snackbar.success({ text: 'Location deleted successfully' })
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to delete location' })

      throw e
    } finally {
      storing.value = false
    }
  }

  const fetchTemplates = async() => {
    templatesLoading.value = true

    try {
      const { data } = await api.get<ResponseTemplate[]>('/api/geo/response-templates')
      templates.value = data
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to fetch templates' })
    } finally {
      templatesLoading.value = false
    }
  }

  const resetForm = () => {
    form.value = { ...defaultForm }
  }

  const fillFormForEdit = (location: Location) => {
    form.value = {
      id: location.id,
      name: location.name,
      type: location.type,
      address: { ...location.address },
      latitude: location.latitude,
      longitude: location.longitude
    }
  }

  return {
    locations,
    templates,
    form,
    loading,
    storing,
    templatesLoading,
    pagination,
    fetchLocations,
    createLocation,
    updateLocation,
    deleteLocation,
    fetchTemplates,
    resetForm,
    fillFormForEdit
  }
})
