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

  <div v-if="errors.booking_interval_minutes" class="text-xs text-red-500 mt-1">
  {{ errors.booking_interval_minutes }}
</div>

<div v-if="errors.booking_window_days" class="text-xs text-red-500 mt-1">
  {{ errors.booking_window_days }}
</div>

  <div class="space-y-3">
    <div class="grid gap-2 sm:grid-cols-[150px_minmax(0,1fr)] sm:items-center">
      <label class="text-sm font-medium text-slate-700">Booking slot interval</label>
      <div
        class="rounded-lg border px-3 py-2 transition"
        :class="errors.booking_interval_minutes ? 'border-red-500' : 'border-black/10 focus-within:border-black'"
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
    </div>

    <div class="grid gap-2 sm:grid-cols-[150px_minmax(0,1fr)] sm:items-center">
      <label class="text-sm font-medium text-slate-700">Booking window days</label>
      <div
        class="rounded-lg border px-3 py-2 transition"
        :class="errors.booking_window_days ? 'border-red-500' : 'border-black/10 focus-within:border-black'"
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

const errors = computed(() => {
  const e = {}

  if (!form.value.booking_interval_minutes) {
    e.booking_interval_minutes = 'Required'
  }

  if (!form.value.booking_window_days) {
    e.booking_window_days = 'Required'
  }

  if (!form.value.valid_from) {
    e.valid_from = 'Required'
  }

  return e
})

const isValid = computed(() => Object.keys(errors.value).length === 0)

const form = ref(props.bookingRules ? { ...props.bookingRules } : getDefaultBookingRules())
const title = computed(() => (props.bookingRules ? 'Edit booking rule change' : 'Booking rule change'))
const validFromPolicy = computed(() => props.bookingRules?.valid_from_policy ?? props.validFromPolicy)
const dateDisabled = computed(() => validFromPolicy.value?.editable === false)

watch(
  () => props.bookingRules,
  (bookingRules) => {
    form.value = bookingRules ? { ...bookingRules } : getDefaultBookingRules()
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
  if (!isValid.value) return
  emit('save', toPayload())
}
</script>
