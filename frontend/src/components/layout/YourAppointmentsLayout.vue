<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { cancelUserAppointment, getUserAppointments } from '@/api'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const router = useRouter()
const appointments = ref([])
const loading = ref(true)
const error = ref(null)
const cancelModalOpen = ref(false)
const cancelling = ref(false)
const pendingCancelId = ref(null)

const getStatusColor = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-500/10 text-amber-600 border-amber-500/20'
    case 'confirmed': return 'bg-blue-500/10 text-blue-600 border-blue-500/20'
    case 'completed': return 'bg-green-500/10 text-green-600 border-green-500/20'
    case 'no_show': return 'bg-gray-500/10 text-gray-600 border-gray-500/20'
    case 'cancelled': return 'bg-red-500/10 text-red-600 border-red-500/20'
    default: return 'bg-gray-500/10 text-gray-600 border-gray-500/20'
  }
}

const getStatusText = (status) => {
  if (!status) return 'unknown'
  return status.replace('_', ' ')
}

const getStartDate = (apt) => apt.start_datetime || apt.appointment_start

const formatDate = (apt) => {
  const start = getStartDate(apt)
  return start ? new Date(start).toLocaleDateString() : apt.date
}

const formatTime = (apt) => {
  const start = getStartDate(apt)
  return start ? new Date(start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : apt.time
}

const sortedAppointments = computed(() => {
  return [...appointments.value].sort((a, b) => {
    const aTime = getStartDate(a) ? new Date(getStartDate(a)).getTime() : 0
    const bTime = getStartDate(b) ? new Date(getStartDate(b)).getTime() : 0
    return aTime - bTime
  })
})

const appointmentToCancel = computed(() =>
  appointments.value.find((apt) => apt.id === pendingCancelId.value) || null
)

const openCancelModal = (id) => {
  pendingCancelId.value = id
  cancelModalOpen.value = true
}

const closeCancelModal = () => {
  cancelModalOpen.value = false
  pendingCancelId.value = null
}

const confirmCancelAppointment = async () => {
  if (!pendingCancelId.value) return
  cancelling.value = true

  try {
    await cancelUserAppointment(pendingCancelId.value)
    const appointment = appointments.value.find(apt => apt.id === pendingCancelId.value)
    if (appointment) {
      appointment.status = 'cancelled'
    }
    closeCancelModal()
  } catch (err) {
    console.error("Failed to cancel appointment:", err)
    error.value = err.response?.data?.message || "Failed to cancel appointment. Please try again later."
  } finally {
    cancelling.value = false
  }
}

onMounted(async () => {
  try {
    loading.value = true
    const response = await getUserAppointments()
    appointments.value = response.data
  } catch (err) {
    console.error("Error fetching appointments:", err)
    error.value = "Failed to load appointments. Please try again later."
  } finally {
    loading.value = false
  }
})

const handleNewBooking = () => {
  router.push('/booking')
}
</script>

<template>
    <div class="min-h-100vh bg-background p-4 md:p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-6">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Your Appointments</h1>
        </div>
        <Button @click="handleNewBooking" class="gap-2 shadow-sm">
          Book New Appointment
        </Button>
      </div>

      <div v-if="error" class="p-4 bg-red-500/10 text-red-600 rounded-xl border border-red-500/20 text-center">
        {{ error }}
      </div>

      <div v-if="loading" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div class="h-64 rounded-xl bg-muted/50 animate-pulse border-2 border-dashed border-muted" v-for="i in 3" :key="i"></div>
      </div>

      <div v-else-if="appointments.length === 0" class="flex flex-col items-center justify-center py-16 border-2 border-dashed rounded-xl bg-accent/5">
        <h3 class="font-semibold text-xl mb-2">No appointments yet</h3>
        <Button @click="handleNewBooking">
          Book Now
        </Button>
      </div>

      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="apt in sortedAppointments" :key="apt.id" class="border-accent/30 bg-accent/10 shadow-sm transition-all hover:border-accent/50 flex flex-col">
          <CardHeader class="pb-3 pt-5">
            <div class="flex justify-between items-start gap-2">
              <CardTitle class="text-xl font-bold leading-tight">{{ apt.service?.name || 'Service' }}</CardTitle>
              <span :class="`px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border ${getStatusColor(apt.status)}`">
                {{ getStatusText(apt.status) }}
              </span>
            </div>
          </CardHeader>

          <CardContent class="space-y-3 pb-6 flex-1 flex flex-col">
            <div class="flex justify-between items-center border-b border-accent/20 pb-2">
              <p class="text-muted-foreground text-sm">Barber</p>
              <p class="font-semibold text-foreground text-sm">{{ apt.employee?.name || apt.barber?.name }}</p>
            </div>

            <div class="flex justify-between items-center border-b border-accent/20 pb-2">
              <p class="text-muted-foreground text-sm">Date</p>
              <p class="font-semibold text-foreground text-sm">{{ formatDate(apt) }}</p>
            </div>

            <div class="flex justify-between items-center border-b border-accent/20 pb-2">
              <p class="text-muted-foreground text-sm">Time</p>
              <p class="font-semibold text-foreground text-sm">{{ formatTime(apt) }}</p>
            </div>

             <div class="flex justify-between items-center pt-2">
              <p class="text-muted-foreground text-sm font-medium">Price</p>
              <p class="font-bold text-lg text-primary">${{ apt.service?.price || apt.price }}</p>
            </div>

            <div v-if="apt.status === 'pending' || apt.status === 'confirmed'" class="pt-4 mt-auto">
              <Button 
                variant="destructive" 
                class="w-full" 
                size="sm"
                @click="openCancelModal(apt.id)"
              >
                Cancel Appointment
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <div
      v-if="cancelModalOpen"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
      @click.self="closeCancelModal"
    >
      <div class="w-full max-w-md rounded-xl border bg-background shadow-2xl">
        <div class="border-b px-6 py-4">
          <h2 class="text-lg font-semibold">Cancel appointment?</h2>
          <p class="mt-1 text-sm text-muted-foreground">
            This action cannot be undone.
          </p>
        </div>

        <div class="px-6 py-4 text-sm">
          <p class="font-medium">{{ appointmentToCancel?.service?.name || 'Service' }}</p>
          <p class="mt-1 text-muted-foreground">
            {{ formatDate(appointmentToCancel || {}) }} at {{ formatTime(appointmentToCancel || {}) }}
          </p>
        </div>

        <div class="flex justify-end gap-2 border-t px-6 py-4">
          <Button
            variant="outline"
            class="bg-accent text-black hover:bg-accent/90 border-accent"
            :disabled="cancelling"
            @click="closeCancelModal"
          >
            Keep Appointment
          </Button>
          <Button variant="destructive" :disabled="cancelling" @click="confirmCancelAppointment">
            {{ cancelling ? 'Cancelling...' : 'Yes, Cancel' }}
          </Button>
        </div>
      </div>
    </div>
    </div>
</template>
