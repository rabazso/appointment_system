<template>
  <ServiceAvailabilityEditModal
    v-if="isEditorOpen"
    :availability="editingAvailability"
    @back="closeEditor"
    @close="$emit('close')"
    @cancel="closeEditor"
    @save="saveSelectedAvailability"
  />

  <ModalShell
    v-else
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      title="Availability"
      description="Manage when this service can be booked."
    />

    <VersionsView
      :versions="availability"
      create-label="Availability change"
      @create="openCreate"
    >
      <template #card="{ version, index }">
        <article class="rounded-xl border border-slate-200 bg-white p-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <span
                class="inline-flex rounded-full px-4 py-1.5 text-sm font-semibold leading-none"
                :class="version.is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
              >
                {{ version.is_available ? 'Available' : 'Unavailable' }}
              </span>

              <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-700">
                <span class="text-slate-900">
                  <span class="font-medium">{{ index === 0 ? 'Valid since:' : 'Valid from:' }}</span>
                  {{ version.valid_from }}
                </span>
                <span v-if="version.valid_to">
                  <span class="font-medium">{{ index === 0 ? 'Valid until:' : 'Valid to:' }}</span>
                  {{ version.valid_to }}
                </span>
              </div>
            </div>

            <div class="flex shrink-0 gap-2">
              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
                @click="openEdit(version)"
              >
                <Pencil class="h-4 w-4" />
              </button>
              <button
                v-if="index !== 0"
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
                @click="deleteSelectedAvailability(version)"
              >
                <Trash class="h-4 w-4" />
              </button>
            </div>
          </div>
        </article>
      </template>
    </VersionsView>
  </ModalShell>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Pencil, Trash } from 'lucide-vue-next'
import VersionsView from '@/components/admin/VersionsView.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import { useServiceAvailabilityConfigurations } from '@/composables/useServiceAvailabilityConfigurations'
import ServiceAvailabilityEditModal from './ServiceAvailabilityEditModal.vue'

const emit = defineEmits(['back', 'close'])

const props = defineProps({
  service: {
    type: Object,
    required: true,
  },
})

const {
  availability,
  fetchAvailability,
  saveAvailability: createAvailability,
  saveExistingAvailability,
  deleteAvailability,
} = useServiceAvailabilityConfigurations(props.service.id)

const creatingAvailability = ref(false)
const editingAvailability = ref(null)

const isEditorOpen = computed(() => creatingAvailability.value || editingAvailability.value !== null)

onMounted(fetchAvailability)

function openCreate() {
  creatingAvailability.value = true
  editingAvailability.value = null
}

function openEdit(availabilityVersion) {
  creatingAvailability.value = false
  editingAvailability.value = availabilityVersion
}

function closeEditor() {
  creatingAvailability.value = false
  editingAvailability.value = null
}

async function saveSelectedAvailability(payload) {
  if (editingAvailability.value) {
    await saveExistingAvailability(editingAvailability.value.id, payload)
  } else {
    await createAvailability(payload)
  }

  closeEditor()
}

async function deleteSelectedAvailability(availabilityVersion) {
  await deleteAvailability(availabilityVersion.id)
}
</script>
