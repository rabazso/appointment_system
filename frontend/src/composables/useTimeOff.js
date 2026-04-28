import {
  getEmployeeTimeOffRequests,
  getEmployeeTimeOffRequestsByMonth,
  getEmployees,
  patchEmployeeTimeOffRequest,
  postEmployeeTimeOffRequest,
} from '@/api/index'
import { formatYearMonth } from '@/utils/date'

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

    return response.data.data
  }

  async function fetchPendingTimeOffRequests() {
    const response = await getEmployeeTimeOffRequests({ status: 'pending' })

    return response.data.data
  }

  async function saveTimeOffRequests(timeOff) {
    for (const date of timeOff.days) {
      for (const employeeId of timeOff.employees) {
        await postEmployeeTimeOffRequest({
          employee_id: employeeId,
          date,
          status: timeOff.status,
          note: timeOff.note || null,
        })
      }
    }
  }

  async function updateTimeOffStatus(id, status, note) {
    await patchEmployeeTimeOffRequest(id, {status, note,})
  }

  async function checkTimeOffConflicts(timeOff) {
    const employees = [...new Set((timeOff.employees || []).map((value) => String(value)).filter(Boolean))]
    const days = [...new Set((timeOff.days || []).filter(Boolean))]
    const blockingStatuses = new Set(['pending', 'approved', 'rejected'])

    const pairs = employees.flatMap((employeeId) => days.map((date) => ({ employeeId, date })))
    const checks = await Promise.all(
      pairs.map(async ({ employeeId, date }) => {
        const response = await getEmployeeTimeOffRequests({
          employee_id: employeeId,
          date,
        })

        const requests = response.data.data || []
        const hasConflict = requests.some((request) => blockingStatuses.has(request.status))

        return {
          employee_id: employeeId,
          date,
          hasConflict,
        }
      }),
    )

    return checks.filter((result) => result.hasConflict)
  }

  function parseMonthQuery(value) {
    if (typeof value !== 'string') return null
    if (value.length !== 7) return null

    const parts = value.split('-')
    if (parts.length !== 2) return null

    const [yearRaw, monthRaw] = parts
    if (yearRaw.length !== 4 || monthRaw.length !== 2) return null

    const year = Number(yearRaw)
    const month = Number(monthRaw)

    if (!Number.isInteger(year) || !Number.isInteger(month)) {
      return null
    }

    if (month < 1 || month > 12) {
      return null
    }

    return new Date(year, month - 1, 1)
  }

  function syncFiltersFromQuery(query, fallbackDate = new Date()) {
    return {
      displayMonth: parseMonthQuery(query?.month) ?? fallbackDate,
      status: typeof query?.status === 'string' ? query.status : '',
      employee: typeof query?.employee === 'string' ? query.employee : '',
    }
  }

  function buildQueryFromFilters(filters, defaultMonthKey = formatYearMonth(new Date())) {
    const query = {}
    const month = formatYearMonth(filters.displayMonth)

    if (month !== defaultMonthKey) {
      query.month = month
    }

    if (filters.status) {
      query.status = filters.status
    }

    if (filters.employee) {
      query.employee = filters.employee
    }

    return query
  }

  return {
    buildQueryFromFilters,
    fetchEmployees,
    fetchPendingTimeOffRequests,
    fetchTimeOffRequests,
    saveTimeOffRequests,
    checkTimeOffConflicts,
    syncFiltersFromQuery,
    updateTimeOffStatus,
  }
}
