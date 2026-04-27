<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex-1 w-full overflow-y-auto p-8">
      <Header
        title="Appointments"
        description="Browse and filter bookings across your shop."
        @menu-click="sidebarOpen = true"
      />

      <div class="flex w-full flex-col gap-6 xl:grid xl:grid-cols-[minmax(0,1fr)_20rem] xl:items-start">
        <section class="order-1 min-w-0 rounded-2xl bg-white p-5 shadow-lg">
          <div class="flex flex-col gap-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
              <article
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-2xl border border-black/10 bg-slate-50 px-4 py-4"
              >
                <p class="text-sm font-medium text-slate-500">{{ stat.label }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">{{ stat.value }}</p>
              </article>
            </div>

            <div class="overflow-hidden rounded-2xl border border-black/10">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-black/10">
                  <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                      <th class="px-4 py-3">Customer</th>
                      <th class="px-4 py-3">Date & time</th>
                      <th class="px-4 py-3">Staff</th>
                      <th class="px-4 py-3">Service</th>
                      <th class="px-4 py-3">Duration</th>
                      <th class="px-4 py-3 text-right">Price</th>
                      <th class="px-4 py-3">Status</th>
                    </tr>
                  </thead>

                  <tbody class="divide-y divide-black/10 bg-white">
                    <tr v-if="loading">
                      <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                        Loading appointments...
                      </td>
                    </tr>

                    <tr v-else-if="appointments.length === 0">
                      <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                        No appointments match these filters.
                      </td>
                    </tr>

                    <tr
                      v-for="appointment in appointments"
                      :key="appointment.id"
                      class="cursor-pointer hover:bg-slate-50/80"
                      @click="openAppointmentModal(appointment)"
                    >
                      <td class="px-4 py-4">
                        <div class="min-w-0">
                          <p class="truncate font-semibold text-slate-950">{{ appointment.customer?.name || 'Guest' }}</p>
                          <p class="truncate text-sm text-slate-500">{{ appointment.customer?.email || 'No email' }}</p>
                        </div>
                      </td>
                      <td class="px-4 py-4">
                        <p class="font-semibold text-slate-950">{{ formatDate(appointment.start_datetime) }}</p>
                        <p class="text-sm text-slate-500">{{ formatTimeRange(appointment.start_datetime, appointment.end_datetime) }}</p>
                      </td>
                      <td class="px-4 py-4 text-sm font-medium text-slate-950">
                        {{ appointment.employee?.name || 'Unknown' }}
                      </td>
                      <td class="px-4 py-4">
                        <div class="min-w-0">
                          <p class="truncate font-semibold text-slate-950">{{ serviceSummary(appointment.services) }}</p>
                          <p class="truncate text-sm text-slate-500">{{ appointment.services.length }} service{{ appointment.services.length === 1 ? '' : 's' }}</p>
                        </div>
                      </td>
                      <td class="px-4 py-4 text-sm text-slate-700">
                        {{ appointment.total_duration ?? sumDuration(appointment.services) }} min
                      </td>
                      <td class="px-4 py-4 text-right text-sm font-semibold text-slate-950">
                        ${{ formatPrice(appointment.price) }}
                      </td>
                      <td class="px-4 py-4">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(appointment.status)">
                          {{ prettyStatus(appointment.status) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>

        <aside class="order-2 w-full shrink-0 xl:sticky xl:top-8">
          <div class="rounded-2xl bg-white p-5 shadow-lg">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-slate-950">Filters</h2>
              <button
                type="button"
                class="text-sm font-medium text-slate-500 transition hover:text-slate-900"
                @click="resetFilters"
              >
                Reset
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Customer, employee, service"
                  class="mt-2 w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                />
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Service</label>
                <select
                  v-model="filters.serviceId"
                  class="mt-2 w-full appearance-none rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                >
                  <option value="">All services</option>
                  <option v-for="service in services" :key="service.id" :value="String(service.id)">
                    {{ service.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employee</label>
                <select
                  v-model="filters.employeeId"
                  class="mt-2 w-full appearance-none rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                >
                  <option value="">All employees</option>
                  <option v-for="employee in employees" :key="employee.id" :value="String(employee.id)">
                    {{ employee.name }}
                  </option>
                </select>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">From</label>
                  <input
                    v-model="filters.dateFrom"
                    type="date"
                    class="mt-2 w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                  />
                </div>

                <div>
                  <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">To</label>
                  <input
                    v-model="filters.dateTo"
                    type="date"
                    class="mt-2 w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                  />
                </div>
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select
                  v-model="filters.status"
                  class="mt-2 w-full appearance-none rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                >
                  <option v-for="option in statusOptions" :key="option.value || 'all'" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quick date filter</label>
                <div class="mt-2 grid grid-cols-3 gap-2">
                  <button
                    v-for="option in quickDateOptions"
                    :key="option.value"
                    type="button"
                    class="rounded-xl border px-3 py-2 text-sm font-medium transition"
                    :class="filters.quickDate === option.value ? 'border-slate-950 bg-slate-950 text-white' : 'border-black/10 bg-white text-slate-700 hover:border-black'"
                    @click="setQuickDate(option.value)"
                  >
                    {{ option.label }}
                  </button>
                </div>
              </div>

              <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Order by</label>
                <div class="mt-2 grid grid-cols-2 gap-2">
                  <select
                    v-model="filters.orderBy"
                    class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                  >
                    <option value="start_datetime">Date & time</option>
                    <option value="created_at">Created at</option>
                    <option value="price">Price</option>
                    <option value="status">Status</option>
                    <option value="customer">Customer</option>
                    <option value="employee">Employee</option>
                  </select>

                  <select
                    v-model="filters.orderDirection"
                    class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                  >
                    <option value="desc">Desc</option>
                    <option value="asc">Asc</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </aside>
      </div>

      <ModalShell v-if="selectedAppointment" content-class="max-w-2xl" @close="closeAppointmentModal">
        <div class="overflow-y-auto pb-3 p-4">
            <div class="flex flex-col gap-4 border-b border-black/10 pb-5">
              <div class="flex items-center justify-between gap-3">
                <p class="text-2xl font-semibold text-slate-950">Appointment details</p>
                <span
                  class="ml-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="statusClass(selectedAppointment.status)"
                >
                  {{ prettyStatus(selectedAppointment.status) }}
                </span>
              </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
              <section class="rounded-2xl border border-black/10 bg-white p-4 md:p-5">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500"> Appointment</h4>
                <div class="mt-4 grid gap-2 text-sm text-slate-700">
                  <p><span class="font-semibold text-slate-950">Date:</span> {{ formatDate(selectedAppointment.start_datetime) }}</p>
                  <p><span class="font-semibold text-slate-950">Start:</span> {{ formatTime(selectedAppointment.start_datetime) }} <span class="font-semibold text-slate-950">End:</span> {{ formatTime(selectedAppointment.end_datetime) }}</p>
                  <p><span class="font-semibold text-slate-950">Total Duration:</span> {{ selectedAppointment.total_duration ?? sumDuration(selectedAppointment.services) }} min</p>
                  <p><span class="font-semibold text-slate-950">Total Price:</span> ${{ formatPrice(selectedAppointment.price) }}</p>
                  <p><span class="font-semibold text-slate-950">Employee:</span> {{ selectedAppointment.employee?.name || 'Unknown' }}</p>
                </div>
              </section>

              <section class="rounded-2xl border border-black/10 bg-white p-4 md:p-5">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</h4>
                <div class="mt-4 grid gap-2 text-sm text-slate-700">
                  <p><span class="font-semibold text-slate-950">Name:</span> {{ selectedAppointment.customer?.name || 'Guest' }}</p>
                  <p><span class="font-semibold text-slate-950">Email:</span> {{ selectedAppointment.customer?.email || 'No email' }}</p>
                </div>
              </section>
            </div>

            <section class="mt-4 rounded-2xl border border-black/10 bg-white p-4 md:p-5">
              <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Services</h4>
              <div class="mt-4 overflow-hidden rounded-2xl">
                <table class="min-w-full divide-y divide-black/10 text-sm">
                  <thead class=" text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                      <th class="px-3 py-2 font-semibold"></th>
                      <th class="px-3 py-2 font-semibold">Duration</th>
                      <th class="px-3 py-2 font-semibold text-right">Price</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-black/10 bg-white">
                    <tr v-for="service in selectedAppointment.services" :key="service.id || service.name">
                      <td class="px-3 py-3 text-slate-950">{{ service.name }}</td>
                      <td class="px-3 py-3 text-slate-700">{{ service.duration }} min</td>
                      <td class="px-3 py-3 text-right text-slate-700">${{ formatPrice(service.price) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
              <section class="rounded-2xl border border-black/10 bg-white p-4 md:p-5">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cancellation info</h4>
                <div class="mt-4 grid gap-2 text-sm text-slate-700">
                  <p><span class="font-semibold text-slate-950">Cancelled by:</span> {{ selectedAppointment.cancelled_by || 'N/A' }}</p>
                  <p><span class="font-semibold text-slate-950">Cancellation reason:</span> {{ selectedAppointment.cancellation_reason || 'N/A' }}</p>
                  <p><span class="font-semibold text-slate-950">Created at:</span> {{ formatDateTime(selectedAppointment.created_at) || 'N/A' }}</p>
                </div>
              </section>

              <section class="rounded-2xl border border-black/10 bg-white p-4 md:p-5">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</h4>
                <div class="mt-4 text-sm text-slate-500">
                  <p>{{ selectedAppointment.notes || 'No notes added.' }}</p>
                </div>
              </section>
            </div>
          </div>

          <template
            #footer
          >
            <div
              v-if="selectedAppointment && (canCancel(selectedAppointment) || canComplete(selectedAppointment) || canMarkNoShow(selectedAppointment))"
              class="flex w-full flex-wrap items-center justify-end gap-3"
            >
              <div class="flex flex-wrap justify-end gap-2">
                <button
                  v-if="canMarkNoShow(selectedAppointment)"
                  type="button"
                  class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-950"
                  @click="runAction('no-show')"
                >
                  No-show
                </button>
                <button
                  v-if="canCancel(selectedAppointment)"
                  type="button"
                  class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                  @click="runAction('cancel')"
                >
                  Cancel
                </button>
                <button
                  v-if="canComplete(selectedAppointment)"
                  type="button"
                  class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                  @click="runAction('complete')"
                >
                  Complete
                </button>
              </div>
            </div>
          </template>
      </ModalShell>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { Calendar, User } from 'lucide-vue-next'
import {
  cancelAdminAppointment,
  completeAdminAppointment,
  getAdminAppointments,
  getEmployees,
  getServices,
  noShowAdminAppointment,
} from '@/api/index'
import Header from '@/components/admin/Header.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import Sidebar from '@/components/admin/Sidebar.vue'

const sidebarOpen = ref(false)
const loading = ref(false)
const appointments = ref([])
const services = ref([])
const employees = ref([])
const selectedAppointmentId = ref(null)

const filters = reactive({
  search: '',
  serviceId: '',
  employeeId: '',
  status: '',
  dateFrom: '',
  dateTo: '',
  quickDate: '',
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

const quickDateOptions = [
  { value: 'today', label: 'Today' },
  { value: 'this_week', label: 'This week' },
  { value: 'this_month', label: 'This month' },
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

const selectedAppointment = computed(
  () => appointments.value.find((appointment) => appointment.id === selectedAppointmentId.value) || null,
)

onMounted(async () => {
  await Promise.all([loadServices(), loadEmployees(), loadAppointments()])
})

watch(
  () => ({ ...filters }),
  () => {
    loadAppointments()
  },
  { deep: true },
)

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

    appointments.value = response.data.data.map((appointment) => ({
      ...appointment,
      total_duration: appointment.services.reduce((sum, service) => sum + Number(service.duration || 0), 0),
    }))
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

function resetFilters() {
  filters.search = ''
  filters.serviceId = ''
  filters.employeeId = ''
  filters.status = ''
  filters.dateFrom = ''
  filters.dateTo = ''
  filters.quickDate = ''
  filters.orderBy = 'start_datetime'
  filters.orderDirection = 'desc'
  selectedAppointmentId.value = null
}

function formatDate(value) {
  if (!value) return ''
  return new Date(value).toLocaleDateString('en-CA')
}

function formatDateTime(value) {
  if (!value) return ''
  const date = new Date(value)
  return `${date.toLocaleDateString('en-CA')} ${date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}`
}

function formatTime(value) {
  if (!value) return ''
  return new Date(value).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}

function formatTimeRange(startValue, endValue) {
  const start = formatTime(startValue)
  const end = formatTime(endValue)
  if (!start && !end) return ''
  if (!end) return start
  return `${start}-${end}`
}

function formatPrice(value) {
  const amount = Number(value)
  return Number.isFinite(amount) ? amount.toFixed(2) : '0.00'
}

function sumDuration(items) {
  return items.reduce((sum, item) => sum + Number(item.duration || 0), 0)
}

function serviceSummary(services) {
  if (!services?.length) return 'Service'
  if (services.length === 1) return services[0]?.name || 'Service'

  const firstService = services[0]?.name || 'Service'
  const moreCount = services.length - 1
  return `${firstService} + ${moreCount} more`
}

function prettyStatus(status) {
  return String(status || '').replace('_', ' ')
}

function statusClass(status) {
  if (status === 'completed') return 'bg-emerald-100 text-emerald-900'
  if (status === 'pending') return 'bg-amber-100 text-amber-900'
  if (status === 'confirmed') return 'bg-blue-100 text-blue-900'
  if (status === 'cancelled') return 'bg-rose-100 text-rose-900'
  if (status === 'no_show') return 'bg-slate-200 text-slate-700'
  return 'bg-slate-100 text-slate-700'
}

function setQuickDate(value) {
  filters.quickDate = value

  const today = new Date()
  today.setHours(0, 0, 0, 0)

  if (value === 'today') {
    const iso = today.toISOString().slice(0, 10)
    filters.dateFrom = iso
    filters.dateTo = iso
    return
  }

  if (value === 'this_week') {
    const day = today.getDay()
    const diff = (day + 6) % 7
    const start = new Date(today)
    start.setDate(today.getDate() - diff)
    const end = new Date(start)
    end.setDate(start.getDate() + 6)
    filters.dateFrom = start.toISOString().slice(0, 10)
    filters.dateTo = end.toISOString().slice(0, 10)
    return
  }

  if (value === 'this_month') {
    const start = new Date(today.getFullYear(), today.getMonth(), 1)
    const end = new Date(today.getFullYear(), today.getMonth() + 1, 0)
    filters.dateFrom = start.toISOString().slice(0, 10)
    filters.dateTo = end.toISOString().slice(0, 10)
  }
}

function openAppointmentModal(appointment) {
  selectedAppointmentId.value = appointment.id
}

function closeAppointmentModal() {
  selectedAppointmentId.value = null
}

function canCancel(appointment) {
  return ['pending', 'confirmed'].includes(appointment?.status)
}

function canComplete(appointment) {
  return appointment?.status === 'confirmed'
}

function canMarkNoShow(appointment) {
  return appointment?.status === 'confirmed'
}

async function runAction(action) {
  if (!selectedAppointment.value) return

  const appointmentId = selectedAppointment.value.id

  if (action === 'cancel') {
    await cancelAdminAppointment(appointmentId, { cancellation_reason: 'Cancelled from admin dashboard' })
  }

  if (action === 'complete') {
    await completeAdminAppointment(appointmentId)
  }

  if (action === 'no-show') {
    await noShowAdminAppointment(appointmentId)
  }

  selectedAppointmentId.value = null
  await loadAppointments()
}
</script>
