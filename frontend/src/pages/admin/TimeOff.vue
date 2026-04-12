<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex min-h-0 flex-1 flex-col overflow-hidden p-4">
      <Header
        title="Time Off"
        description="Manage vacation, sick leave, and personal days"
        action-label="+ add request"
        @menu-click="sidebarOpen = true"
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
          <div class="mb-4 flex flex-wrap items-center gap-2">
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
              class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-black/10 bg-white text-gray-500 shadow-sm transition hover:border-black"
              @click="goNextMonth"
            >
              <ChevronRight class="h-4 w-4" />
            </button>
          </div>

          <div class="flex min-h-0 flex-1">
            <CalendarView
              :month="month"
              :cellMap="dayMap"
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

          <div class="mt-5 flex gap-3">
            <div class="flex-1 flex flex-col gap-1">
              <label class="font-semibold text-xs">Status</label>
              <select v-model="filterStatus" class="rounded-lg border border-black/10 bg-white px-2 py-1.5 text-sm">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="flex-1 flex flex-col gap-1">
              <label class="text-xs font-semibold">Employee</label>
              <select v-model="filterEmployee" class="rounded-lg border border-black/10 bg-white px-2 py-1.5 text-sm">
                <option value="">All</option>
                <option v-for="employee in employees" :key="employee" :value="employee">
                  {{ employee }}
                </option>
              </select>
            </div>

            <div class="flex-1 flex flex-col gap-1">
              <label class="text-xs font-semibold">Date</label>
              <input v-model="filterDate" type="date" class="rounded-lg border border-black/10 bg-white px-2 py-1.5 text-sm" />
            </div>
          </div>

          <div v-if="filteredRequests.length > 0" class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
              v-for="request in filteredRequests"
              :key="request.id"
              class="rounded-3xl border border-black/10 bg-white p-4 shadow-sm"
            >
              <p class="font-semibold">{{ request.employee }}</p>
              <p class="mt-1 text-sm font-semibold">
                {{ request.date }}
              </p>
              <p class="mt-3 line-clamp-3 min-h-10 text-sm">{{ request.note }}</p>

              <div class="mt-4 flex gap-3">
                <button
                  type="button"
                  class="flex-1 rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold transition hover:brightness-96"
                  @click=""
                >
                  Decline
                </button>

                <button
                  type="button"
                  class="flex-1 rounded-2xl bg-secondary px-4 py-2.5 text-sm font-semibold transition hover:brightness-96"
                  @click=""
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
</template>

<script setup>
import { computed, ref } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import CalendarView from '@/components/admin/calendar/CalendarView.vue'
import { shiftMonth, toISO } from '@/utils/date'
import { INITIAL_REQUESTS } from '@/data/calenderData'

const sidebarOpen = ref(false)
const viewMode = ref('calendar')
const month = ref(new Date())
const filterStatus = ref('')
const filterEmployee = ref('')
const filterDate = ref('')
const requests = ref(INITIAL_REQUESTS.map((x) => ({ ...x })))

const monthLabel = computed(() =>
  month.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric',
  }),
)

const employees = computed(() => {
    return new Set(requests.value.map(x => x.employee))
})

const filteredRequests = computed(() => {
  return requests.value.filter((request) => {
    const statusMatch = filterStatus.value === '' || request.status === filterStatus.value
    const employeeMatch = filterEmployee.value === '' || request.employee === filterEmployee.value
    const requestDate = request.date
    const dateMatch = filterDate.value === '' || requestDate === filterDate.value
    return statusMatch && employeeMatch && dateMatch
  })
})

const dayMap = computed(() => {
  const groupByDate = {}
  
  for (const item of requests.value) {
    const itemDate = item.date
    if (!groupByDate[itemDate]) {
      groupByDate[itemDate] = []
    }
    groupByDate[itemDate].push(item)
  }

  const contentMap = {}
  for (const [date, items] of Object.entries(groupByDate)) {
    contentMap[date] = {
      content: `${items.length} request${items.length !== 1 ? 's' : ''}`,
      contentClass: 'rounded-md bg-black/10 px-2 py-1 text-sm font-semibold',
      dotContent: true,
    }
  }

  return contentMap
})

function goPrevMonth() {
  month.value = shiftMonth(month.value, -1)
}

function goNextMonth() {
  month.value = shiftMonth(month.value, 1)
}
</script>
