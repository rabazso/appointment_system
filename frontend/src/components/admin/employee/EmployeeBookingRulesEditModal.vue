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
      <div class="rounded-lg border border-black/10 bg-white px-3 py-2 transition focus-within:border-black">
        <div class="flex items-center gap-2">
          <input
            v-model.number="form.slot_interval_minutes"
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
      <div class="rounded-lg border border-black/10 bg-white px-3 py-2 transition focus-within:border-black">
        <div class="flex items-center gap-2">
          <input
            v-model.number="form.max_advance_days"
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
      @cancel="$emit('cancel')"
      @save="$emit('save', toPayload())"
    />
  </template>
  </ModalShell>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import EditModalFooter from '@/components/admin/EditModalFooter.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'

defineEmits(['back', 'cancel', 'close', 'save'])

const props = defineProps({
  bookingRules: {
    type: Object,
    default: null,
  },
})

const form = ref(props.bookingRules ? { ...props.bookingRules } : getDefaultBookingRules())
const title = computed(() => (props.bookingRules ? 'Edit booking rule change' : 'Booking rule change'))

watch(
  () => props.bookingRules,
  (bookingRules) => {
    form.value = bookingRules ? { ...bookingRules } : getDefaultBookingRules()
  },
)

function getDefaultBookingRules() {
  return {
    valid_from: new Date().toISOString().slice(0, 10),
    valid_to: null,
    max_advance_days: 14,
    slot_interval_minutes: 30,
  }
}

function toPayload() {
  return {
    valid_from: form.value.valid_from,
    valid_to: form.value.valid_to || null,
    slot_interval_minutes: form.value.slot_interval_minutes,
    max_advance_days: form.value.max_advance_days,
  }
}
</script>
