import {
  getEmployeeTimeOffRequests,
  getEmployeeTimeOffRequestsByMonth,
  getEmployees,
  patchEmployeeTimeOffRequest,
  postEmployeeTimeOffRequest,
} from '@/api/index'

export function useTimeOff() {
  async function fetchEmployees() {
    const response = await getEmployees()

    return response.data.data.map((employee) => ({
      id: employee.id,
      name: employee.name,
    }))
  }

  async function fetchTimeOffRequests(month) {
    const response = await getEmployeeTimeOffRequestsByMonth(month)

    return mapTimeOffRequests(response.data.data)
  }

  async function fetchPendingTimeOffRequests() {
    const response = await getEmployeeTimeOffRequests({ status: 'pending' })

    return mapTimeOffRequests(response.data.data)
  }

  function mapTimeOffRequests(timeOffRequests) {
    return timeOffRequests.map((timeOff) => ({
      id: timeOff.id,
      employeeId: timeOff.employee_id,
      employee: timeOff.employee_name,
      type: timeOff.type,
      date: timeOff.date,
      status: timeOff.status,
      note: timeOff.note ?? '',
    }))
  }

  async function saveTimeOffRequests(timeOff) {
    for (const date of timeOff.days) {
      for (const employeeId of timeOff.employees) {
        await postEmployeeTimeOffRequest({
          employee_id: employeeId,
          date,
          type: timeOff.type,
          status: timeOff.status,
          note: timeOff.note || null,
        })
      }
    }
  }

  async function updateTimeOffStatus(id, status) {
    await patchEmployeeTimeOffRequest(id, { status })
  }

  return {
    fetchEmployees,
    fetchPendingTimeOffRequests,
    fetchTimeOffRequests,
    saveTimeOffRequests,
    updateTimeOffStatus,
  }
}
