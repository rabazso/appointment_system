  <template>
    <div class="flex bg-slate-100 h-dvh overflow-hidden">

      <Sidebar />

      <main class="flex-1 w-full p-6 overflow-y-auto">
        <header class="mb-8 flex items-center justify-between rounded-2xl bg-white p-8 shadow-sm">
          <div>
            <h1 class="text-4xl font-semibold text-black">Services</h1>
            <p class="mt-1 text-xs text-gray-500">Manage your business services</p>
          </div>

          <Button @click="openAddModal">+ new service</Button>
        </header>

        <div class="max-w-7xl mx-auto gap-6 grid grid-cols-1 xl:grid-cols-3 md:grid-cols-2 w-full">
          <article v-for="service in services" :key="service.id"
            class=" flex flex-col min-h-64 rounded-2xl bg-white p-4 shadow-lg">
            <div class="mb-3 flex items-center justify-between">
              <span class=" rounded-full px-2 py-1 text-xs font-semibold" :class="service.active
                ? 'bg-emerald-100 text-emerald-900'
                : 'bg-rose-100 text-rose-900'
                ">
                {{ service.active ? 'Active' : 'Inactive' }}
              </span>

              <div class="flex items-center gap-2">
                <button
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
                  @click="openEditModal(service)">
                  <Pencil class="h-5 w-5" />
                </button>

                <button
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
                  @click="openDeleteModal(service)">
                  <Trash class="h-5 w-5" />
                </button>
              </div>
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
              <ToggleButton v-model="service.active" @click="toggleServiceStatus(service)"/>
            </div>
          </article>
        </div>

        <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center pl-64" @click.self="showAddModal = false">
        <div class="bg-white rounded-2xl p-6 pt-12 w-96 relative">
          <button class="absolute top-2 right-2" @click="showAddModal = false">
            <X class="w-8 h-8" />
          </button>

          <div>
            <h2 class="text-2xl font-semibold mb-6">
              {{ isEditingService ? 'Edit service' : 'New service' }}
            </h2>

            <div class="mb-3 rounded-lg border border-black/10 p-2 transition-all duration-250 focus-within:border-black">
              <input
                v-model="serviceForm.name"
                type="text"
                placeholder="Service name"
                class="w-full !outline-none"
              />
            </div>

            <div class="mb-3 h-25 rounded-lg border border-black/10 p-2 transition-all duration-250 focus-within:border-black">
              <textarea
                v-model="serviceForm.description"
                type="text"
                placeholder="Service description"
                class="h-full w-full resize-none text-sm outline-none"
              />
            </div>

            <div class="flex justify-end">
              <Button @click="saveService">
                Save
              </Button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center pl-64" @click.self="showDeleteModal = false">
        <div class="bg-white rounded-2xl p-6 pt-12 w-64 relative">
          <button class="absolute top-2 right-2" @click="showDeleteModal = false">
            <X class="w-8 h-8" />
          </button>

          <p class="text-center mb-6">Are you sure you want to delete the service</p>
          
          <div class="flex justify-center">
            <Button @click="removeService">Confirm</Button>
          </div>
        </div>
      </div>

      </main>
    </div>
  </template>

  <script setup>
  import { ref, onMounted} from 'vue'
  import Button from '@/components/admin/Button.vue'
  import Sidebar from '@/components/admin/Sidebar.vue'
  import ToggleButton from '@/components/admin/ToggleButton.vue'
  import { Pencil, Trash, X } from 'lucide-vue-next'
  import {
    getServices,
    postService,
    putService,
    patchService,
    deleteService
  } from '@/api/index'

  const services = ref([])

  onMounted(async () => {
    services.value = (await getServices()).data
  })


  const showAddModal = ref(false)
  const showDeleteModal = ref(false)
  const selectedService = ref(null)
  const isEditingService = ref(false)
  const serviceForm = ref({
    name: '',
    description: '',
  })

  function openAddModal() {
    selectedService.value = null
    isEditingService.value = false
    serviceForm.value = {
      name: '',
      description: '',
    }
    showAddModal.value = true
  }

  function openEditModal(service) {
    selectedService.value = service
    isEditingService.value = true
    serviceForm.value = {
      name: service.name,
      description: service.description,
    }
    showAddModal.value = true
  }

  function openDeleteModal(service){
    selectedService.value = service
    showDeleteModal.value = true
  }

  async function saveService() {

    if (!isEditingService.value) {
      const newService = {
        name: serviceForm.value.name,
        description: serviceForm.value.description,
        active: true,
      }
        await postService(newService);
    }
  else {
      selectedService.value.name = serviceForm.value.name
      selectedService.value.description = serviceForm.value.description

      await putService(selectedService.value.id, {
        name: serviceForm.value.name,
        description: serviceForm.value.description});
    }

      services.value = (await getServices()).data 

    showAddModal.value = false
  }

  async function toggleServiceStatus(service) {

      await patchService(service.id, {active :service.active})
      services.value = (await getServices()).data
  }

  async function removeService() {

    await deleteService(selectedService.value.id)
    services.value = (await getServices()).data
    showDeleteModal.value = false
  }
  </script>