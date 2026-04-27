import { computed, onMounted, reactive, ref, watch } from 'vue'
import { getAdminAppointments, getEmployees, getServices } from '@/api/index'
import { useRoute, useRouter } from 'vue-router'

export const adminAppointmentsKey = Symbol('adminAppointments')

export function useAdminAppointments() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const appointments = ref([])
  const services = ref([])
  const employees = ref([])
  const syncingFromRoute = ref(false)
  const filters = reactive({
    search: '',
    serviceId: '',
    employeeId: '',
    status: '',
    dateFrom: '',
    dateTo: '',
    orderBy: 'start_datetime',
    orderDirection: 'desc',
  })

  const statusOptions = [
    { value: '', label: 'All statuses' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'pending', label: 'Pending' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'no_show', label: 'No show' },
  ]

  const stats = computed(() => {
    const total = appointments.value.length
    const upcoming = appointments.value.filter((item) => ['pending', 'confirmed'].includes(item.status)).length
    const completed = appointments.value.filter((item) => item.status === 'completed').length
    const cancelled = appointments.value.filter((item) => ['cancelled', 'no_show'].includes(item.status)).length

    return [
      { label: 'Total', value: total },
      { label: 'Upcoming', value: upcoming },
      { label: 'Completed', value: completed },
      { label: 'Cancelled', value: cancelled },
    ]
  })

  function syncFiltersFromQuery(query) {
    filters.search = typeof query.search === 'string' ? query.search : ''
    filters.serviceId = typeof query.serviceId === 'string' ? query.serviceId : ''
    filters.employeeId = typeof query.employeeId === 'string' ? query.employeeId : ''
    filters.status = typeof query.status === 'string' ? query.status : ''
    filters.dateFrom = typeof query.dateFrom === 'string' ? query.dateFrom : ''
    filters.dateTo = typeof query.dateTo === 'string' ? query.dateTo : ''
    filters.orderBy = typeof query.orderBy === 'string' ? query.orderBy : 'start_datetime'
    filters.orderDirection = typeof query.orderDirection === 'string' ? query.orderDirection : 'desc'
  }

  function buildQueryFromFilters() {
    const query = {}

    if (filters.search) query.search = filters.search
    if (filters.serviceId) query.serviceId = filters.serviceId
    if (filters.employeeId) query.employeeId = filters.employeeId
    if (filters.status) query.status = filters.status
    if (filters.dateFrom) query.dateFrom = filters.dateFrom
    if (filters.dateTo) query.dateTo = filters.dateTo
    if (filters.orderBy !== 'start_datetime') query.orderBy = filters.orderBy
    if (filters.orderDirection !== 'desc') query.orderDirection = filters.orderDirection

    return query
  }

  function resetFilters() {
    filters.search = ''
    filters.serviceId = ''
    filters.employeeId = ''
    filters.status = ''
    filters.dateFrom = ''
    filters.dateTo = ''
    filters.orderBy = 'start_datetime'
    filters.orderDirection = 'desc'
  }

  async function loadAppointments() {
    loading.value = true
    try {
      const response = await getAdminAppointments({
        search: filters.search || undefined,
        service_id: filters.serviceId || undefined,
        employee_id: filters.employeeId || undefined,
        status: filters.status || undefined,
        date_from: filters.dateFrom || undefined,
        date_to: filters.dateTo || undefined,
        order_by: filters.orderBy || undefined,
        order_direction: filters.orderDirection || undefined,
      })

      appointments.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  async function loadServices() {
    services.value = (await getServices()).data.data
  }

  async function loadEmployees() {
    employees.value = (await getEmployees()).data.data
  }

  watch(
    () => route.query,
    (query) => {
      syncingFromRoute.value = true
      syncFiltersFromQuery(query)
      syncingFromRoute.value = false
      loadAppointments()
    },
    { immediate: true },
  )

  watch(
    filters,
    () => {
      if (syncingFromRoute.value) return
      const nextQuery = buildQueryFromFilters()
      router.replace({ query: nextQuery })
      loadAppointments()
    },
    { deep: true },
  )

  onMounted(async () => {
    await Promise.all([loadServices(), loadEmployees()])
  })

  return {
    appointments,
    employees,
    filters,
    loading,
    loadAppointments,
    loadEmployees,
    loadServices,
    resetFilters,
    services,
    stats,
    statusOptions,
  }
}
