<template>
  <article
    class="group cursor-pointer rounded-2xl border border-black/10 bg-white p-4 transition hover:border-black/20 hover:bg-slate-50/70"
    @click="$emit('open')"
  >
    <div class="flex flex-wrap items-center gap-3">
      <div class="mx-auto">
        <p class="text-[11px] font-semibold uppercase tracking-wide leading-none text-slate-400">Customer</p>
        <div class="mt-1 flex items-start gap-3">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
              <h3 class="truncate text-lg font-semibold text-slate-950">
                {{ appointment.customer?.name || 'Guest' }}
              </h3>
            </div>
            <p class="truncate text-sm text-slate-500">
              {{ appointment.customer?.email || 'No email' }}
            </p>
          </div>
        </div>
      </div>

      <div class="mx-auto">
        <p class="text-[11px] font-semibold uppercase tracking-wide leading-none text-slate-400">Date & time</p>
        <div class="mt-1 flex flex-col gap-1 text-sm">
          <div class="inline-flex items-center justify-center gap-2 rounded-xl border border-black/10 bg-white px-3 py-2">
            <span class="text-slate-950">{{ dateText }}</span>
          </div>
          <div class="inline-flex items-center justify-center gap-2 rounded-xl border border-black/10 bg-white px-3 py-2">
            <span class="text-slate-950">{{ timeText }}</span>
          </div>
        </div>
      </div>

      <div class="ml-auto flex flex-row gap-4">
        <div class="mx-auto">
        <p class="text-[11px] font-semibold uppercase tracking-wide leading-none text-slate-400">Staff</p>
          <div class="mt-1 rounded-xl border border-black/10 bg-white px-3 py-2 text-slate-950">
            {{ appointment.employee?.name || 'Unknown' }}
          </div>
        </div>
      <div class="mx-auto">
        <p class="text-[11px] font-semibold uppercase tracking-wide leading-none text-slate-400">Service</p>
          <div class="mt-1 rounded-xl border border-black/10 bg-white px-3 py-2 flex flex-col gap-1">
            <p
              v-for="service in servicesName"
              :key="service.id || service.name"
              class="text-slate-950"
            >
              {{ service.name }}
            </p>
          </div>
        </div>
      </div>

        <div class="ml-auto flex flex-row gap-4 flex-wrap">
          <div class="mx-auto">
          <p class="text-[11px] font-semibold uppercase tracking-wide leading-none text-slate-400">Duration</p>
          <div class="mt-1 rounded-xl border border-black/10 bg-white px-3 py-2 text-sm text-slate-950">
            {{ durationText }}
          </div>
                </div>
          <div class="mx-auto">
          <p class="text-[11px] font-semibold uppercase tracking-wide leading-none text-slate-400">Price</p>
            <div class="mt-1 rounded-xl border border-black/10 bg-white px-3 py-2 text-sm text-slate-950">
              {{ priceText }}
            </div>
          </div>
          <div class="ml-auto">
        <p class="text-[11px] font-semibold uppercase tracking-wide leading-none text-slate-400">Status</p>
        <div class="mt-1 inline-flex rounded-full px-3 py-2 text-xs font-semibold" :class="statusClassValue">
          {{ statusLabel }}
        </div>
      </div>
        </div>

      
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'

defineEmits(['open'])

const props = defineProps({
  appointment: {
    type: Object,
    required: true,
  },
})

function statusClass(status) {
  if (status === 'completed') return 'bg-emerald-100 text-emerald-900'
  if (status === 'pending') return 'bg-gray-200 text-gray-800'
  if (status === 'confirmed') return 'border-black/10 border'
  if (status === 'cancelled') return 'border border-black/10 bg-white text-black line-through'
  if (status === 'no_show') return 'bg-rose-100 text-rose-900'
  return 'bg-slate-100 text-slate-700'
}

function prettyStatus(status) {
  return String(status || '').replace('_', ' ')
}

function formatDate(value) {
  if (!value) return ''
  return new Date(value).toLocaleDateString('en-CA')
}

function formatTime(value) {
  if (!value) return ''
  return new Date(value).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}

function formatTimeRange(startValue, endValue) {
  const start = formatTime(startValue)
  const end = formatTime(endValue)
  if (!start && !end) return ''
  if (!end) return start
  return `${start}-${end}`
}

function formatPrice(value) {
  const amount = Number(value)
  return Number.isFinite(amount) ? amount.toFixed(2) : '0.00'
}

function sumDuration(items) {
  return items.reduce((sum, item) => sum + Number(item.duration || 0), 0)
}

function serviceSummary(servicesList) {
  if (!servicesList?.length) return 'Service'
  if (servicesList.length === 1) return servicesList[0]?.name || 'Service'
  const firstService = servicesList[0]?.name || 'Service'
  const moreCount = servicesList.length - 1
  return `${firstService} + ${moreCount} more`
}

const statusLabel = computed(() => prettyStatus(props.appointment.status))
const statusClassValue = computed(() => statusClass(props.appointment.status))
const servicesName = props.appointment.services;
const durationText = computed(() => `${props.appointment.total_duration ?? sumDuration(props.appointment.services)} min`)
const priceText = computed(() => `$${formatPrice(props.appointment.price)}`)
const dateText = computed(() => formatDate(props.appointment.start_datetime))
const timeText = computed(() => formatTimeRange(props.appointment.start_datetime, props.appointment.end_datetime))
</script>
