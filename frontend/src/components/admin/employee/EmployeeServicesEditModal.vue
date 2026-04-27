<template>
  <ModalShell
    show-back
    @back="$emit('back')"
    @close="$emit('close')"
  >
    <ModalHeader
      :title="title"
      :description="description"
    />

    <EmployeeServicePicker
      v-if="isPickerOpen"
      :available-service-options="availableServiceOptions"
      :pending-services="pendingServicesToAdd"
      :is-service-already-added="isServiceAlreadyAdded"
      @back="closeServicePicker"
      @toggle-service="togglePendingService"
      @add-services="addSelectedServices"
    />

  <div v-else class="space-y-4">
    <div class="rounded-2xl bg-white p-4">
      <div class="mb-4 flex items-center justify-end">
        <button
          type="button"
          class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl bg-[#ff9838] px-4 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c]"
          @click="openServicePicker"
        >
          + Add Services
        </button>
      </div>

      <div v-if="form.services.length" class="space-y-2">
        <div
          v-for="service in form.services"
          :key="service.name"
          class="rounded-xl border border-slate-200 bg-white p-3"
        >
          <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_110px_110px_32px] sm:items-center">
            <p class="text-sm font-semibold text-slate-900">{{ service.name }}</p>

            <div>
              <div
                class="rounded-lg border bg-white px-3 py-2 transition-all duration-250 focus-within:border-black"
                :class="fieldError(service, 'price') ? 'border-red-500' : 'border-black/10'"
              >
                <div class="flex items-center gap-2">
                  <input
                    v-model.number="service.price"
                    type="number"
                    min="1"
                    class="w-full outline-none"
                  />
                  <span class="shrink-0 text-sm font-medium text-slate-400">Ft</span>
                </div>
              </div>
              <p v-if="fieldError(service, 'price')" class="mt-1 text-xs text-red-500">
                {{ fieldError(service, 'price') }}
              </p>
            </div>

            <div>
              <div
                class="rounded-lg border bg-white px-3 py-2 transition-all duration-250 focus-within:border-black"
                :class="fieldError(service, 'duration') ? 'border-red-500' : 'border-black/10'"
              >
                <div class="flex items-center gap-2">
                  <input
                    v-model.number="service.duration"
                    type="number"
                    min="1"
                    class="w-full outline-none"
                  />
                  <span class="shrink-0 text-sm font-medium text-slate-400">min</span>
                </div>
              </div>
              <p v-if="fieldError(service, 'duration')" class="mt-1 text-xs text-red-500">
                {{ fieldError(service, 'duration') }}
              </p>
            </div>

            <button
              type="button"
              class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
              @click="removeService(service)"
            >
              <Trash class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <div
        v-else
        class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-6 text-center text-sm text-slate-500"
      >
        No services selected yet. Use `Add service` to start.
      </div>
    </div>

  </div>
  <template v-if="!isPickerOpen" #footer>
    <EditModalFooter
      v-model="form.valid_from"
      :date-disabled="dateDisabled"
      :min="validFromPolicy?.min"
      :max="validFromPolicy?.max"
      @cancel="$emit('cancel')"
      @save="handleSave"
    />
  </template>
  </ModalShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { Trash } from 'lucide-vue-next'
import EditModalFooter from '@/components/admin/EditModalFooter.vue'
import ModalHeader from '@/components/admin/ModalHeader.vue'
import ModalShell from '@/components/admin/ModalShell.vue'
import { getServices } from '@/api/index'
import EmployeeServicePicker from './EmployeeServicePicker.vue'
import { useToastStore } from '@/stores/ToastStore.js'

const emit = defineEmits(['back', 'cancel', 'close', 'save'])

const props = defineProps({
  services: {
    type: Object,
    default: null,
  },
  validFromPolicy: {
    type: Object,
    default: null,
  },
})

const form = reactive(createForm(props.services))
const submitted = ref(false)
const title = computed(() => (props.services ? 'Edit service change' : 'Service change'))
const validFromPolicy = computed(() => props.services?.valid_from_policy ?? props.validFromPolicy)
const dateDisabled = computed(() => validFromPolicy.value?.editable === false)
const description = computed(() => {
  return isPickerOpen.value
    ? 'Choose which services should be assigned to this employee.'
    : 'Update assigned services for this employee.'
})
const isPickerOpen = ref(false)
const pendingServicesToAdd = ref([])
const serviceOptions = ref([])
const availableServiceOptions = computed(() => serviceOptions.value.map((service) => ({ ...service })))
const errors = computed(() => getErrors())
const toast = useToastStore()

onMounted(fetchServiceOptions)

watch(
  () => props.services,
  (services) => {
    Object.assign(form, createForm(services))
    submitted.value = false
  },
)

function createForm(services) {
  return {
    valid_from: toInputDate(services?.valid_from) || props.validFromPolicy?.min || toInputDate(new Date().toISOString()),
    services: (services?.services ?? []).map((service) => ({ ...service })),
  }
}

async function fetchServiceOptions() {
  try {
    serviceOptions.value = (await getServices()).data.data
  } catch {
    toast.showError('Failed to load data.')
    serviceOptions.value = []
  }
}

function openServicePicker() {
  pendingServicesToAdd.value = []
  isPickerOpen.value = true
}

function closeServicePicker() {
  pendingServicesToAdd.value = []
  isPickerOpen.value = false
}

function isServiceAlreadyAdded(serviceName) {
  return form.services.some((service) => service.name === serviceName)
}

function togglePendingService(serviceName) {
  const next = new Set(pendingServicesToAdd.value)
  if (next.has(serviceName)) next.delete(serviceName)
  else next.add(serviceName)
  pendingServicesToAdd.value = [...next]
}

function addSelectedServices() {
  const selected = new Set(form.services.map((service) => service.name))
  const additions = availableServiceOptions.value
    .filter((service) => pendingServicesToAdd.value.includes(service.name) && !selected.has(service.name))
    .map((service) => ({
      service_id: service.id,
      name: service.name,
      price: null,
      duration: null,
    }))

  form.services = [...form.services, ...additions]
  closeServicePicker()
}

function removeService(service) {
  form.services = form.services.filter((item) => item.name !== service.name)
}

function toPayload() {
  return {
    ...(dateDisabled.value ? {} : { valid_from: form.valid_from }),
    services: [...form.services],
  }
}

function handleSave() {
  submitted.value = true
  if (Object.keys(errors.value).length) return
  emit('save', toPayload())
}

function fieldError(service, field) {
  const index = form.services.indexOf(service)
  return submitted.value ? errors.value[`services.${index}.${field}`] : null
}

function getErrors() {
  const nextErrors = {}

  form.services.forEach((service, index) => {
    validatePositiveNumber(nextErrors, `services.${index}.price`, service.price)
    validatePositiveNumber(nextErrors, `services.${index}.duration`, service.duration)
  })

  return nextErrors
}

function validatePositiveNumber(nextErrors, key, value) {
  if (value === null || value === undefined || value === '') {
    nextErrors[key] = 'Required'
    return
  }

  if (Number(value) <= 0) {
    nextErrors[key] = 'Must be greater than 0'
  }
}

function toInputDate(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value).slice(0, 10)
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}
</script>
