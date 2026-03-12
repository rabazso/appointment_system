<script setup>
import { computed, ref } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Calendar } from '@/components/ui/calendar'
import { Button } from '@/components/ui/button'
import { Check, X } from 'lucide-vue-next'

const props = defineProps({
  appointments: { type: Array, required: true },
  selectedAppointmentIds: { type: Array, default: () => [] }
})
const emit = defineEmits([
  'cancel-appointment',
  'complete-appointment',
  'toggle-appointment-selection',
  'clear-appointment-selection',
  'cancel-selected-appointments'
])

const activeTab = ref('appointments')
const selectedDate = ref(new Date())
const STATUS_ORDER = ['pending', 'confirmed', 'completed', 'cancelled']
const selectedAppointmentIdSet = computed(() => new Set(props.selectedAppointmentIds))

const getStatusColor = (status) => {
  switch (status) {
    case 'confirmed': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    case 'completed': return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status) => {
    const labels = {
        confirmed: 'Confirmed',
        pending: 'Waiting for confirmation',
        cancelled: 'Canceled',
        completed: 'Done'
    }
    return labels[status] || status
}

const isAppointmentCancellable = (status) => ['pending', 'confirmed'].includes(status)

const groupedAppointments = computed(() => {
  const grouped = new Map(STATUS_ORDER.map((status) => [status, []]))

  for (const appointment of props.appointments) {
    if (!grouped.has(appointment.status)) {
      grouped.set(appointment.status, [])
    }
    grouped.get(appointment.status).push(appointment)
  }

  const normalizeDate = (value) => {
    if (!value) return Number.MAX_SAFE_INTEGER
    const timestamp = new Date(value).getTime()
    return Number.isFinite(timestamp) ? timestamp : Number.MAX_SAFE_INTEGER
  }

  for (const appointments of grouped.values()) {
    appointments.sort((a, b) => normalizeDate(a.start_datetime) - normalizeDate(b.start_datetime))
  }

  const orderedStatuses = [
    ...STATUS_ORDER,
    ...Array.from(grouped.keys()).filter((status) => !STATUS_ORDER.includes(status))
  ]

  return orderedStatuses
    .map((status) => ({
      status,
      label: getStatusText(status),
      appointments: grouped.get(status) ?? []
    }))
    .filter((group) => group.appointments.length > 0)
})
</script>

<template>
  <Card class="border-none shadow-none md:border md:shadow-sm">
    <div class="p-4 border-b">
      <div class="flex space-x-4">
        <button 
          @click="activeTab = 'appointments'" 
          :class="['px-4 py-2 text-sm font-medium rounded-md transition-colors', activeTab === 'appointments' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']">
          Dates
        </button>
      </div>
    </div>

    <CardContent class="p-6">
      <div v-if="activeTab === 'appointments'" class="space-y-4">
        <div
          v-if="selectedAppointmentIds.length > 0"
          class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-red-200 bg-red-50 p-3"
        >
          <p class="text-sm font-medium text-red-800">
            {{ selectedAppointmentIds.length }} appointment{{ selectedAppointmentIds.length === 1 ? '' : 's' }} selected
          </p>
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-800 transition hover:bg-red-100"
              @click="emit('clear-appointment-selection')"
            >
              Clear
            </button>
            <button
              type="button"
              class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700"
              @click="emit('cancel-selected-appointments')"
            >
              Cancel Selected
            </button>
          </div>
        </div>

        <div v-if="appointments.length === 0" class="text-center py-10 text-muted-foreground">
          No appointments yet.
        </div>

        <div v-else class="space-y-6">
          <section v-for="group in groupedAppointments" :key="group.status" class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-semibold tracking-wide text-foreground uppercase">{{ group.label }}</h3>
              <span class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                {{ group.appointments.length }}
              </span>
            </div>

            <div
              v-for="apt in group.appointments"
              :key="apt.id"
              class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <div class="flex items-center gap-4">
                <label v-if="isAppointmentCancellable(apt.status)" class="inline-flex items-center">
                  <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                    :checked="selectedAppointmentIdSet.has(apt.id)"
                    @change="emit('toggle-appointment-selection', apt.id)"
                  >
                </label>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">
                  {{ apt.client.charAt(0) }}
                </div>
                <div>
                  <h4 class="font-semibold">{{ apt.client }}</h4>
                  <p class="text-sm text-muted-foreground">{{ apt.service }} • {{ apt.time }}</p>
                </div>
              </div>

              <div class="flex items-center gap-4">
                <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(apt.status)]">
                  {{ getStatusText(apt.status) }}
                </span>
                <Button
                  v-if="apt.status === 'confirmed'"
                  size="icon"
                  variant="ghost"
                  class="h-8 w-8 text-green-600 hover:bg-green-50 hover:text-green-800"
                  title="Foglalás teljesítve"
                  @click="emit('complete-appointment', apt.id)"
                >
                  <Check class="h-4 w-4" />
                </Button>
                <Button
                  v-if="isAppointmentCancellable(apt.status)"
                  size="icon"
                  variant="ghost"
                  class="h-8 w-8 text-red-500 hover:bg-red-50 hover:text-red-700"
                  title="Foglalás lemondása"
                  @click="emit('cancel-appointment', apt.id)"
                >
                  <X class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </section>
        </div>
      </div>

      <div v-else-if="activeTab === 'calendar'" class="flex justify-center">
        <Calendar v-model="selectedDate" class="rounded-md border shadow" />
      </div>
    </CardContent>
  </Card>
</template>
