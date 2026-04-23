import {
  createEmployeeBookingRules,
  deleteEmployeeBookingRules,
  getEmployeeBookingRules,
  updateEmployeeBookingRules,
} from '@/api/index'
import { ref } from 'vue'

export function useEmployeeBookingRuleConfigurations(employeeId) {
  const bookingRules = ref([])

  async function fetchBookingRules() {
    const response = await getEmployeeBookingRules(employeeId)
    bookingRules.value = response.data.data
    return bookingRules.value
  }

  async function saveBookingRules(payload) {
    await createEmployeeBookingRules(employeeId, payload)
    await fetchBookingRules()
  }

  async function saveExistingBookingRules(id, payload) {
    await updateEmployeeBookingRules(id, payload)
    await fetchBookingRules()
  }

  async function deleteBookingRules(id) {
    await deleteEmployeeBookingRules(id)
    await fetchBookingRules()
  }

  return {
    bookingRules,
    fetchBookingRules,
    saveBookingRules,
    saveExistingBookingRules,
    deleteBookingRules,
  }
}
