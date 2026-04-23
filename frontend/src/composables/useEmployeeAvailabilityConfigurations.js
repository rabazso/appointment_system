import {
  createEmployeeAvailability,
  deleteEmployeeAvailability,
  getEmployeeAvailability,
  updateEmployeeAvailability,
} from '@/api/index'
import { ref } from 'vue'

export function useEmployeeAvailabilityConfigurations(employeeId) {
  const availability = ref([])

  async function fetchAvailability() {
    const response = await getEmployeeAvailability(employeeId)
    availability.value = response.data.data
    return availability.value
  }

  async function saveAvailability(payload) {
    await createEmployeeAvailability(employeeId, payload)
    await fetchAvailability()
  }

  async function saveExistingAvailability(id, payload) {
    await updateEmployeeAvailability(id, payload)
    await fetchAvailability()
  }

  async function deleteAvailability(id) {
    await deleteEmployeeAvailability(id)
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
