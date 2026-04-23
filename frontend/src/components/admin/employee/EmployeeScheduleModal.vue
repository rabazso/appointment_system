<template>
  <EmployeeScheduleEditModal
    v-if="activeView === 'editor'"
    :schedule="selectedSchedule"
    @back="closeEditor"
    @close="$emit('close')"
    @cancel="closeEditor"
    @save="saveSchedule"
  />

  <EmployeeScheduleView
    v-else-if="activeView === 'view'"
    :schedule="selectedSchedule"
    @back="closeView"
    @close="$emit('close')"
    @edit="openEdit(selectedSchedule)"
    @delete="deleteSelectedSchedule(selectedSchedule)"
  />

  <ModalShell
    v-else
    :showBack="true"
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="activeTitle"
      :description="activeDescription"
    />

    <VersionsView
      :versions="schedules"
      create-label="Schedule change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <VersionCard
          :valid-from="version.valid_from"
          :valid-to="version.valid_to"
          :index="index"
          @view="openView(version)"
          @edit="openEdit(version)"
          @delete="deleteSelectedSchedule(version)"
        >
          <p class="text-base font-semibold text-slate-900">{{ getScheduleSummary(version) }}</p>
        </VersionCard>
      </template>
    </VersionsView>
  </ModalShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import VersionCard from '@/components/admin/VersionCard.vue'
import VersionsView from '@/components/admin/VersionsView.vue'
import { useEmployeeScheduleConfigurations } from '@/composables/useEmployeeScheduleConfigurations'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeScheduleEditModal from './EmployeeScheduleEditModal.vue'
import EmployeeScheduleView from './EmployeeScheduleView.vue'

defineEmits(['back', 'close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

const {
  schedules,
  fetchSchedules,
  saveSchedule: createSchedule,
  saveExistingSchedule,
  deleteSchedule,
} = useEmployeeScheduleConfigurations(props.employee.id)

const activeView = ref('list')
const selectedSchedule = ref(null)

const activeTitle = computed(() => {
  return 'Schedule'
})
const activeDescription = computed(() => {
  return 'Review schedule changes and working hours.'
})

onMounted(fetchSchedules)

function openView(schedule) {
  activeView.value = 'view'
  selectedSchedule.value = schedule
}

function closeView() {
  activeView.value = 'list'
  selectedSchedule.value = null
}

async function deleteSelectedSchedule(schedule) {
  await deleteSchedule(schedule.id)
  closeView()
}

function openCreate() {
  activeView.value = 'editor'
  selectedSchedule.value = null
}

function openEdit(schedule) {
  activeView.value = 'editor'
  selectedSchedule.value = schedule
}

function closeEditor() {
  activeView.value = 'list'
  selectedSchedule.value = null
}

async function saveSchedule(payload) {
  if (selectedSchedule.value) {
    await saveExistingSchedule(selectedSchedule.value.id, payload)
  } else {
    await createSchedule(payload)
  }

  closeEditor()
}

function getScheduleSummary(schedule) {
  const count = schedule.weeklyHours.filter((day) => day.isOpen).length
  if (!count) return 'No working days'
  if (count === 1) return '1 working day'
  return `${count} working days`
}
</script>
