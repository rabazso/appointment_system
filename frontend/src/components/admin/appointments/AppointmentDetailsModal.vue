<template>
  <ModalShell v-if="appointment" content-class="max-w-2xl" @close="$emit('close')">
    <div class="overflow-y-auto p-4 pb-3">
      <div class="flex flex-col gap-4 border-b border-black/10 pb-5">
        <div class="flex items-center justify-between gap-3">
          <p class="text-2xl font-semibold text-slate-950">Appointment details</p>
          <span class="ml-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass">
            {{ prettyStatus }}
          </span>
        </div>
      </div>

      <div class="mt-4 grid gap-4 md:grid-cols-2">
        <section class="rounded-2xl border border-black/10 bg-white p-4 md:p-5">
          <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Appointment</h4>
          <div class="mt-4 grid gap-2 text-sm text-slate-700">
            <p><span class="font-semibold text-slate-950">Date:</span> {{ formatDate(appointment.start_datetime) }}</p>
            <p>
              <span class="font-semibold text-slate-950">Start:</span> {{ formatTime(appointment.start_datetime) }}
              <span class="font-semibold text-slate-950">End:</span> {{ formatTime(appointment.end_datetime) }}
            </p>
            <p><span class="font-semibold text-slate-950">Total Duration:</span> {{ appointment.total_duration ?? sumDuration(appointment.services) }} min</p>
            <p><span class="font-semibold text-slate-950">Total Price:</span> ${{ formatPrice(appointment.price) }}</p>
            <p><span class="font-semibold text-slate-950">Employee:</span> {{ appointment.employee?.name || 'Unknown' }}</p>
          </div>
        </section>

        <section class="rounded-2xl border border-black/10 bg-white p-4 md:p-5">
          <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</h4>
          <div class="mt-4 grid gap-2 text-sm text-slate-700">
            <p><span class="font-semibold text-slate-950">Name:</span> {{ appointment.customer?.name || 'Guest' }}</p>
            <p><span class="font-semibold text-slate-950">Email:</span> {{ appointment.customer?.email || 'No email' }}</p>
          </div>
        </section>
      </div>

      <section class="mt-4 rounded-2xl border border-black/10 bg-white p-4 md:p-5">
        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Services</h4>
        <div class="mt-4 overflow-hidden rounded-2xl">
          <table class="min-w-full divide-y divide-black/10 text-sm">
            <thead class="text-left text-sm">
              <tr>
                <th class="px-3 py-2 font-semibold"></th>
                <th class="px-3 py-2 font-semibold">Duration</th>
                <th class="px-3 py-2 font-semibold text-right">Price</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-black/10 bg-white">
              <tr v-for="service in appointment.services" :key="service.id || service.name">
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
            <p><span class="font-semibold text-slate-950">Cancelled by:</span> {{ appointment.cancelled_by || 'N/A' }}</p>
            <p><span class="font-semibold text-slate-950">Cancellation reason:</span> {{ appointment.cancellation_reason || 'N/A' }}</p>
            <p><span class="font-semibold text-slate-950">Created at:</span> {{ formatDateTime(appointment.created_at) || 'N/A' }}</p>
          </div>
        </section>

        <section class="rounded-2xl border border-black/10 bg-white p-4 md:p-5">
          <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</h4>
          <div class="mt-4 text-sm text-slate-500">
            <p>{{ appointment.notes || 'No notes added.' }}</p>
          </div>
        </section>
      </div>
    </div>

    <template #footer>
      <div v-if="canCancel" class="w-full rounded-2xl bg-white px-2 py-2">
        <p class="text-xs font-semibold uppercase tracking-wide leading-none text-slate-400">Reason</p>
        <textarea
          v-model="cancelReason"
          placeholder="Reason for cancellation"
          rows="3"
          class="mt-1 w-full resize-none rounded-lg border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
        />
      </div>

      <div
        v-if="canCancel || canComplete || canMarkNoShow"
        class="flex w-full flex-wrap items-center justify-end gap-3"
      >
        <div class="flex flex-wrap justify-end gap-2">
          <button
            v-if="canMarkNoShow"
            type="button"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-950"
            @click="$emit('no-show')"
          >
            No-show
          </button>
          <button
            v-if="canCancel"
            type="button"
            class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
            @click="$emit('cancel', cancelReason)"
          >
            Cancel
          </button>
          <button
            v-if="canComplete"
            type="button"
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
            @click="$emit('complete')"
          >
            Complete
          </button>
        </div>
      </div>
    </template>
  </ModalShell>
</template>

<script setup>
import { computed, ref } from 'vue'
import ModalShell from '@/components/admin/ModalShell.vue'

defineEmits(['close', 'cancel', 'complete', 'no-show'])

const props = defineProps({
  appointment: {
    type: Object,
    required: true,
  },
})

const cancelReason = ref('')

const prettyStatus = computed(() => String(props.appointment.status || '').replace('_', ' '))
const statusClass = computed(() => {
  if (props.appointment.status === 'completed') return 'bg-emerald-100 text-emerald-900'
  if (props.appointment.status === 'pending') return 'bg-gray-200 text-gray-800'
  if (props.appointment.status === 'confirmed') return 'border-black/10 border'
  if (props.appointment.status === 'cancelled') return 'border border-black/10 bg-white text-black line-through'
  if (props.appointment.status === 'no_show') return 'bg-rose-100 text-rose-900'
  return 'bg-slate-100 text-slate-700'
})

const hasEnded = computed(() => {
  if (!props.appointment.end_datetime) return false
  return new Date(props.appointment.end_datetime).getTime() <= Date.now()
})

const canCancel = computed(() => !hasEnded.value && ['pending', 'confirmed'].includes(props.appointment.status))
const canComplete = computed(() => hasEnded.value && props.appointment.status === 'confirmed')
const canMarkNoShow = computed(() => hasEnded.value && props.appointment.status === 'confirmed')

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

function formatPrice(value) {
  const amount = Number(value)
  return Number.isFinite(amount) ? amount.toFixed(2) : '0.00'
}

function sumDuration(items) {
  return items.reduce((sum, item) => sum + Number(item.duration || 0), 0)
}
</script>
