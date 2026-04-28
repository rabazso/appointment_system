<template>
  <ModalShell
    v-if="activeSection === 'menu'"
    @close="$emit('close')"
  >
    <ModalHeader
      title="Service Configuration"
      description="Choose what you want to configure for this service."
    />

    <div class="space-y-3">
      <button
        v-for="item in menuItems"
        :key="item.section"
        type="button"
        class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-4 text-left transition hover:bg-slate-50"
        :disabled="isOpeningSection !== null"
        @click="openSection(item.section)"
      >
        <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600">
          <component
            :is="item.icon"
            class="h-6 w-6"
          />
        </div>
        <div>
          <p class="text-lg font-semibold text-slate-900">{{ item.title }}</p>
          <p class="mt-1 text-sm text-slate-500">{{ item.description }}</p>
        </div>
      </button>
    </div>
  </ModalShell>

  <ServiceDetailsModal
    v-else-if="activeSection === 'details'"
    :service="service"
    @back="showMenu"
    @close="$emit('close')"
  />

  <ServiceAvailabilityModal
    v-else
    :service="service"
    :versions="availabilityVersions"
    :refresh-section="fetchAvailability"
    @back="showMenu"
    @close="$emit('close')"
  />
</template>

<script setup>
import { ref } from 'vue'
import { CircleCheck, FileText } from 'lucide-vue-next'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import ServiceAvailabilityModal from './ServiceAvailabilityModal.vue'
import ServiceDetailsModal from './ServiceDetailsModal.vue'
import { useServiceAvailabilityConfigurations } from '@/composables/useServiceAvailabilityConfigurations'
import { useToastStore } from '@/stores/ToastStore.js'

defineEmits(['close'])

const props = defineProps({
  service: {
    type: Object,
    required: true,
  },
})

const activeSection = ref('menu')
const isOpeningSection = ref(null)
const toast = useToastStore()
const { availability: availabilityVersions, fetchAvailability: loadAvailability } = useServiceAvailabilityConfigurations(props.service.id)

const menuItems = [
  {
    section: 'details',
    title: 'Details',
    description: 'Edit service name and description.',
    icon: FileText,
  },
  {
    section: 'availability',
    title: 'Availability',
    description: 'Manage when this service can be booked.',
    icon: CircleCheck,
  },
]

function showMenu() {
  activeSection.value = 'menu'
}

async function openSection(section) {
  if (isOpeningSection.value) return

  if (section === 'details') {
    activeSection.value = section
    return
  }

  isOpeningSection.value = section
  try {
    await loadAvailability()
    activeSection.value = section
  } catch (error) {
    toast.showError('Failed to load data.')
  } finally {
    isOpeningSection.value = null
  }
}

async function fetchAvailability() {
  await loadAvailability()
}
</script>
