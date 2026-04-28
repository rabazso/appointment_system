<script setup>
import Modal from '@/components/employee/Modal.vue'
import { Button } from '@/components/ui/button'

const props = defineProps({
  open: { type: Boolean, default: false },
  appointment: { type: Object, default: null },
  reason: { type: String, default: '' },
  reasonLength: { type: Number, default: 0 },
  maxReasonLength: { type: Number, default: 250 },
  errorMessage: { type: String, default: '' },
  closeDisabled: { type: Boolean, default: false },
  formatAppointmentTime: { type: Function, required: true },
})

const emit = defineEmits(['close', 'submit', 'update:reason'])

function onReasonInput(event) {
  emit('update:reason', event.target.value)
}
</script>

<template>
  <Modal v-if="props.open" title="Cancel appointment?" description="Add a clear reason for the client before canceling."
    content-class="max-w-lg rounded-xl border shadow-2xl" :close-disabled="props.closeDisabled" @close="emit('close')">
    <div class="space-y-4">
      <div class="rounded-lg border bg-muted/20 p-3 text-sm">
        <p class="font-medium text-foreground">{{ props.appointment.service }}</p>
        <p class="mt-1 text-muted-foreground">
          Client: {{ props.appointment.client }} | Time: {{ props.formatAppointmentTime(props.appointment) }}
        </p>
      </div>

      <div class="space-y-2">
        <label for="employee-cancellation-reason" class="text-sm font-medium text-foreground">
          Cancellation reason
        </label>
        <textarea id="employee-cancellation-reason" :value="props.reason" rows="4" :maxlength="props.maxReasonLength"
          :disabled="props.closeDisabled" placeholder="Please explain why this appointment is being cancelled."
          class="resize-none w-full rounded-lg border border-border bg-transparent px-3 py-2 text-foreground outline-none transition hover:border-black"
          @input="onReasonInput"
        ></textarea>
        <p class="text-xs text-muted-foreground">
          {{ props.reasonLength }} / {{ props.maxReasonLength }} characters
        </p>
      </div>

      <p v-if="props.errorMessage" class="rounded-md bg-red-100 px-3 py-2 text-sm text-red-700">
        {{ props.errorMessage }}
      </p>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <Button
          type="button"
          variant="ghost"
          class="inline-flex items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-base font-semibold text-white shadow-none transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:bg-rose-200 disabled:text-rose-700"
          :disabled="props.closeDisabled || !props.appointment"
          @click="emit('submit')"
        >
          Cancel Appointment
        </Button>
      </div>
    </template>
  </Modal>
</template>
