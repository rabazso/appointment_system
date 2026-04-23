import {
  createEmployeeServices,
  deleteEmployeeServices,
  getEmployeeServices,
  updateEmployeeServices,
} from '@/api/index'
import { ref } from 'vue'

export function useEmployeeServiceConfigurations(employeeId) {
  const services = ref([])

  async function fetchServices() {
    const response = await getEmployeeServices(employeeId)
    services.value = response.data.data

    return services.value
  }

  async function saveService(payload) {
    await createEmployeeServices(employeeId, payload)
    await fetchServices()
  }

  async function saveExistingService(id, payload) {
    await updateEmployeeServices(id, payload)
    await fetchServices()
  }

  async function deleteService(id) {
    await deleteEmployeeServices(id)
    await fetchServices()
  }

  return {
    services,
    fetchServices,
    saveService,
    saveExistingService,
    deleteService,
  }
}
