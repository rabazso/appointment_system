<script setup>
import { ref, watch } from 'vue'
import { Button } from '@components/ui/button'
import { X, Star } from 'lucide-vue-next'

const props = defineProps({
  appointments: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'submit'])

const rating = ref(5)
const comment = ref('')
const appointmentId = ref('')

watch(
  () => props.appointments,
  (nextAppointments) => {
    if (!nextAppointments.length) {
      appointmentId.value = ''
      return
    }

    const hasCurrentSelection = nextAppointments.some(
      (appointment) => String(appointment.id) === String(appointmentId.value),
    )

    if (!hasCurrentSelection) {
      appointmentId.value = String(nextAppointments[0].id)
    }
  },
  { immediate: true },
)

function closeModal() {
  emit('close')
}

function selectRating(star) {
  rating.value = star
}

function handleSubmit() {
  if (!appointmentId.value || props.loading) {
    return
  }

  emit('submit', {
    appointment_id: Number(appointmentId.value),
    rating: rating.value,
    comment: comment.value.trim() || null,
  })
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" @click.self="closeModal">
    <div class="relative w-full max-w-lg rounded-xl bg-background p-6 shadow-2xl">
      <button
        type="button"
        class="absolute right-4 top-4 text-muted-foreground transition-colors hover:text-primary"
        @click="closeModal"
      >
        <X class="h-5 w-5" />
      </button>

      <h3 class="mb-2 text-2xl font-bold text-primary">Leave a review</h3>
      <p class="mb-6 text-sm text-muted-foreground">Tell us how your experience was.</p>

      <form class="space-y-5" @submit.prevent="handleSubmit">
        <div>
          <label for="review-appointment" class="mb-2 block text-sm font-semibold text-primary">Appointment</label>
          <select
            id="review-appointment"
            v-model="appointmentId"
            class="h-10 w-full rounded-lg border border-border bg-transparent px-3 text-primary outline-none transition focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/40"
            :disabled="loading || appointments.length === 0"
            required
          >
            <option value="" disabled>Select an appointment</option>
            <option
              v-for="appointment in appointments"
              :key="appointment.id"
              :value="String(appointment.id)"
            >
              #{{ appointment.id }} - {{ appointment.service?.name || 'Service' }} - {{ appointment.start_datetime?.slice(0, 16).replace('T', ' ') }}
            </option>
          </select>
          <p v-if="appointments.length === 0" class="mt-2 text-xs text-muted-foreground">
            No completed appointments found for review.
          </p>
        </div>

        <div>
          <p class="mb-2 text-sm font-semibold text-primary">Rating</p>
          <div class="flex items-center gap-2">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              class="cursor-pointer transition-transform hover:scale-110"
              :aria-label="`Rate ${star} star`"
              @click="selectRating(star)"
            >
              <Star
                class="h-6 w-6"
                :class="star <= rating ? 'fill-accent text-accent' : 'text-muted-foreground/50'"
              />
            </button>
          </div>
        </div>

        <div>
          <label for="review-comment" class="mb-2 block text-sm font-semibold text-primary">Comment</label>
          <textarea
            id="review-comment"
            v-model="comment"
            rows="4"
            maxlength="1000"
            placeholder="Write your feedback..."
            class="w-full rounded-lg border border-border bg-transparent px-3 py-2 text-primary outline-none transition focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/40"
          />
        </div>

        <p v-if="errorMessage" class="rounded-md bg-red-500 px-3 py-2 text-sm text-white">
          {{ errorMessage }}
        </p>

        <div class="flex justify-end gap-3">
          <Button type="button" variant="outlinebackground" :disabled="loading" @click="closeModal">Cancel</Button>
          <Button type="submit" :disabled="loading || appointments.length === 0">
            {{ loading ? 'Submitting...' : 'Submit review' }}
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>
