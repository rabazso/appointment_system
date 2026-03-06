<template>
  <div class="flex bg-slate-100 h-dvh overflow-hidden">

    <Sidebar />

    <main class="flex-1 w-full p-6 overflow-y-auto">
      <header class="mb-8 flex items-center justify-between rounded-2xl bg-white p-8 shadow-sm">
        <div>
          <h1 class="text-4xl font-semibold text-black">Services</h1>
          <p class="mt-1 text-xs text-gray-500">Manage your business services</p>
        </div>

        <Button @click="">+ new service</Button>
      </header>

      <div class="lg:max-w-7xl md:max-w-md mx-auto gap-6 grid grid-cols-1 xl:grid-cols-3 lg:grid-cols-2 md:grid-cols-1">
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
                @click="">
                <Pencil class="h-5 w-5" />
              </button>

              <button
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
                @click="">
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
            <ToggleButton v-model="service.active" />
          </div>
        </article>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Button from '@/components/admin/Button.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import ToggleButton from '@/components/admin/ToggleButton.vue'
import { Pencil, Trash } from 'lucide-vue-next'
import { getServices } from '@/api/index'

const services = ref([])

onMounted(async () => {
  services.value = (await getServices()).data
})

</script>