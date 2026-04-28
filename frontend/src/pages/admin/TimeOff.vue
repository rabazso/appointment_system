<template>
  <PageLayout
    title="Time Off"
    description="Manage vacation, sick leave, and personal days"
    action-label="+ add time off"
    @action-click="openAddTimeOffModal"
  >

      <div class="mx-auto mb-4 flex rounded-2xl bg-white p-1 shadow-sm 2xl:hidden">
        <button
          type="button"
          class="rounded-md px-4 py-2 font-semibold transition"
          :class="viewMode === 'calendar' ? 'bg-secondary' : 'bg-white'"
          @click="viewMode = 'calendar'"
        >
          Calendar
        </button>
        <button
          type="button"
          class="rounded-md px-4 py-2 font-semibold transition"
          :class="viewMode === 'requests' ? 'bg-secondary' : 'bg-white'"
          @click="viewMode = 'requests'"
        >
          Requests
        </button>
      </div>

      <div class="flex min-h-0 w-full flex-1 flex-col gap-4 2xl:flex-row 2xl:overflow-hidden">
        <div
          :class="[viewMode === 'calendar' ? 'flex' : 'hidden', '2xl:flex']"
          class="w-full flex-1 flex-col rounded-2xl bg-white p-4"
        >
          <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-black/10 bg-white text-gray-500 shadow-sm transition hover:border-black"
                @click="goPrevMonth"
              >
                <ChevronLeft class="h-4 w-4" />
              </button>

              <div class="inline-flex min-h-11 min-w-[140px] items-center justify-center rounded-2xl border border-black/10 bg-white px-4 font-semibold shadow-sm">
                {{ monthLabel }}
              </div>

              <button
                type="button"
                class="inline-flex h-11 w-11  items-center justify-center rounded-2xl border border-black/10 bg-white text-gray-500 shadow-sm transition hover:border-black"
                @click="goNextMonth"
              >
                <ChevronRight class="h-4 w-4" />
              </button>
            </div>

            <div class="ml-auto flex flex-wrap items-end justify-end gap-2">
              <div class="relative">
                <Select v-model="calendarFilterStatusSelect">
                  <SelectTrigger>
                    <SelectValue placeholder="All statuses" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="ALL_SELECT_VALUE">All statuses</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="approved">Approved</SelectItem>
                    <SelectItem value="rejected">Rejected</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="relative">
                <Select v-model="calendarFilterEmployeeSelect">
                  <SelectTrigger>
                    <SelectValue placeholder="All employees" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="ALL_SELECT_VALUE">All employees</SelectItem>
                    <SelectItem v-for="employee in employeeOptions" :key="employee.id" :value="employee.name">
                      {{ employee.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>

          <div class="flex min-h-0 flex-1">
            <CalendarView
              :month="displayMonth"
              :cellMap="dayMap"
              @day-click="openTimeOffModalForDate"
            />
          </div>
        </div>

        <aside
          :class="[viewMode === 'requests' ? 'flex' : 'hidden', '2xl:flex']"
          class="w-full flex-col overflow-y-auto rounded-2xl bg-white p-5 shadow-sm 2xl:w-7/20"
        >
          <div class="flex items-center justify-between">
            <div>
            <h3 class="text-xl font-semibold">Requests</h3>
            <p class="mt-1 text-xs text-gray-500">You can see the requests here</p>
            </div>
            <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-white px-2 text-sm font-semibold text-gray-500 border border-black/10">
              {{ filteredRequests.length }}
            </span>
          </div>

          <div v-if="filteredRequests.length > 0" class="mt-5 grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(min(100%,15rem),1fr))]">
            <div
              v-for="request in filteredRequests"
              :key="request.id"
              class="flex min-h-48 flex-col rounded-3xl border border-black/10 bg-white p-4 shadow-sm"
            >
              <p class="min-h-6 truncate font-semibold">{{ request.employee_name }}</p>
              <p class="mt-1 min-h-5 text-sm font-semibold">
                {{ request.date }}
              </p>
              <p class="mt-3 line-clamp-2 min-h-[2lh] text-sm leading-5">{{ request.note ?? '' }}</p>

              <div class="mt-auto flex gap-3 pt-4">
                <button
                  type="button"
                  class="flex-1 rounded-xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold transition hover:brightness-96"
                  @click="changeTimeOffStatus({ id: request.id, status: 'rejected' })"
                >
                  Reject
                </button>

                <button
                  type="button"
                  class="flex-1 rounded-xl bg-secondary px-4 py-2.5 text-sm font-semibold transition hover:brightness-96"
                  @click="changeTimeOffStatus({ id: request.id, status: 'approved' })"
                >
                  Approve
                </button>
              </div>
            </div>
          </div>

          <div v-else class="flex-1 flex items-center justify-center">
            <p class="text-center text-sm text-gray-500">
              No requests.
            </p>
          </div>
        </aside>
      </div>
  </PageLayout>
  <TimeOffDayModal
    v-if="showTimeOffDayModal"
    :date="selectedDate"
    :time-offs="selectedTimeOffs"
    @close="closeTimeOffModals"
    @add="openCreateTimeOffForDate"
    @status-change="changeTimeOffStatus"
  />
  <TimeOffCreateModal
    v-if="showTimeOffCreateModal"
    :initial-date="selectedDate"
    :employees="employeeOptions"
    :conflict-employee-ids="conflictEmployeeIds"
    :conflict-messages-by-employee="timeOffValidationErrorsByEmployee"
    @close="closeTimeOffModals"
    @save="saveTimeOff"
  />
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import PageLayout from '@/components/admin/PageLayout.vue'
import CalendarView from '@/components/admin/calendar/CalendarView.vue'
import TimeOffCreateModal from '@/components/admin/TimeOffCreateModal.vue'
import TimeOffDayModal from '@/components/admin/TimeOffDayModal.vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { formatYearMonth, shiftMonth, toISO } from '@/utils/date'
import { useTimeOff } from '@/composables/useTimeOff'
import { useToastStore } from '@/stores/ToastStore.js'

const ALL_SELECT_VALUE = '__all__'
const viewMode = ref('calendar')
const route = useRoute()
const router = useRouter()
const syncingFromRoute = ref(false)
const displayMonth = ref(new Date())
const calendarFilterStatus = ref('')
const calendarFilterEmployee = ref('')
const calendarTimeOffs = ref([])
const pendingRequests = ref([])
const employeeOptions = ref([])
const showTimeOffDayModal = ref(false)
const showTimeOffCreateModal = ref(false)
const selectedTimeOffs = ref([])
const selectedDate = ref('')
const conflictEmployeeIds = ref([])
const timeOffValidationErrorsByEmployee = ref({})
const todayISO = toISO(new Date())
const toast = useToastStore()
const {
  buildQueryFromFilters,
  checkTimeOffConflicts,
  fetchEmployees,
  fetchPendingTimeOffRequests,
  fetchTimeOffRequests,
  saveTimeOffRequests,
  syncFiltersFromQuery,
  updateTimeOffStatus,
} = useTimeOff()

const monthLabel = computed(() =>
  displayMonth.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric',
  }),
)

const filteredRequests = computed(() => {
  return pendingRequests.value
})

const calendarFilterStatusSelect = computed({
  get: () => (calendarFilterStatus.value === '' ? ALL_SELECT_VALUE : calendarFilterStatus.value),
  set: (value) => {
    calendarFilterStatus.value = value === ALL_SELECT_VALUE ? '' : value
  },
})

const calendarFilterEmployeeSelect = computed({
  get: () => (calendarFilterEmployee.value === '' ? ALL_SELECT_VALUE : calendarFilterEmployee.value),
  set: (value) => {
    calendarFilterEmployee.value = value === ALL_SELECT_VALUE ? '' : value
  },
})

const calendarRequests = computed(() => {
  return calendarTimeOffs.value.filter((request) => {
    const statusMatch = calendarFilterStatus.value === '' || request.status === calendarFilterStatus.value
    const employeeMatch = calendarFilterEmployee.value === '' || request.employee_name === calendarFilterEmployee.value
    return statusMatch && employeeMatch
  })
})

const dayMap = computed(() => {
  const groupByDate = {}
  
  for (const item of calendarRequests.value) {
    const itemDate = item.date
    if (!groupByDate[itemDate]) {
      groupByDate[itemDate] = []
    }
    groupByDate[itemDate].push(item)
  }

  const contentMap = {}
  for (const [date, items] of Object.entries(groupByDate)) {
    const status = items[0]?.status
    const hasStatusContext = Boolean(calendarFilterEmployee.value || calendarFilterStatus.value)

    contentMap[date] = {
      content: `${items.length} request${items.length !== 1 ? 's' : ''}`,
      contentClass: hasStatusContext
        ? getCalendarStatusClass(status)
        : 'rounded-md bg-white px-2 py-1 text-sm font-semibold',
      dotContent: true,
      dotContentClass: hasStatusContext ? getCalendarStatusDotClass(status) : undefined,
    }
  }

  return contentMap
})

function getCalendarStatusClass(status) {
  const baseClass = 'rounded-md px-2 py-1 text-sm font-semibold'

  if (status === 'approved') return `${baseClass} bg-emerald-100 text-emerald-900`
  if (status === 'pending') return `${baseClass} bg-gray-200 text-gray-800`
  if (status === 'rejected') return `${baseClass} bg-rose-100 text-rose-900`
  if (status === 'cancelled') return `${baseClass} border border-black/10 bg-white text-black line-through`
  return `${baseClass}`
}

function getCalendarStatusDotClass(status) {
  if (status === 'approved') return 'bg-emerald-500'
  if (status === 'pending') return 'bg-gray-500'
  if (status === 'rejected') return 'bg-rose-500'
  if (status === 'cancelled') return 'border border-black-500 bg-transparent'
  return 'bg-black'
}

function syncStateFromQuery(query) {
  const nextState = syncFiltersFromQuery(query, new Date())
  displayMonth.value = nextState.displayMonth
  calendarFilterStatus.value = nextState.status
  calendarFilterEmployee.value = nextState.employee
}

function goPrevMonth() {
  displayMonth.value = shiftMonth(displayMonth.value, -1)
}

function goNextMonth() {
  displayMonth.value = shiftMonth(displayMonth.value, 1)
}

function openAddTimeOffModal() {
  selectedTimeOffs.value = []
  selectedDate.value = todayISO
  conflictEmployeeIds.value = []
  timeOffValidationErrorsByEmployee.value = {}
  showTimeOffDayModal.value = false
  showTimeOffCreateModal.value = true
}

function openTimeOffModalForDate(dateISO) {
  const timeOffs = calendarRequests.value.filter((request) => request.date === dateISO)
  const timeOff = timeOffs[0]

  if (!timeOff && dateISO < todayISO) {
    return
  }

  selectedTimeOffs.value = timeOffs.map((item) => ({ ...item }))
  selectedDate.value = dateISO

  if (timeOff) {
    showTimeOffCreateModal.value = false
    showTimeOffDayModal.value = true
    return
  }

  showTimeOffDayModal.value = false
  showTimeOffCreateModal.value = true
}

function openCreateTimeOffForDate() {
  conflictEmployeeIds.value = []
  timeOffValidationErrorsByEmployee.value = {}
  showTimeOffDayModal.value = false
  showTimeOffCreateModal.value = true
}

function closeTimeOffModals() {
  showTimeOffDayModal.value = false
  showTimeOffCreateModal.value = false
  selectedTimeOffs.value = []
  selectedDate.value = ''
  conflictEmployeeIds.value = []
  timeOffValidationErrorsByEmployee.value = {}
}

async function saveTimeOff(timeOff) {
  try {
    conflictEmployeeIds.value = []
    timeOffValidationErrorsByEmployee.value = {}

    const conflicts = await checkTimeOffConflicts(timeOff)

    if (conflicts.length > 0) {
      const uniqueEmployeeIds = [...new Set(conflicts.map((conflict) => String(conflict.employee_id)))]
      conflictEmployeeIds.value = uniqueEmployeeIds
      const grouped = conflicts.reduce((accumulator, conflict) => {
        const employeeId = String(conflict.employee_id)
        if (!accumulator[employeeId]) {
          accumulator[employeeId] = new Set()
        }
        accumulator[employeeId].add(conflict.date)
        return accumulator
      }, {})

      const messages = {}
      for (const [employeeId, datesSet] of Object.entries(grouped)) {
        const dates = [...datesSet].sort()
        messages[employeeId] = dates
          .map((date) => `This employee already has a request on ${date}.`)
          .join('\n')
      }

      timeOffValidationErrorsByEmployee.value = messages
      return
    }

    await saveTimeOffRequests(timeOff)
    await loadTimeOffRequests()
    await loadPendingRequests()
    closeTimeOffModals()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

async function changeTimeOffStatus({ id, status }) {
  if (selectedDate.value && selectedDate.value < todayISO) {
    return
  }

  try {
    await updateTimeOffStatus(id, status)
    await loadTimeOffRequests()
    await loadPendingRequests()
    selectedTimeOffs.value = selectedTimeOffs.value.map((timeOff) => (
      timeOff.id === id ? { ...timeOff, status } : timeOff
    ))
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}

async function loadEmployees() {
  try {
    employeeOptions.value = await fetchEmployees()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
}

async function loadTimeOffRequests() {
  try {
    calendarTimeOffs.value = await fetchTimeOffRequests(formatYearMonth(displayMonth.value))
  } catch (error) {
    toast.showError('Failed to load data.')
  }
}

async function loadPendingRequests() {
  try {
    pendingRequests.value = await fetchPendingTimeOffRequests()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
}

watch(
  () => route.query,
  (query) => {
    syncingFromRoute.value = true
    syncStateFromQuery(query)
    syncingFromRoute.value = false
    loadTimeOffRequests()
  },
  { immediate: true },
)

watch(
  [displayMonth, calendarFilterStatus, calendarFilterEmployee],
  () => {
    if (syncingFromRoute.value) return
    router.replace({
      query: buildQueryFromFilters(
        {
          displayMonth: displayMonth.value,
          status: calendarFilterStatus.value,
          employee: calendarFilterEmployee.value,
        },
        formatYearMonth(new Date()),
      ),
    })
  },
)

onMounted(() => {
  loadEmployees()
  loadPendingRequests()
})
</script>
