<script setup>
import AppointmentMonthlyCalendar from '@/components/employee/appointment-schedule/AppointmentMonthlyCalendar.vue'

const props = defineProps({
  monthlySummary: { type: Object, required: true },
  monthlyCalendar: { type: Array, required: true },
  getMonthlyFillStyle: { type: Function, required: true }
})

const emit = defineEmits(['open-day'])
</script>

<template>
  <div class="flex min-h-0 flex-1 flex-col gap-4">
    <div class="flex items-end justify-between">
      <div>
        <h3 class="text-lg font-semibold text-slate-900">Monthly View</h3>
      </div>
    </div>

    <AppointmentMonthlyCalendar
      :monthly-calendar="props.monthlyCalendar"
      :get-monthly-fill-style="props.getMonthlyFillStyle"
      @open-day="emit('open-day', $event)"
    />
  </div>
</template>
