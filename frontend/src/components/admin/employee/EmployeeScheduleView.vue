<template>
  <ModalShell
    :showBack="true"
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      title="Schedule details"
      description="Review the selected weekly schedule."
    />

    <VersionDetailActions
      :valid-from="schedule.valid_from"
      :valid-to="schedule.valid_to"
      @edit="$emit('edit')"
      @delete="$emit('delete')"
    />

    <div class="space-y-4">
      <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div
          v-for="(day, index) in weekDayOptions"
          :key="day.key"
          class="px-5 py-4"
          :class="index !== weekDayOptions.length - 1 ? 'border-b border-slate-100' : ''"
        >
          <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-4">
            <span class="text-sm font-medium text-slate-700">{{ day.fullLabel }}</span>
            <span
              class="text-sm font-semibold"
              :class="schedule?.weeklyHours?.[index]?.isOpen ? 'text-slate-900' : 'text-slate-400'"
            >
              {{
                schedule?.weeklyHours?.[index]?.isOpen
                  ? `${schedule.weeklyHours[index].start} - ${schedule.weeklyHours[index].end}`
                  : 'Off'
              }}
            </span>
          </div>

          <div
            v-if="schedule?.weeklyHours?.[index]?.isOpen && getBreaksForWeekday(index).length"
            class="mt-2 flex flex-wrap items-center gap-2 rounded-lg bg-slate-50 px-3 py-1.5"
          >
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Breaks</span>
            <div class="flex flex-wrap gap-1">
              <span
                v-for="(breakItem, breakIndex) in getBreaksForWeekday(index)"
                :key="`${breakItem.weekday}-${breakIndex}`"
                class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-0.5 text-xs font-medium text-slate-600"
              >
                {{ breakItem.start }} - {{ breakItem.end }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ModalShell>
</template>

<script setup>
import ModalShell from '@/components/admin/ModalShell.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import VersionDetailActions from './VersionDetailActions.vue'

defineEmits(['back', 'close', 'edit', 'delete'])

const props = defineProps({
  schedule: {
    type: Object,
    required: true,
  },
})

const weekDayOptions = [
  { key: 'mon', fullLabel: 'Monday' },
  { key: 'tue', fullLabel: 'Tuesday' },
  { key: 'wed', fullLabel: 'Wednesday' },
  { key: 'thu', fullLabel: 'Thursday' },
  { key: 'fri', fullLabel: 'Friday' },
  { key: 'sat', fullLabel: 'Saturday' },
  { key: 'sun', fullLabel: 'Sunday' },
]

function getBreaksForWeekday(weekday) {
  return props.schedule.breaks?.filter((breakEntry) => breakEntry.weekday === weekday) ?? []
}
</script>
