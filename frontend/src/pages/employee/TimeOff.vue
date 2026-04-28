<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import PageLayout from '@/components/PageLayout.vue'
import { useToastStore } from '@/stores/ToastStore.js'
import {
  cancelEmployeeOwnTimeOffRequest,
  getEmployeeOwnTimeOffRequests,
  getEmployeeShopHolidaysByMonth,
  postEmployeeOwnTimeOffRequest,
} from '@/api/index.js'

const requests = ref([])
const holidays = ref([])
const showRequestModal = ref(false)
const viewMode = ref('requests')
const statusFilter = ref('all')
const dateOrder = ref('none')
const todayISO = new Date().toISOString().slice(0, 10)
const form = reactive(createForm())
const submitted = ref(false)
const toast = useToastStore()

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
const requestConflictMap = computed(() => {
  const blockingStatuses = new Set(['pending', 'approved', 'rejected'])
  const map = {}

  for (const day of form.days) {
    const matches = requests.value.filter((request) => request.date === day)
    const blocking = matches.find((request) => blockingStatuses.has(request.status))

    if (blocking) {
      map[day] = `This day already has a ${blocking.status} time off request.`
    }
  }

  return map
})
const requestHasConflict = computed(() => form.days.some((day) => Boolean(requestConflictMap.value[day])))
const filledDays = computed(() => form.days.filter(Boolean))
function createForm() {
  return {
    days: [todayISO],
    note: '',
  }
}

async function loadRequests() {
  try {
    const response = await getEmployeeOwnTimeOffRequests()
    requests.value = response.data.data ?? []
  } catch {
    requests.value = []
    toast.showError('Failed to load data.')
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
    toast.showError('Failed to load data.')
  }
}

async function cancelRequest(request) {
  if (request?.status !== 'pending') return

  try {
    await cancelEmployeeOwnTimeOffRequest(request.id)
    toast.show('Changes saved successfully.')
    await loadRequests()
  } catch {
    toast.showError('Failed to save changes.')
  }
}

async function submitRequest() {
  submitted.value = true

  if (!filledDays.value.length) {
    toast.showError('Please select at least one day.')
    return
  }

  if (requestHasConflict.value) {
    toast.showError('Please remove days that already have a time off request.')
    return
  }

  if (!form.note.trim()) {
    toast.showError('Please add a reason.')
    return
  }

  try {
    for (const date of filledDays.value) {
      await postEmployeeOwnTimeOffRequest({
        date,
        note: form.note.trim(),
      })
    }
    showRequestModal.value = false
    form.days = [todayISO]
    form.note = ''
    submitted.value = false
    toast.show('Changes saved successfully.')
    await loadRequests()
  } catch {
    toast.showError('Failed to save changes.')
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

function addDay() {
  form.days.push(todayISO)
}

function removeDay(index) {
  form.days.splice(index, 1)
}

function dayHasConflict(day) {
  return Boolean(requestConflictMap.value[day])
}

function dayErrorMessage(day) {
  return requestConflictMap.value[day] || ''
}

function openRequestModal() {
  submitted.value = false
  form.days = [todayISO]
  form.note = ''
  showRequestModal.value = true
}

function closeRequestModal() {
  showRequestModal.value = false
  submitted.value = false
}

onMounted(async () => {
  await Promise.all([loadRequests(), loadHolidays()])
})
</script>

<template>
  <PageLayout
    role="employee"
    title="Time Off"
    description="Track your requests and see shop holidays."
    action-label="Request Time Off"
    :show-action="true"
    @action-click="openRequestModal"
  >
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
      @click.self="closeRequestModal"
    >
      <div class="relative flex w-96 flex-col rounded-2xl bg-white p-4 pt-12">
        <button class="absolute top-2 right-2" @click="closeRequestModal">
          <X class="w-8 h-8" />
        </button>

        <h3 class="text-2xl font-semibold">Add time off</h3>

        <div class="mt-4 flex w-full min-w-0 flex-col gap-4">
          <div>
            <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Choose days</label>
            <div class="flex flex-col gap-2">
              <div
                v-for="(_, index) in form.days"
                :key="`time-off-day-${index}`"
                class="grid items-center gap-2"
                :class="{ '[grid-template-columns:minmax(0,1fr)_30px]': form.days.length > 1 }"
              >
                <input
                  v-model="form.days[index]"
                  type="date"
                  :min="todayISO"
                  class="mt-2 h-11 w-full rounded-lg border bg-white px-3 text-base outline-none transition hover:border-black"
                  :class="dayHasConflict(form.days[index]) ? 'border-rose-500 text-rose-600' : 'border-black/10'"
                />
                <button
                  v-if="form.days.length > 1"
                  type="button"
                  class="inline-flex h-[22px] w-[22px] items-center justify-center rounded-[6px] border border-[#d1d5db] bg-white p-0 text-[12px] font-medium leading-none text-[#475569] transition hover:border-black"
                  aria-label="Remove day"
                  @click="removeDay(index)"
                >
                  x
                </button>
                <p
                  v-if="submitted && dayErrorMessage(form.days[index])"
                  class="col-span-full whitespace-pre-line break-words text-xs leading-snug text-rose-500"
                >
                  {{ dayErrorMessage(form.days[index]) }}
                </p>
              </div>

              <div class="flex justify-end">
                <button
                  type="button"
                  class="rounded-lg border border-black/10 bg-white p-2 text-sm font-medium transition hover:border-black"
                  @click="addDay"
                >
                  + Add day
                </button>
              </div>
            </div>
          </div>

          <div>
            <label class="text-xs font-semibold tracking-wider text-gray-500 leading-none">Reason</label>
            <textarea
              v-model="form.note"
              rows="5"
              placeholder="Reason for time off"
              class="mt-2 w-full min-h-24 resize-none rounded-lg border bg-white px-3 py-2 text-base outline-none transition hover:border-black"
              :class="submitted && !form.note.trim() ? 'border-rose-500' : 'border-black/10'"
            />
            <p v-if="submitted && !form.note.trim()" class="mt-1 whitespace-normal break-words text-xs leading-snug text-rose-500">
              Required
            </p>
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
