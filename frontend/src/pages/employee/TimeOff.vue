<script setup>
import { computed, onMounted, ref } from 'vue'
import { parseDate } from '@internationalized/date'
import { PopoverContent, PopoverPortal, PopoverRoot, PopoverTrigger } from 'reka-ui'
import { X, Calendar as CalendarIcon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import PageLayout from '@/components/employee/PageLayout.vue'
import {
  cancelEmployeeOwnTimeOffRequest,
  getEmployeeOwnTimeOffRequests,
  getEmployeeShopHolidaysByMonth,
  postEmployeeOwnTimeOffRequest,
} from '@/api/index.js'

const errorMessage = ref('')
const successMessage = ref('')
const requests = ref([])
const holidays = ref([])
const showRequestModal = ref(false)
const requestDate = ref('')
const requestNote = ref('')
const isRequestDatePickerOpen = ref(false)
const viewMode = ref('requests')
const statusFilter = ref('all')
const dateOrder = ref('none')

const hasRequests = computed(() => requests.value.length > 0)
const visibleRequests = computed(() => {
  const filtered = requests.value.filter((request) => {
    if (statusFilter.value === 'all') return true
    return request?.status === statusFilter.value
  })

  if (dateOrder.value === 'none') return filtered

  const sorted = [...filtered].sort((a, b) => a.date.localeCompare(b.date))
  return dateOrder.value === 'asc' ? sorted : sorted.reverse()
})

const hasHolidays = computed(() => holidays.value.length > 0)
const calendarRequestDate = computed(() => {
  if (!requestDate.value) return undefined
  try {
    return parseDate(requestDate.value)
  } catch {
    return undefined
  }
})

async function loadRequests() {
  try {
    const response = await getEmployeeOwnTimeOffRequests()
    requests.value = response.data.data ?? []
  } catch (error) {
    requests.value = []
    errorMessage.value = error.response?.data?.message || 'Failed to load time off requests.'
  }
}

async function loadHolidays() {
  try {
    const today = new Date()
    const months = [0, 1, 2].map((offset) => {
      const date = new Date(today.getFullYear(), today.getMonth() + offset, 1)
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
    })

    const responses = await Promise.all(months.map((month) => getEmployeeShopHolidaysByMonth(month)))
    const merged = responses.flatMap((response) => response.data?.data ?? [])

    const byId = new Map()
    for (const holiday of merged) {
      byId.set(String(holiday.id), holiday)
    }

    holidays.value = [...byId.values()].sort((a, b) => String(a.date).localeCompare(String(b.date)))
  } catch {
    holidays.value = []
    errorMessage.value = 'Failed to load holidays.'
  }
}

async function cancelRequest(request) {
  if (request?.status !== 'pending') return

  errorMessage.value = ''
  successMessage.value = ''

  try {
    await cancelEmployeeOwnTimeOffRequest(request.id)
    successMessage.value = 'Pending request cancelled.'
    await loadRequests()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to cancel request.'
  }
}

async function submitRequest() {
  if (!requestDate.value) {
    errorMessage.value = 'Please select a date.'
    successMessage.value = ''
    return
  }

  errorMessage.value = ''
  successMessage.value = ''

  try {
    await postEmployeeOwnTimeOffRequest({
      date: requestDate.value,
      note: requestNote.value.trim(),
    })
    showRequestModal.value = false
    requestDate.value = ''
    requestNote.value = ''
    successMessage.value = 'Time off request submitted.'
    await loadRequests()
  } catch (error) {
    errorMessage.value = error.response?.data?.errors?.date?.[0]
      || error.response?.data?.message
      || 'Failed to submit time off request.'
  }
}

function getStatusClass(status) {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-800'
  if (status === 'pending') return 'bg-gray-200 text-gray-800'
  if (status === 'rejected') return 'bg-rose-100 text-rose-800'
  if (status === 'cancelled') return 'border border-slate-400 bg-white text-slate-500 line-through'
  return 'bg-slate-100 text-slate-700'
}

function getHolidayStatusClass(isOpen) {
  return isOpen ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
}

function setRequestDate(value) {
  requestDate.value = value?.toString?.() || ''
  isRequestDatePickerOpen.value = false
}

onMounted(async () => {
  await Promise.all([loadRequests(), loadHolidays()])
})
</script>

<template>
  <PageLayout
    current-section="time-off"
    title="Time Off"
    description="Track your requests and see shop holidays."
    action-label="Request Time Off"
    :show-action="true"
    @action-click="showRequestModal = true"
  >
    <p v-if="errorMessage" class="mb-4 rounded-md bg-red-100 px-4 py-2 text-sm text-red-700">
      {{ errorMessage }}
    </p>
    <p v-if="successMessage" class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-700">
      {{ successMessage }}
    </p>

    <div class="mx-auto mb-4 flex rounded-2xl bg-white p-1 shadow-sm 2xl:hidden">
      <button
        type="button"
        class="rounded-md px-4 py-2 font-semibold transition"
        :class="viewMode === 'requests' ? 'bg-secondary' : 'bg-white'"
        @click="viewMode = 'requests'"
      >
        Your Requests
      </button>
      <button
        type="button"
        class="rounded-md px-4 py-2 font-semibold transition"
        :class="viewMode === 'holidays' ? 'bg-secondary' : 'bg-white'"
        @click="viewMode = 'holidays'"
      >
        Holidays
      </button>
    </div>

    <div class="grid w-full gap-6 2xl:h-full 2xl:min-h-0 2xl:grid-cols-2">
      <section
        :class="[viewMode === 'requests' ? 'flex' : 'hidden', '2xl:flex']"
        class="min-w-0 rounded-2xl bg-white p-6 md:p-8 flex-col 2xl:h-full 2xl:min-h-0"
      >
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-2xl font-semibold text-black">Your Requests</h2>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
            {{ visibleRequests.length }}
          </span>
        </div>

        <div class="mt-4 flex items-end justify-end gap-2">
          <select
            v-model="statusFilter"
            class="h-10 min-w-[150px] rounded-xl border border-black/10 bg-white px-3 text-sm font-medium outline-none transition hover:border-black"
          >
            <option value="all">All statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <select
            v-model="dateOrder"
            class="h-10 min-w-[150px] rounded-xl border border-black/10 bg-white px-3 text-sm font-medium outline-none transition hover:border-black"
          >
            <option value="none">Default order</option>
            <option value="asc">Date ascending</option>
            <option value="desc">Date descending</option>
          </select>
        </div>

        <div class="mt-4 2xl:flex-1 2xl:min-h-0 2xl:overflow-y-auto">
          <div v-if="!hasRequests" class="flex h-full items-center justify-center rounded-lg text-center text-sm text-slate-500">
            <p>No time off requests yet.</p>
          </div>

          <div v-else-if="visibleRequests.length > 0" class="grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(min(100%,15rem),1fr))]">
            <article
              v-for="request in visibleRequests"
              :key="request.id"
              class="flex min-h-48 flex-col rounded-3xl border border-black/10 bg-white p-4"
            >
              <div class="flex items-start justify-between gap-3">
                <p class="text-base font-semibold text-slate-900">{{ request.date }}</p>
                <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize', getStatusClass(request.status)]">
                  {{ request.status }}
                </span>
              </div>

              <p class="mt-3 line-clamp-3 min-h-[3lh] text-sm leading-5 text-slate-600">
                {{ request.note }}
              </p>

              <div class="mt-auto pt-4">
                <Button
                  v-if="request.status === 'pending'"
                  type="button"
                  variant="default"
                  size="sm"
                  class="w-full rounded-xl bg-secondary px-4 py-2.5 text-sm font-semibold text-black transition hover:brightness-96 disabled:cursor-not-allowed disabled:opacity-60 !shadow-none"
                  @click="cancelRequest(request)"
                >
                  Cancel
                </Button>
              </div>
            </article>
          </div>

          <div v-else class="flex h-full items-center justify-center rounded-lg text-center text-sm text-slate-500">
            <p>No requests match the selected filters.</p>
          </div>
        </div>
      </section>

      <section
        :class="[viewMode === 'holidays' ? 'flex' : 'hidden', '2xl:flex']"
        class="min-w-0 rounded-2xl bg-white p-6 md:p-8 flex-col 2xl:h-full 2xl:min-h-0"
      >
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-2xl font-semibold text-black">Holidays</h2>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
            {{ holidays.length }}
          </span>
        </div>
        <p class="mt-1 text-sm text-slate-500">Shop special days for the next 3 months.</p>

        <div class="mt-4 2xl:flex-1 2xl:min-h-0 2xl:overflow-y-auto">
          <div v-if="!hasHolidays" class="flex h-full items-center justify-center rounded-lg text-center text-sm text-slate-500">
            <p>No holidays found.</p>
          </div>

          <div v-else class="grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(min(100%,15rem),1fr))]">
            <article
              v-for="holiday in holidays"
              :key="holiday.id"
              class="flex min-h-40 flex-col rounded-3xl border border-black/10 bg-white p-4"
            >
              <div class="flex items-start justify-between gap-3">
                <p class="text-base font-semibold text-slate-900">{{ holiday.date }}</p>
                <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', getHolidayStatusClass(holiday.is_open)]">
                  {{ holiday.is_open ? 'Open' : 'Closed' }}
                </span>
              </div>

              <p class="mt-3 text-sm font-semibold text-slate-800">{{ holiday.name }}</p>
              <p class="mt-2 text-sm text-slate-600">
                {{ holiday.is_open ? `${holiday.open_time} - ${holiday.close_time}` : 'Closed all day' }}
              </p>
            </article>
          </div>
        </div>
      </section>
    </div>

    <div
      v-if="showRequestModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
      @click.self="showRequestModal = false"
    >
      <div class="relative flex w-96 flex-col rounded-2xl bg-white p-4 pt-12">
        <button class="absolute top-2 right-2" @click="showRequestModal = false">
          <X class="w-8 h-8" />
        </button>

        <h3 class="text-2xl font-semibold">Add time off</h3>

        <div class="mt-4 flex w-full min-w-0 flex-col gap-4">
          <div>
            <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Choose a day</label>
            <PopoverRoot :open="isRequestDatePickerOpen" @update:open="(open) => { isRequestDatePickerOpen = open }">
              <PopoverTrigger as-child>
                <button
                  type="button"
                  class="mt-2 h-11 w-full rounded-lg border border-black/10 bg-white px-3 text-base outline-none transition hover:border-black [font-variant-numeric:tabular-nums] flex items-center justify-between"
                >
                  <span>{{ requestDate || 'YYYY-MM-DD' }}</span>
                  <CalendarIcon class="h-4 w-4 text-slate-500" />
                </button>
              </PopoverTrigger>
              <PopoverPortal>
                <PopoverContent
                  side="bottom"
                  align="start"
                  :side-offset="6"
                  :collision-padding="12"
                  position-strategy="fixed"
                  class="z-[90] w-auto rounded-xl border border-slate-200 bg-white p-2 shadow-lg"
                >
                  <Calendar
                    :model-value="calendarRequestDate"
                    layout="month-and-year"
                    class="rounded-md"
                    @update:model-value="setRequestDate"
                  />
                </PopoverContent>
              </PopoverPortal>
            </PopoverRoot>
          </div>

          <div>
            <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Reason</label>
            <textarea
              v-model="requestNote"
              rows="5"
              placeholder="Reason for time off"
              class="mt-2 w-full min-h-24 resize-none rounded-lg border border-black/10 bg-white px-3 py-2 text-base outline-none transition hover:border-black"
            />
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <Button
            type="button"
            class="rounded-lg bg-secondary px-5 text-black !shadow-none"
            @click="submitRequest"
          >
            Save
          </Button>
        </div>
      </div>
    </div>
  </PageLayout>
</template>
