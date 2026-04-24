<template>
  <ModalShell
    :showBack="true"
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="title"
      description="Update working hours and breaks for this schedule version."
    />

  <div class="space-y-3">
    

    <div class="mx-auto flex w-fit gap-1 rounded-lg border border-black/10 p-1">
      <button
        type="button"
        class="rounded-md px-4 py-3 text-sm font-medium transition"
        :class="currentView === 'hours' ? 'bg-secondary text-black' : 'hover:bg-slate-50'"
        @click="currentView = 'hours'"
      >
        Working hours
      </button>
      <button
        type="button"
        class="rounded-md px-4 py-3 text-sm font-medium transition"
        :class="currentView === 'breaks' ? 'bg-secondary text-black' : 'hover:bg-slate-50'"
        @click="currentView = 'breaks'"
      >
        Breaks
      </button>
    </div>

    <div v-if="currentView === 'hours'" class="space-y-2">
      <div
        v-for="(_, index) in form.weeklyHours"
        :key="index"
        class="rounded-xl border border-slate-200 bg-white p-3"
      >
        <div class="flex flex-row gap-2 flex-wrap justify-between">
          <div class="inline-flex w-fit items-center gap-3">
            <ToggleButton v-model="form.weeklyHours[index].isOpen" />
            <span class="text-sm">{{ getDayLabel(index) }}</span>
          </div>

          <div
            class="flex min-w-0 items-center gap-2 ml-auto flex-wrap"
            :class="form.weeklyHours[index].isOpen ? '' : 'opacity-40'"
          >
            <template v-if="form.weeklyHours[index].isOpen">
              <input
                v-model="form.weeklyHours[index].start"
                type="time"
                class="rounded-lg border border-black/10 bg-white p-1 text-sm outline-none [font-variant-numeric:tabular-nums] transition hover:border-black"
              />
              <span class="text-sm text-slate-400">-</span>
              <input
                v-model="form.weeklyHours[index].end"
                type="time"
                class="rounded-lg border border-black/10 bg-white p-1 text-sm outline-none [font-variant-numeric:tabular-nums] transition hover:border-black"
              />
            </template>
            <span v-else class="text-sm font-medium text-slate-400">Off</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="space-y-2">
      <div v-if="enabledWorkingDays.length" class="space-y-2">
        <div
          v-for="{ day, index } in enabledWorkingDays"
          :key="index"
          class="rounded-xl border border-slate-200 bg-white p-3"
        >
          <div class="flex items-center justify-between gap-3">
            <p class="text-sm">{{ getDayLabel(index) }}</p>
            <button
              type="button"
              class="inline-flex h-7 items-center justify-center gap-1 rounded-lg border border-black/10 bg-white px-2.5 text-xs font-medium transition hover:border-black"
              @click="addBreak(index)"
            >
              <Plus class="h-3.5 w-3.5" />
              Add break
            </button>
          </div>

          <div v-if="getBreaksForWeekday(index).length" class="mt-2 space-y-1.5">
            <div
              v-for="(breakItem, breakIndex) in getBreaksForWeekday(index)"
              :key="`${breakItem.weekday}-${breakIndex}`"
              class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-black/10 p-3"
            >
              <span class="text-sm">Break</span>

              <div class="flex flex-wrap items-center gap-2">
                <input
                  v-model="breakItem.start"
                  type="time"
                  class="rounded-lg border border-black/10 bg-white p-1 text-sm outline-none [font-variant-numeric:tabular-nums] transition hover:border-black"
                />
                <span class="text-sm text-slate-400">-</span>
                <input
                  v-model="breakItem.end"
                  type="time"
                  class="rounded-lg border border-black/10 bg-white p-1 text-sm outline-none [font-variant-numeric:tabular-nums] transition hover:border-black"
                />
                <button
                  type="button"
                  class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white transition hover:border-black"
                  @click="removeBreak(breakItem)"
                >
                  <Trash class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-4 py-6 text-center text-sm text-slate-500">
        Turn on a working day first, then add breaks here.
      </div>
    </div>

  </div>

  <template #footer>
    <EditModalFooter
      v-model="form.valid_from"
      :saving="saving"
      @cancel="$emit('cancel')"
      @save="$emit('save', toPayload())"
    />
  </template>
  </ModalShell>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Plus, Trash } from 'lucide-vue-next'
import ToggleButton from '@/components/admin/ToggleButton.vue'
import EditModalFooter from '@/components/admin/EditModalFooter.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import { WEEKDAYS } from '@/data/calenderData'

defineEmits(['back', 'cancel', 'close', 'save'])

const props = defineProps({
  schedule: {
    type: Object,
    default: null,
  },
  saving: {
    type: Boolean,
    default: false,
  },
})


const currentView = ref('hours')
const form = ref(props.schedule ? cloneSchedule(props.schedule) : getDefaultSchedule())
const title = computed(() => (props.schedule ? 'Edit schedule' : 'Create schedule'))

const enabledWorkingDays = computed(() => {
  return form.value.weeklyHours
    .map((day, index) => ({ day, index }))
    .filter(({ day }) => day.isOpen)
})

watch(
  () => props.schedule,
  (schedule) => {
    form.value = schedule ? cloneSchedule(schedule) : getDefaultSchedule()
    currentView.value = 'hours'
  },
)

function cloneSchedule(schedule) {
  return JSON.parse(JSON.stringify(schedule))
}

function getDefaultSchedule() {
  return {
    valid_from: new Date().toISOString().slice(0, 10),
    valid_to: null,
    weeklyHours: WEEKDAYS.map((_, weekday) => ({
      weekday,
      isOpen: false,
      start: null,
      end: null,
    })),
    breaks: [],
  }
}

function addBreak(dayIndex) {
  const day = form.value.weeklyHours[dayIndex]
  form.value.breaks.push({
    weekday: dayIndex,
    start: day.start,
    end: day.end,
  })
}

function removeBreak(breakToRemove) {
  form.value.breaks = form.value.breaks.filter((breakItem) => breakItem !== breakToRemove)
}

function getBreaksForWeekday(weekday) {
  return form.value.breaks.filter((breakItem) => breakItem.weekday === weekday)
}

function getDayLabel(index) {
  return WEEKDAYS[index].label
}

function toPayload() {
  return {
    valid_from: form.value.valid_from,
    weeklyHours: form.value.weeklyHours,
    breaks: form.value.breaks
      .filter((breakItem) => enabledWorkingDays.value.some(({ index }) => index === breakItem.weekday)),
  }
}
</script>
