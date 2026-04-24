<template>
  <EmployeeServicesEditModal
    v-if="activeView === 'editor'"
    :services="selectedServices"
    :valid-from-policy="createValidFromPolicy"
    @back="closeEditor"
    @close="$emit('close')"
    @cancel="closeEditor"
    @save="saveServices"
  />

  <EmployeeServicesView
    v-else-if="activeView === 'view'"
    :services="selectedServices"
    @back="closeView"
    @close="$emit('close')"
    @edit="openEdit(selectedServices)"
    @delete="deleteSelectedServices(selectedServices)"
  />

  <ModalShell
    v-else
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="activeTitle"
      :description="activeDescription"
    />

    <VersionsView
      :versions="services"
      create-label="Service change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <VersionCard
          :valid-from="version.valid_from"
          :valid-to="version.valid_to"
          :index="index"
          @view="openView(version)"
          @edit="openEdit(version)"
          @delete="deleteSelectedServices(version)"
        >
          <p class="text-base font-semibold text-slate-900">{{ getServicesSummary(version) }}</p>
        </VersionCard>
      </template>
    </VersionsView>
  </ModalShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import VersionCard from '@/components/admin/VersionCard.vue'
import VersionsView from '@/components/admin/VersionsView.vue'
import { useEmployeeServiceConfigurations } from '@/composables/useEmployeeServiceConfigurations'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import EmployeeServicesEditModal from './EmployeeServicesEditModal.vue'
import EmployeeServicesView from './EmployeeServicesView.vue'
import { addDays, maxDate, parseISODate, toISO } from '@utils/date'

defineEmits(['back', 'close'])

const props = defineProps({
  employee: {
    type: Object,
    required: true,
  },
})

const {
  services,
  fetchServices,
  saveService: createService,
  saveExistingService,
  deleteService,
} = useEmployeeServiceConfigurations(props.employee.id)

const activeView = ref('list')
const selectedServices = ref(null)

const activeTitle = computed(() => {
  return 'Services'
})
const activeDescription = computed(() => {
  return 'Manage assigned services for this employee.'
})
const createValidFromPolicy = computed(() => getCreateValidFromPolicy(services.value))

onMounted(fetchServices)

function openView(services) {
  activeView.value = 'view'
  selectedServices.value = services
}

function closeView() {
  activeView.value = 'list'
  selectedServices.value = null
}

function openCreate() {
  activeView.value = 'editor'
  selectedServices.value = null
}

function openEdit(services) {
  activeView.value = 'editor'
  selectedServices.value = services
}

function closeEditor() {
  activeView.value = 'list'
  selectedServices.value = null
}

async function deleteSelectedServices(services) {
  await deleteService(services.id)
  closeView()
}

async function saveServices(payload) {
  if (selectedServices.value) {
    await saveExistingService(selectedServices.value.id, payload)
  } else {
    await createService(payload)
  }

  closeEditor()
}

function getServicesSummary(services) {
  const count = services.services?.length ?? 0
  if (!count) return 'No services assigned'
  if (count === 1) return '1 service assigned'
  return `${count} services assigned`
}

function getCreateValidFromPolicy(versions) {
  const tomorrow = addDays(new Date(), 1)
  const latestValidFrom = versions
    .map((version) => version.valid_from)
    .filter(Boolean)
    .sort()
    .at(-1)

  const min = latestValidFrom
    ? maxDate(tomorrow, addDays(parseISODate(latestValidFrom), 1))
    : tomorrow

  return {
    editable: true,
    min: toISO(min),
    max: null,
  }
}
</script>
