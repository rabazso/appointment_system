<script setup>
const props = defineProps({
  appointments: { type: Array, required: true },
  formatAppointmentDate: { type: Function, required: true },
})
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-end justify-between">
      <div class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">
        {{ props.appointments.length }} cancelled
      </div>
    </div>

    <div v-if="props.appointments.length" class="space-y-3">
      <article
        v-for="appointment in props.appointments"
        :key="appointment.id"
        class="rounded-[1.25rem] border border-slate-200 bg-white px-4 py-4"
      >
        <p class="text-sm font-semibold text-slate-500">
          {{ props.formatAppointmentDate(appointment.start) }}
        </p>
        <p class="mt-2 text-lg font-semibold text-slate-900">
          {{ appointment.client }}
        </p>
        <p class="mt-1 text-sm text-slate-700">
          {{ appointment.service }}
        </p>
        <div class="mt-3 rounded-2xl bg-rose-50 px-3 py-2">
          <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-600">Cancellation reason</p>
          <p class="mt-1 text-sm leading-snug text-slate-700">{{ appointment.cancellation_reason }}</p>
        </div>
      </article>
    </div>

    <div v-else class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center">
      <p class="text-base font-medium text-slate-700">No cancelled appointments yet.</p>
    </div>
  </div>
</template>
