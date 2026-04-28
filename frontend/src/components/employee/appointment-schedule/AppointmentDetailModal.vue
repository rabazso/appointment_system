<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Check, UserX, X } from 'lucide-vue-next'
import Modal from '@/components/employee/Modal.vue'
import { Button } from '@/components/ui/button'

const props = defineProps({
  appointment: { type: Object, default: null },
  formatAppointmentDate: { type: Function, required: true },
  formatAppointmentTime: { type: Function, required: true },
  getStatusLabel: { type: Function, required: true },
  isAppointmentCancellable: { type: Function, required: true }
})

const emit = defineEmits(['close', 'complete', 'cancel', 'mark-no-show'])

const currentTime = ref(Date.now())
let currentTimeInterval = null

const hasAppointmentEnded = computed(() => {
  if (props.appointment?.status !== 'confirmed') {
    return false
  }

  const end = props.appointment.end.getTime()

  return Number.isFinite(end) && currentTime.value >= end
})

const canCompleteAppointment = computed(() => hasAppointmentEnded.value)
const canMarkNoShow = computed(() => hasAppointmentEnded.value)
const canCancelAppointment = computed(() => {
  if (!props.isAppointmentCancellable(props.appointment?.status)) {
    return false
  }

  const start = props.appointment.start.getTime()

  return Number.isFinite(start) && currentTime.value < start
})

const serviceSummary = computed(() => props.appointment.service)

onMounted(() => {
  currentTimeInterval = window.setInterval(() => {
    currentTime.value = Date.now()
  }, 15000)
})

onBeforeUnmount(() => {
  if (currentTimeInterval) {
    window.clearInterval(currentTimeInterval)
  }
})
</script>

<template>
  <Modal v-if="props.appointment" :title="props.appointment.client" :description="props.appointment.service"
    content-class="max-w-3xl rounded-[1.75rem] border border-white/70 shadow-[0_30px_80px_rgba(15,23,42,0.18)]"
    :close-on-backdrop="false"
    @close="emit('close')"
  >
    <div class="grid gap-4 md:grid-cols-2">
      <div class="md:col-span-2">
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Booked services</p>
        <div class="rounded-2xl border border-slate-200 px-4 py-3">
          <p class="text-lg font-medium leading-snug text-slate-900">{{ serviceSummary }}</p>
        </div>
      </div>

      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Total duration</p>
        <div class="rounded-2xl border border-slate-200 px-4 py-3">
          <p class="text-lg font-medium leading-snug text-slate-900">{{ props.appointment.total_duration }} mins</p>
        </div>
      </div>

      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Price</p>
        <div class="rounded-2xl border border-slate-200 px-4 py-3">
          <p class="text-lg font-medium leading-snug text-slate-900">$ {{ props.appointment.total_price }}</p>
        </div>
      </div>

      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Date</p>
        <div class="rounded-2xl border border-slate-200 px-4 py-3">
          <p class="text-lg font-medium text-slate-900">{{ props.formatAppointmentDate(props.appointment.start) }}</p>
        </div>
      </div>

      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Time</p>
        <div class="rounded-2xl border border-slate-200 px-4 py-3">
          <p class="text-lg font-mediumd text-slate-900">
            {{ props.formatAppointmentTime(props.appointment.start) }} - {{ props.formatAppointmentTime(props.appointment.end) }}
          </p>
        </div>
      </div>

      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Status</p>
        <div class="rounded-2xl border border-slate-200 px-4 py-3">
          <p class="text-lg font-medium text-slate-900">{{ props.getStatusLabel(props.appointment.status) }}</p>
        </div>
      </div>

      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Contact</p>
        <div class="rounded-2xl border border-slate-200 px-4 py-3">
          <p class="text-lg font-medium text-slate-900">{{ props.appointment.email }}</p>
        </div>
      </div>
    </div>

    <div v-if="props.appointment.customer_note" class="mt-4 w-full">
      <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">Customer note</p>
      <div class="rounded-2xl border border-slate-200 px-4 py-3">
        <p class="text-lg font-medium leading-snug text-slate-900">{{ props.appointment.customer_note }}</p>
      </div>
    </div>

    <template #footer>
      <div class="mt-3 flex flex-wrap justify-end gap-3 bg-white md:mt-6">
        <Button v-if="props.appointment.status === 'confirmed'" type="button" variant="ghost"
          class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-base font-semibold text-white shadow-none transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-200 disabled:text-emerald-700"
          :disabled="!canCompleteAppointment"
          :title="canCompleteAppointment ? 'Complete appointment' : 'Appointment can only be completed after it has ended'"
          @click="emit('complete', props.appointment.id)"
        >
          <span class="flex w-full items-center justify-center gap-2">
            <Check class="h-4 w-4" />
            Complete appointment
          </span>
        </Button>
        <Button v-if="props.appointment.status === 'confirmed'" type="button" variant="ghost"
          class="inline-flex items-center gap-2 rounded-2xl bg-slate-800 px-5 py-3 text-base font-semibold text-white shadow-none transition hover:bg-slate-900 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500"
          :disabled="!canMarkNoShow"
          :title="canMarkNoShow ? 'Mark appointment as no-show' : 'No-show can only be set after the appointment has ended'"
          @click="emit('mark-no-show', props.appointment.id)"
        >
          <span class="flex w-full items-center justify-center gap-2">
            <UserX class="h-4 w-4" />
            Mark no-show
          </span>
        </Button>
        <Button v-if="props.isAppointmentCancellable(props.appointment.status)" type="button" variant="ghost"
          class="inline-flex items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-base font-semibold text-white shadow-none transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:bg-rose-200 disabled:text-rose-700"
          :disabled="!canCancelAppointment"
          :title="canCancelAppointment ? 'Cancel appointment' : 'Appointment can only be cancelled before it starts'"
          @click="emit('cancel', props.appointment.id)"
        >
          <span class="flex w-full items-center justify-center gap-2">
            <X class="h-4 w-4" />
            Cancel appointment
          </span>
        </Button>
      </div>
    </template>
  </Modal>
</template>
