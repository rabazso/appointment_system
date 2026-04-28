<script setup>
const props = defineProps({
  bookedCount: { type: Number, required: true },
  segments: { type: Array, required: true },
  getStatusStyles: { type: Function, required: true },
  formatMinutes: { type: Function, required: true },
  formatAppointmentTime: { type: Function, required: true },
})

const emit = defineEmits(['open-details'])
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-end justify-between">
      <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
        {{ props.bookedCount }} booked
      </div>
    </div>

    <div class="p-0">
      <div class="relative min-h-[156px] overflow-hidden rounded-[1.25rem] p-3 sm:p-5">
        <div class="relative grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
          <div v-for="segment in props.segments" :key="segment.id" class="h-[132px] p-1.5 md:h-[144px]">
            <div
              v-if="segment.kind === 'empty'"
              class="flex h-full w-full flex-col items-center justify-center rounded-[1rem] border border-dashed border-slate-200 bg-slate-50/90 p-3 text-center text-slate-400"
            >
              <p class="mt-3 text-[11px] font-semibold uppercase">
                {{ props.formatMinutes(segment.start) }} - {{ props.formatMinutes(segment.end) }}
              </p>
              <p class="mt-1 text-sm font-medium">Empty</p>
            </div>

            <div
              v-else
              class="relative flex h-full w-full cursor-pointer flex-col rounded-[1rem] border px-3 py-3.5 transition hover:scale-[1.01]"
              :class="props.getStatusStyles(segment.appointment.status)"
              @click="emit('open-details', segment.appointment)"
            >
              <div class="min-w-0">
                <div class="text-base font-black leading-[1.05] tracking-[0.02em]">
                  <span class="block whitespace-nowrap">{{ props.formatAppointmentTime(segment.appointment.start) }}</span>
                  <span class="mt-1 block whitespace-nowrap opacity-80">{{ props.formatAppointmentTime(segment.appointment.end) }}</span>
                </div>
                <p class="mt-3 overflow-hidden text-lg font-semibold leading-[1.08] [display:-webkit-box] [-webkit-box-orient:vertical] [-webkit-line-clamp:2]">
                  {{ segment.appointment.client }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
