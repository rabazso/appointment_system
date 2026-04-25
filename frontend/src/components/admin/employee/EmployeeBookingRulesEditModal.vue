<template>
  <ModalShell
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="title"
      description="Update booking limits and appointment rules."
    />

  <div class="space-y-3">
    <div class="grid gap-2 sm:grid-cols-[150px_minmax(0,1fr)] sm:items-center">
      <label class="text-sm font-medium text-slate-700">Booking slot interval</label>
      <div>
        <div
          class="rounded-lg border px-3 py-2 transition"
          :class="fieldError('booking_interval_minutes') ? 'border-red-500' : 'border-black/10 focus-within:border-black'"
        >
          <div class="flex items-center gap-2">
            <input
              v-model.number="form.booking_interval_minutes"
              type="number"
              min="1"
              class="w-full bg-transparent text-sm outline-none"
            />
            <span class="shrink-0 text-sm font-medium text-slate-400">min</span>
          </div>
        </div>
        <p v-if="fieldError('booking_interval_minutes')" class="mt-1 text-xs text-red-500">
          {{ fieldError('booking_interval_minutes') }}
        </p>
      </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-[150px_minmax(0,1fr)] sm:items-center">
      <label class="text-sm font-medium text-slate-700">Booking window days</label>
      <div>
        <div
          class="rounded-lg border px-3 py-2 transition"
          :class="fieldError('booking_window_days') ? 'border-red-500' : 'border-black/10 focus-within:border-black'"
        >
          <div class="flex items-center gap-2">
            <input
              v-model.number="form.booking_window_days"
              type="number"
              min="1"
              class="w-full bg-transparent text-sm outline-none"
            />
            <span class="shrink-0 text-sm font-medium text-slate-400">days</span>
          </div>
        </div>
        <p v-if="fieldError('booking_window_days')" class="mt-1 text-xs text-red-500">
          {{ fieldError('booking_window_days') }}
        </p>
      </div>
    </div>
  </div>
  <template #footer>
    <EditModalFooter
      v-model="form.valid_from"
      :date-disabled="dateDisabled"
      :min="validFromPolicy?.min"
      :max="validFromPolicy?.max"
      @cancel="$emit('cancel')"
      @save="handleSave"
    />
  </template>
  </ModalShell>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import EditModalFooter from '@/components/admin/EditModalFooter.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'

const emit = defineEmits(['back', 'cancel', 'close', 'save'])

const props = defineProps({
  bookingRules: {
    type: Object,
    default: null,
  },
  validFromPolicy: {
    type: Object,
    default: null,
  },
})

const submitted = ref(false)
const form = ref(props.bookingRules ? { ...props.bookingRules } : getDefaultBookingRules())
const title = computed(() => (props.bookingRules ? 'Edit booking rule change' : 'Booking rule change'))
const validFromPolicy = computed(() => props.bookingRules?.valid_from_policy ?? props.validFromPolicy)
const dateDisabled = computed(() => validFromPolicy.value?.editable === false)
const errors = computed(() => getErrors())

watch(
  () => props.bookingRules,
  (bookingRules) => {
    form.value = bookingRules ? { ...bookingRules } : getDefaultBookingRules()
    submitted.value = false
  },
)

function getDefaultBookingRules() {
  return {
    valid_from: props.validFromPolicy?.min ?? new Date().toISOString().slice(0, 10),
    booking_window_days: 14,
    booking_interval_minutes: 30,
  }
}

function toPayload() {
  return {
    ...(dateDisabled.value ? {} : { valid_from: form.value.valid_from }),
    booking_window_days: form.value.booking_window_days,
    booking_interval_minutes: form.value.booking_interval_minutes,
  }
}

function handleSave() {
  submitted.value = true
  if (Object.keys(errors.value).length) return
  emit('save', toPayload())
}

function fieldError(field) {
  return submitted.value ? errors.value[field] : null
}

function getErrors() {
  const nextErrors = {}

  validatePositiveNumber(nextErrors, 'booking_interval_minutes', form.value.booking_interval_minutes)
  validatePositiveNumber(nextErrors, 'booking_window_days', form.value.booking_window_days)

  return nextErrors
}

function validatePositiveNumber(nextErrors, key, value) {
  if (value === null || value === undefined || value === '') {
    nextErrors[key] = 'Required'
    return
  }

  if (Number(value) <= 0) {
    nextErrors[key] = 'Must be greater than 0'
  }
}
</script>
