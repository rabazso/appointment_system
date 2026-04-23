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
        @click="activeSection = item.section"
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

defineEmits(['close'])

defineProps({
  service: {
    type: Object,
    required: true,
  },
})

const activeSection = ref('menu')

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
</script>
