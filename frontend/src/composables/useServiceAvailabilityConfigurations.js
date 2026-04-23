import {
  createServiceAvailability,
  deleteServiceAvailability,
  getServiceAvailability,
  updateServiceAvailability,
} from '@/api/index'
import { ref } from 'vue'

export function useServiceAvailabilityConfigurations(serviceId) {
  const availability = ref([])

  async function fetchAvailability() {
    const response = await getServiceAvailability(serviceId)
    availability.value = response.data.data
    return availability.value
  }

  async function saveAvailability(payload) {
    await createServiceAvailability(serviceId, payload)
    await fetchAvailability()
  }

  async function saveExistingAvailability(id, payload) {
    await updateServiceAvailability(id, payload)
    await fetchAvailability()
  }

  async function deleteAvailability(id) {
    await deleteServiceAvailability(id)
    await fetchAvailability()
  }

  return {
    availability,
    fetchAvailability,
    saveAvailability,
    saveExistingAvailability,
    deleteAvailability,
  }
}
