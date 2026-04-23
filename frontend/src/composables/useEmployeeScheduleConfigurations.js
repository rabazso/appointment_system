import {
  createEmployeeSchedule,
  deleteEmployeeSchedule,
  getEmployeeSchedules,
  updateEmployeeSchedule,
} from '@/api/index'
import { ref } from 'vue'

export function useEmployeeScheduleConfigurations(employeeId) {
  const schedules = ref([])

  async function fetchSchedules() {
    const response = await getEmployeeSchedules(employeeId)
    schedules.value = response.data.data
    return schedules.value
  }

  async function saveSchedule(payload) {
    await createEmployeeSchedule(employeeId, payload)
    await fetchSchedules()
  }

  async function saveExistingSchedule(id, payload) {
    await updateEmployeeSchedule(id, payload)
    await fetchSchedules()
  }

  async function deleteSchedule(id) {
    await deleteEmployeeSchedule(id)
    await fetchSchedules()
  }

  return {
    schedules,
    fetchSchedules,
    saveSchedule,
    saveExistingSchedule,
    deleteSchedule,
  }
}
