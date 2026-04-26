<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Calendar } from '@/components/ui/calendar'
import { Button } from '@/components/ui/button'
import PageLayout from '@/components/employee/PageLayout.vue'
import { onClickOutside } from '@vueuse/core'
import { getLocalTimeZone, today } from '@internationalized/date'
import { Calendar as CalendarIcon } from 'lucide-vue-next'
import { cancelEmployeeOwnTimeOffRequest, getEmployeeOwnTimeOffRequests, postEmployeeOwnTimeOffRequest } from '@/api/index.js'

const errorMessage = ref('')
const successMessage = ref('')
const requests = ref([])
const selectedDate = ref(null)
const isDatePickerOpen = ref(false)
const datePickerWrapper = ref(null)
const statusFilter = ref('all')
const dateOrder = ref('none')

const formValues = ref({
  date: '',
  note: '',
})

const hasRequests = computed(() => requests.value.length > 0)
const visibleRequests = computed(() => {
  const filtered = requests.value.filter((request) => {
    if (statusFilter.value === 'all') {
      return true
    }

    return request?.status === statusFilter.value
  })

  if (dateOrder.value === 'none') {
    return filtered
  }

  const sorted = [...filtered].sort((a, b) => a.date.localeCompare(b.date))
  return dateOrder.value === 'asc' ? sorted : sorted.reverse()
})

const loadRequests = async () => {
  errorMessage.value = ''

  try {
    const response = await getEmployeeOwnTimeOffRequests()
    requests.value = response.data.data
  } catch (error) {
    requests.value = []
    errorMessage.value = error.response?.data?.message || 'Failed to load time off requests.'
  }
}

const submitRequest = async () => {
  const requestDate = selectedDate.value ? selectedDate.value.toString() : ''

  if (!requestDate) {
    errorMessage.value = 'Please fill in all required fields before submitting.'
    successMessage.value = ''
    return
  }

  errorMessage.value = ''
  successMessage.value = ''

  try {
    await postEmployeeOwnTimeOffRequest({
      date: requestDate,
      note: formValues.value.note.trim(),
    })

    successMessage.value = 'Time off request submitted.'
    selectedDate.value = null
    formValues.value.date = ''
    formValues.value.note = ''
    await loadRequests()
  } catch (error) {
    errorMessage.value = error.response?.data?.errors?.date?.[0]
      || error.response?.data?.message
      || 'Failed to submit time off request.'
  }
}

const cancelRequest = async (request) => {
  if (request?.status !== 'pending') {
    return
  }
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

const getStatusClass = (status) => {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-800'
  if (status === 'pending') return 'bg-gray-200 text-gray-800'
  if (status === 'rejected') return 'bg-rose-100 text-rose-800'
  if (status === 'cancelled') return 'border border-slate-400 bg-white text-slate-500 line-through'
}

watch(selectedDate, (value) => {
  formValues.value.date = value ? value.toString() : ''

  if (value) {
    isDatePickerOpen.value = false
  }
})

onClickOutside(datePickerWrapper, () => {
  isDatePickerOpen.value = false
})

onMounted(() => {
  loadRequests()
})
</script>

<template>
  <PageLayout
    current-section="time-off"
    title="Time Off"
    description="Create and track your own time off requests."
  >
    <div class="grid w-full gap-6 2xl:h-full 2xl:min-h-0 2xl:overflow-hidden 2xl:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)]">
      <section class="min-w-0 rounded-2xl bg-white p-6 md:p-8 h-fit">
      <h2 class="text-2xl font-semibold text-black">Request Time Off</h2>

      <p v-if="errorMessage" class="mt-4 rounded-md bg-red-100 px-4 py-2 text-sm text-red-700">
        {{ errorMessage }}
      </p>
      <p v-if="successMessage" class="mt-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-700">
        {{ successMessage }}
      </p>

      <FormKit
        v-model="formValues"
        type="form"
        :actions="false"
        :incomplete-message="false"
        @submit="submitRequest"
      >
        <div class="mt-6 grid gap-4 md:grid-cols-3">
          <FormKit type="hidden" name="date" />

          <label ref="datePickerWrapper" class="relative flex w-fit flex-col items-start gap-2">
            <span class="block text-sm font-medium text-slate-700">Date</span>
            <Button
              type="button"
              variant="outline"
              class="h-10 w-[220px] border-slate-300 bg-white px-3 text-sm font-normal text-slate-900 hover:bg-white !shadow-none [&_p]:m-0 [&_p]:flex [&_p]:w-full [&_p]:items-center [&_p]:justify-between"
              @click="isDatePickerOpen = !isDatePickerOpen"
            >
              <span>{{ selectedDate ? selectedDate : 'yyyy-mm-dd' }}</span>
              <CalendarIcon class="h-4 w-4 text-slate-500" />
            </Button>

            <div
              v-if="isDatePickerOpen"
              class="absolute left-0 top-full z-20 mt-2 rounded-lg border border-slate-200 bg-white p-2"
            >
              <Calendar
                v-model="selectedDate"
                class="rounded-md"
                layout="month-and-year"
                :min-value="today(getLocalTimeZone())"
              />
            </div>
          </label>

          <FormKit
            type="textarea"
            name="note"
            label="Reason"
            placeholder="Add reason for your request"
            :rows="5"
            outer-class="md:col-span-3"
            label-class="block text-sm font-medium text-slate-700"
            input-class="w-full rounded-lg border border-slate-300 !bg-white px-3 py-3 text-sm outline-none resize-none"
            messages-class="hidden"
          />

          <div class="md:col-span-3 flex justify-end">
            <Button
              type="submit"
              class="rounded-lg bg-black px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-70 !shadow-none"
            >
              {{ 'Submit Request' }}
            </Button>
          </div>
        </div>
      </FormKit>
      </section>

      <section class="min-w-0 rounded-2xl bg-white p-6 md:p-8 flex flex-col 2xl:h-full 2xl:min-h-0 2xl:overflow-hidden">
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
                {{ 'Cancel' }}
              </Button>
            </div>
          </article>
        </div>

        <div v-else class="flex h-full items-center justify-center rounded-lg text-center text-sm text-slate-500">
          <p>No requests match the selected filters.</p>
        </div>
      </div>
      </section>
    </div>
  </PageLayout>
</template>
