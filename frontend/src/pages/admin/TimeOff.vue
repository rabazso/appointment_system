<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex min-h-0 flex-1 flex-col overflow-hidden p-4">
      <Header
        title="Time Off"
        description="Manage vacation, sick leave, and personal days"
        action-label="+ add time off"
        @menu-click="sidebarOpen = true"
        @action-click="openAddTimeOffModal"
      />

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
                <select v-model="calendarFilterStatus" class="min-h-11 appearance-none rounded-2xl font-medium border border-black/10 bg-white shadow-sm outline-none transition px-4 hover:border-black">
                  <option value="">All statuses</option>
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>

              <div class="relative">
                <select v-model="calendarFilterEmployee" class="min-h-11 appearance-none rounded-2xl font-medium border border-black/10 bg-white  shadow-sm outline-none transition px-4 hover:border-black">
                  <option value="">All employees</option>
                  <option v-for="employee in employees" :key="employee" :value="employee">
                    {{ employee }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="flex min-h-0 flex-1">
            <CalendarView
              :month="month"
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
              <p class="min-h-6 truncate font-semibold">{{ request.employee }}</p>
              <p class="mt-1 min-h-5 text-sm font-semibold">
                {{ request.date }}
              </p>
              <p class="mt-3 line-clamp-2 min-h-[2lh] text-sm leading-5">{{ request.note }}</p>

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
    </main>
  </div>
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
    @close="closeTimeOffModals"
    @save="saveTimeOff"
  />
</template>

<script setup>
import { computed, ref } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import CalendarView from '@/components/admin/calendar/CalendarView.vue'
import TimeOffCreateModal from '@/components/admin/TimeOffCreateModal.vue'
import TimeOffDayModal from '@/components/admin/TimeOffDayModal.vue'
import { shiftMonth } from '@/utils/date'
import { INITIAL_REQUESTS } from '@/data/calenderData'

const sidebarOpen = ref(false)
const viewMode = ref('calendar')
const month = ref(new Date())
const calendarFilterStatus = ref('')
const calendarFilterEmployee = ref('')
const requests = ref(INITIAL_REQUESTS.map((x) => ({ ...x })))
const showTimeOffDayModal = ref(false)
const showTimeOffCreateModal = ref(false)
const selectedTimeOffs = ref([])
const selectedDate = ref('')

const monthLabel = computed(() =>
  month.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric',
  }),
)

const employees = computed(() => {
  return new Set(requests.value.map((x) => x.employee))
})

const employeeOptions = computed(() => {
  return Array.from(employees.value)
})

const filteredRequests = computed(() => {
  return requests.value.filter((request) => request.status === 'pending')
})

const calendarRequests = computed(() => {
  return requests.value.filter((request) => {
    const statusMatch = calendarFilterStatus.value === '' || request.status === calendarFilterStatus.value
    const employeeMatch = calendarFilterEmployee.value === '' || request.employee === calendarFilterEmployee.value
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
  if (status === 'cancelled') return `${baseClass} border border-black bg-white text-black line-through`
  return `${baseClass}`
}

function getCalendarStatusDotClass(status) {
  if (status === 'approved') return 'bg-emerald-500'
  if (status === 'pending') return 'bg-gray-500'
  if (status === 'rejected') return 'bg-rose-500'
  if (status === 'cancelled') return 'border border-black-500 bg-transparent'
  return 'bg-black'
}

function goPrevMonth() {
  month.value = shiftMonth(month.value, -1)
}

function goNextMonth() {
  month.value = shiftMonth(month.value, 1)
}

function openAddTimeOffModal() {
  selectedTimeOffs.value = []
  selectedDate.value = ''
  showTimeOffDayModal.value = false
  showTimeOffCreateModal.value = true
}

function openTimeOffModalForDate(dateISO) {
  const timeOffs = calendarRequests.value.filter((request) => request.date === dateISO)
  const timeOff = timeOffs[0]

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
  showTimeOffDayModal.value = false
  showTimeOffCreateModal.value = true
}

function closeTimeOffModals() {
  showTimeOffDayModal.value = false
  showTimeOffCreateModal.value = false
  selectedTimeOffs.value = []
  selectedDate.value = ''
}

function saveTimeOff(timeOff) {
  for (const day of timeOff.days) {
    for (const employee of timeOff.employees) {
      requests.value.push(createRequestFromForm(timeOff, day, employee))
    }
  }

  closeTimeOffModals()
}

function changeTimeOffStatus({ id, status }) {
  const requestIndex = requests.value.findIndex((request) => request.id === id)
  if (requestIndex !== -1) {
    requests.value[requestIndex] = {
      ...requests.value[requestIndex],
      status,
    }
  }

  selectedTimeOffs.value = selectedTimeOffs.value.map((timeOff) => {
    if (timeOff.id !== id) return timeOff
    return {
      ...timeOff,
      status,
    }
  })

}

function createRequestFromForm(timeOff, date, employee, id = getNextRequestId()) {
  return {
    id,
    employee,
    type: timeOff.type,
    date,
    status: timeOff.status,
    note: timeOff.note,
  }
}

function getNextRequestId() {
  return Math.max(0, ...requests.value.map((request) => Number(request.id) || 0)) + 1
}
</script>
