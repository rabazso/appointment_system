<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex min-h-0 flex-1 flex-col overflow-hidden p-4 md:p-6 xl:p-8">
      <Header
        title="Shop Gallery"
        description="Manage the gallery images shown on your public shop profile"
        @menu-click="sidebarOpen = true"
      />

      <div class="min-h-0 flex-1">
        <section class="flex h-full min-h-0 flex-col rounded-2xl bg-white p-6 shadow-sm">
          <div class="mb-4 flex items-start justify-between gap-3">
            <div>
              <h2 class="text-2xl font-semibold text-black">Gallery images</h2>
              <p class="mt-1 text-sm text-slate-500">Upload and manage the images customers see first.</p>
            </div>

            <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
              + Upload images
              <input
                type="file"
                accept="image/*"
                multiple
                class="hidden"
                @change="onGalleryChange"
              />
            </label>
          </div>

          <div v-if="gallery.length" class="grid grid-cols-[repeat(auto-fill,minmax(140px,1fr))] gap-3 overflow-auto pr-1">
            <div
              v-for="image in gallery"
              :key="image.id"
              class="group relative aspect-square cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
              @click="openPreview(image)"
            >
              <img
                :src="image.preview_url"
                :alt="`Gallery image ${image.id}`"
                class="h-full w-full object-cover"
              />
              <button
                type="button"
                class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/60 bg-white/90 text-slate-700 opacity-0 transition group-hover:opacity-100"
                @click.stop="removeGalleryImage(image)"
              >
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div
            v-else
            class="flex min-h-0 flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400"
          >
            No gallery images yet
          </div>
        </section>
      </div>
    </main>

    <div
      v-if="activeImage"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
      @click="closePreview"
    >
      <div class="relative max-h-full max-w-6xl" @click.stop>
        <button
          type="button"
          class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-slate-900 shadow-sm transition hover:bg-white"
          @click="closePreview"
        >
          <X class="h-5 w-5" />
        </button>

        <img
          :src="activeImage.original_url"
          :alt="`Gallery image ${activeImage.id}`"
          class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Trash2, X } from 'lucide-vue-next'
import { deleteShopImage, getShopImages, uploadShopImage } from '@/api'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'

const sidebarOpen = ref(false)
const activeImage = ref(null)
const gallery = ref([])

onMounted(async () => {
  await loadGallery()
})

async function loadGallery() {
  const response = await getShopImages()
  gallery.value = response.data.data ?? response.data ?? []
}

async function onGalleryChange(event) {
  const files = Array.from(event.target.files ?? [])
  event.target.value = ''

  for (const file of files) {
    const payload = new FormData()
    payload.append('image', file)
    const response = await uploadShopImage(payload)
    const uploadedImage = response.data.data ?? response.data
    gallery.value = [uploadedImage, ...gallery.value]
  }
}

async function removeGalleryImage(image) {
  await deleteShopImage(image.id)
  gallery.value = gallery.value.filter((item) => item.id !== image.id)

  if (activeImage.value?.id === image.id) {
    activeImage.value = null
  }
}

function openPreview(image) {
  activeImage.value = image
}

function closePreview() {
  activeImage.value = null
}
</script>
