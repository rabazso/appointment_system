<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Star, X } from 'lucide-vue-next'
import { cancelUserAppointment, getUserAppointments, postAppointmentReview } from '@/api'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import LeaveReviewModal from '@/components/modals/LeaveReviewModal.vue'
import { useToastStore } from '@/stores/ToastStore.js'

const router = useRouter()
const toast = useToastStore()
const appointments = ref([])
const loading = ref(true)
const error = ref(null)
const cancelModalOpen = ref(false)
const cancelling = ref(false)
const pendingCancelId = ref(null)
const reviewModalOpen = ref(false)
const reviewSubmitting = ref(false)
const reviewError = ref('')
const pendingReviewAppointmentId = ref(null)
const viewReviewAppointmentId = ref(null)

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

const formatDuration = (apt) => {
  const totalDuration = Number(apt?.total_duration)
  if (Number.isFinite(totalDuration) && totalDuration > 0) {
    return `${Math.round(totalDuration)} min`
  }

  if (apt?.start_datetime && apt?.end_datetime) {
    const diffMs = new Date(apt.end_datetime).getTime() - new Date(apt.start_datetime).getTime()
    const diffMinutes = Math.round(diffMs / 60000)

    if (Number.isFinite(diffMinutes) && diffMinutes > 0) {
      return `${diffMinutes} min`
    }
  }

  return '-'
}

const getServiceNames = (apt) => {
  const services = Array.isArray(apt?.services) ? apt.services : []
  const names = services
    .map((service) => service?.name?.trim())
    .filter(Boolean)

  if (names.length > 0) {
    return names.join(', ')
  }

  return apt?.service?.name || 'Service'
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

const reviewableAppointments = computed(() =>
  appointments.value.filter((apt) => apt.status === 'completed' && !apt.review)
)

const appointmentToViewReview = computed(() =>
  appointments.value.find((apt) => apt.id === viewReviewAppointmentId.value) || null
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
    toast.showError('Failed to cancel appointment.')
  } finally {
    cancelling.value = false
  }
}

const openLeaveReviewModal = (id) => {
  pendingReviewAppointmentId.value = id
  reviewError.value = ''
  reviewModalOpen.value = true
}

const closeLeaveReviewModal = (force = false) => {
  if (reviewSubmitting.value && !force) {
    return
  }

  reviewModalOpen.value = false
  pendingReviewAppointmentId.value = null
  reviewError.value = ''
}

const openViewReviewModal = (id) => {
  viewReviewAppointmentId.value = id
}

const closeViewReviewModal = () => {
  viewReviewAppointmentId.value = null
}

const submitReview = async (payload) => {
  const appointmentId = payload?.appointment_id
  if (!appointmentId || reviewSubmitting.value) {
    return
  }

  reviewSubmitting.value = true
  reviewError.value = ''

  try {
    const response = await postAppointmentReview(appointmentId, {
      rating: payload.rating,
      comment: payload.comment,
    })
    const review = response.data?.data ?? response.data
    const appointment = appointments.value.find((apt) => apt.id === appointmentId)

    if (appointment) {
      appointment.review = review
    }

    toast.show('Review submitted.')
    closeLeaveReviewModal(true)
  } catch (err) {
    console.error('Failed to submit review:', err)
    reviewError.value = err.response?.data?.message
      || err.response?.data?.errors?.rating?.[0]
      || err.response?.data?.errors?.comment?.[0]
      || 'Failed to submit review.'
    toast.showError('Failed to submit review.')
  } finally {
    reviewSubmitting.value = false
  }
}

const formatReviewDate = (value) => {
  if (!value) {
    return ''
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return ''
  }

  return date.toLocaleString([], {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(async () => {
  try {
    loading.value = true
    const response = await getUserAppointments()
    appointments.value = response.data
  } catch (err) {
    console.error("Error fetching appointments:", err)
    error.value = "Failed to load appointments. Please try again later."
    toast.showError('Failed to load appointments.')
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
              <CardTitle class="text-xl font-bold leading-tight">{{ getServiceNames(apt) }}</CardTitle>
              <span :class="`px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border ${getStatusColor(apt.status)}`">
                {{ getStatusText(apt.status) }}
              </span>
            </div>
          </CardHeader>

          <CardContent class="space-y-3 pb-6 flex-1 flex flex-col">
            <div class="flex min-h-11 justify-between items-center border-b border-accent/20 py-2">
              <p class="text-muted-foreground text-sm">Barber</p>
              <p class="font-semibold text-foreground text-sm">{{ apt.employee?.name || apt.barber?.name }}</p>
            </div>

            <div class="flex min-h-11 justify-between items-center border-b border-accent/20 py-2">
              <p class="text-muted-foreground text-sm">Date</p>
              <p class="font-semibold text-foreground text-sm">{{ formatDate(apt) }}</p>
            </div>

            <div class="flex min-h-11 justify-between items-center border-b border-accent/20 py-2">
              <p class="text-muted-foreground text-sm">Time</p>
              <p class="font-semibold text-foreground text-sm">{{ formatTime(apt) }}</p>
            </div>

            <div class="flex min-h-11 justify-between items-center border-b border-accent/20 py-2">
              <p class="text-muted-foreground text-sm">Duration</p>
              <p class="font-semibold text-foreground text-sm">{{ formatDuration(apt) }}</p>
            </div>

            <div class="flex min-h-11 justify-between items-center border-b border-accent/20 py-2">
              <p class="text-muted-foreground text-sm">Note</p>
              <p class="max-w-[65%] min-w-0 text-right font-semibold text-foreground text-sm whitespace-pre-wrap break-all">{{ apt.customer_note?.trim() || '-' }}</p>
            </div>

             <div class="flex min-h-11 justify-between items-center py-2">
              <p class="text-muted-foreground text-sm font-medium">Price</p>
              <p class="font-bold text-lg text-primary">${{ apt.price ?? apt.service?.price }}</p>
            </div>

            <div v-if="apt.status === 'completed'" class="pt-4 mt-auto">
              <Button
                v-if="apt.review"
                variant="outline"
                class="w-full"
                size="sm"
                @click="openViewReviewModal(apt.id)"
              >
                View Review
              </Button>
              <Button
                v-else
                class="w-full"
                size="sm"
                @click="openLeaveReviewModal(apt.id)"
              >
                Leave Review
              </Button>
            </div>

            <div v-else-if="apt.status === 'pending' || apt.status === 'confirmed'" class="pt-4 mt-auto">
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
          <p class="font-medium">{{ getServiceNames(appointmentToCancel || {}) }}</p>
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

    <LeaveReviewModal
      v-if="reviewModalOpen"
      :appointments="reviewableAppointments"
      :initial-appointment-id="pendingReviewAppointmentId"
      :loading="reviewSubmitting"
      :error-message="reviewError"
      @close="closeLeaveReviewModal"
      @submit="submitReview"
    />

    <div
      v-if="appointmentToViewReview && appointmentToViewReview.review"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
      @click.self="closeViewReviewModal"
    >
      <div class="w-full max-w-md rounded-xl border bg-background shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b px-6 py-4">
          <div>
            <h2 class="text-lg font-semibold">Your review</h2>
            <p class="mt-1 text-sm text-muted-foreground">
              {{ getServiceNames(appointmentToViewReview) }}
            </p>
          </div>
          <button
            type="button"
            class="text-muted-foreground transition-colors hover:text-primary"
            aria-label="Close review"
            @click="closeViewReviewModal"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <div class="space-y-5 px-6 py-5">
          <div class="rounded-lg bg-accent/10 px-4 py-3 text-sm">
            <p class="font-medium">
              {{ formatDate(appointmentToViewReview) }} at {{ formatTime(appointmentToViewReview) }}
            </p>
            <p v-if="formatReviewDate(appointmentToViewReview.review.created_at)" class="mt-1 text-muted-foreground">
              Reviewed {{ formatReviewDate(appointmentToViewReview.review.created_at) }}
            </p>
          </div>

          <div>
            <p class="mb-2 text-sm font-semibold text-primary">Rating</p>
            <div class="flex items-center gap-2">
              <Star
                v-for="star in 5"
                :key="star"
                class="h-6 w-6"
                :class="star <= Number(appointmentToViewReview.review.rating) ? 'fill-accent text-accent' : 'text-muted-foreground/40'"
              />
            </div>
          </div>

          <div>
            <p class="mb-2 text-sm font-semibold text-primary">Comment</p>
            <p class="whitespace-pre-wrap rounded-lg border bg-background px-3 py-3 text-sm text-foreground">
              {{ appointmentToViewReview.review.comment || 'No comment.' }}
            </p>
          </div>
        </div>

        <div class="flex justify-end border-t px-6 py-4">
          <Button @click="closeViewReviewModal">Close</Button>
        </div>
      </div>
    </div>
    </div>
</template>
