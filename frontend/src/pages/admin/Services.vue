<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex-1 w-full overflow-y-auto p-8">
      <Header
        title="Services"
        description="Manage your business services"
        @menu-click="sidebarOpen = true"
      >
        <template #actions>
          <Button @click="showServiceCreateModal = true">
            + new service
          </Button>
        </template>
      </Header>

      <div class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="service in services"
          :key="service.id"
          class="flex min-h-64 flex-col rounded-2xl bg-white p-4 shadow-lg transition hover:shadow-xl"
        >
          <div class="mb-3 flex items-center justify-between">
            <span
              class="rounded-full px-2 py-1 text-xs font-semibold"
              :class="service.is_available ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900'"
            >
              {{ service.is_available ? 'Available' : 'Unavailable' }}
            </span>

            <button
              type="button"
              class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
              @click.stop="openServiceDeleteModal(service)"
            >
              <Trash class="h-5 w-5" />
            </button>
          </div>

          <div class="mb-2">
            <h3 class="text-xl font-semibold text-black">
              {{ service.name }}
            </h3>
          </div>

          <p class="text-sm text-black">
            {{ service.description }}
          </p>

          <div class="mt-auto flex justify-end pt-3">
            <button
              type="button"
              class="inline-flex h-9 items-center gap-2 rounded-lg border border-black/10 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              @click.stop="openServiceConfigModal(service)"
            >
              <Settings class="h-4 w-4" />
              Configure
            </button>
          </div>
        </article>
      </div>
    </main>
  </div>

  <ServiceCreateModal
    v-if="showServiceCreateModal"
    :saving="savingService"
    @close="closeServiceCreateModal"
    @save="saveService"
  />

  <ServiceDeleteModal
    v-if="showServiceDeleteModal && selectedService"
    :service="selectedService"
    @close="closeServiceDeleteModal"
    @confirm="removeSelectedService"
  />

  <ServiceConfigureModal
    v-if="showServiceConfigModal && selectedService"
    :service="selectedService"
    @close="closeServiceConfigModal"
  />
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue'
import { Settings, Trash } from 'lucide-vue-next'
import { deleteService, getServices, postService } from '@/api/index'
import Button from '@/components/admin/Button.vue'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import ServiceCreateModal from '@/components/admin/service/ServiceCreateModal.vue'
import ServiceConfigureModal from '@/components/admin/service/ServiceConfigureModal.vue'
import ServiceDeleteModal from '@/components/admin/service/ServiceDeleteModal.vue'
import { useToastStore } from '@/stores/ToastStore.js'

const services = ref([])
const sidebarOpen = ref(false)
const selectedService = ref(null)
const showServiceCreateModal = ref(false)
const showServiceDeleteModal = ref(false)
const showServiceConfigModal = ref(false)
const savingService = ref(false)
const toast = useToastStore()

onMounted(async () => {
  await fetchServices()
})

async function fetchServices() {
  try {
    services.value = (await getServices()).data.data
  } catch (error) {
    toast.showError('Failed to load data.')
  }
}

function closeServiceCreateModal() {
  showServiceCreateModal.value = false
}

function openServiceDeleteModal(service) {
  selectedService.value = service
  showServiceDeleteModal.value = true
}

async function closeServiceDeleteModal() {
  showServiceDeleteModal.value = false
  await nextTick()
  selectedService.value = null
}

function openServiceConfigModal(service) {
  selectedService.value = service
  showServiceConfigModal.value = true
}

async function closeServiceConfigModal() {
  showServiceConfigModal.value = false
  await fetchServices()
  await nextTick()
  selectedService.value = null
}

async function saveService(payload) {
  savingService.value = true

  try {
    await postService(payload)
    await fetchServices()
    closeServiceCreateModal()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  } finally {
    savingService.value = false
  }
}

async function removeSelectedService() {
  if (!selectedService.value) return

  try {
    await deleteService(selectedService.value.id)
    await fetchServices()
    await closeServiceDeleteModal()
    toast.show('Changes saved successfully.')
  } catch (error) {
    toast.showError('Failed to save changes.')
  }
}
</script>
