<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <Sidebar :isOpen="sidebarOpen" @close="sidebarOpen = false" />

    <main class="flex min-h-0 flex-1 flex-col overflow-hidden p-4 md:p-6 xl:p-8">
      <Header
        title="Shop Profile"
        description="Manage your public contact details and gallery"
        @menu-click="sidebarOpen = true"
      />

      <div class="mb-4 flex justify-center xl:hidden">
        <div class="inline-flex rounded-full bg-black px-1.5 py-1 shadow-lg shadow-black/20">
        <button
          type="button"
          class="rounded-full px-5 py-2.5 text-sm font-semibold transition"
          :class="viewMode === 'contact' ? 'bg-secondary text-white' : 'bg-transparent text-white/90 hover:bg-white/10'"
          @click="viewMode = 'contact'"
        >
          Contact
        </button>
        <button
          type="button"
          class="rounded-full px-5 py-2.5 text-sm font-semibold transition"
          :class="viewMode === 'gallery' ? 'bg-secondary text-white' : 'bg-transparent text-white/90 hover:bg-white/10'"
          @click="viewMode = 'gallery'"
        >
          Gallery
        </button>
        </div>
      </div>

      <div class="flex min-h-0 flex-1 flex-col gap-4 xl:grid xl:grid-cols-2 xl:overflow-hidden">
        <section
          :class="[viewMode === 'contact' ? 'flex' : 'hidden', 'xl:flex']"
          class="min-h-0 flex-1 flex-col rounded-2xl bg-white p-6 shadow-sm"
        >
          <div class="mb-6 flex items-start justify-between gap-4">
            <div>
              <h2 class="text-2xl font-semibold text-black">Contact</h2>
              <p class="mt-1 text-sm text-slate-500">These details appear across your public shop profile.</p>
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
            <div class="min-w-0 space-y-2">
              <label class="text-sm font-semibold text-slate-900">Phone</label>
              <div class="rounded-xl border border-black/10 bg-white px-4 py-3 transition focus-within:border-black">
                <input
                  v-model="form.phone"
                  type="text"
                  placeholder="+36 ..."
                  class="w-full bg-transparent text-sm outline-none"
                  @input="markDirty"
                />
              </div>
            </div>

            <div class="min-w-0 space-y-2">
              <label class="text-sm font-semibold text-slate-900">Email</label>
              <div class="rounded-xl border border-black/10 bg-white px-4 py-3 transition focus-within:border-black">
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="hello@shop.com"
                  class="w-full bg-transparent text-sm outline-none"
                  @input="markDirty"
                />
              </div>
            </div>

            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-semibold text-slate-900">Address</label>
              <div class="rounded-xl border border-black/10 bg-white px-4 py-3 transition focus-within:border-black">
                <input
                  v-model="form.address"
                  type="text"
                  placeholder="Street, city"
                  class="w-full bg-transparent text-sm outline-none"
                  @input="markDirty"
                />
              </div>
            </div>
          </div>

          <section class="mt-6 flex min-h-0 flex-1 flex-col">
            <div class="flex items-center justify-between gap-3">
              <h3 class="text-sm font-semibold">Links: </h3>
              <button
                type="button"
                class="rounded-xl border border-black/10 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                @click="addLink"
              >
                + Add
              </button>
            </div>

            <div class="mt-4 min-h-0 flex-1 space-y-3 overflow-auto pr-1">
              <p v-if="form.links.length === 0" class="text-sm text-center text-slate-500">
                No links added.
              </p>

              <div
                v-for="(link, index) in form.links"
                :key="index"
                class="grid gap-3 md:grid-cols-[10rem_minmax(0,1fr)_auto]"
              >
                <div class="min-w-0">
                  <input
                    v-model="link.label"
                    type="text"
                    placeholder="Instagram"
                    class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                    @input="markDirty"
                  />
                </div>
                <div class="min-w-0">
                  <input
                    v-model="link.url"
                    type="url"
                    placeholder="https://..."
                    class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                    @input="markDirty"
                  />
                </div>
                <div class="flex items-center">
                  <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-black/10 text-gray-500 transition hover:border-black"
                    @click="removeLink(index)"
                    aria-label="Remove link"
                  >
                    <Trash class="size-4" />
                  </button>
                </div>
              </div>
            </div>
          </section>

          <div v-if="isDirty" class="mt-4 shrink-0 -mx-6 -mb-6 rounded-b-2xl border-t border-black/10 bg-white px-6 py-4">
            <div class="flex justify-end gap-3">
              <button
                type="button"
                :disabled="isLoading || !shopInformationId"
                class="inline-flex rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                :class="{ 'cursor-not-allowed opacity-60': isLoading || !shopInformationId }"
                @click="resetChanges"
              >
                Cancel
              </button>
              <button
                type="button"
                :disabled="isLoading || !shopInformationId"
                class="inline-flex rounded-xl bg-secondary px-4 py-2 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c]"
                :class="{ 'cursor-not-allowed opacity-60': isLoading || !shopInformationId }"
                @click="saveSettings"
              >
                Save changes
              </button>
            </div>
          </div>
        </section>

        <section
          :class="[viewMode === 'gallery' ? 'flex' : 'hidden', 'xl:flex']"
          class="min-h-0 flex-1 flex-col rounded-2xl bg-white p-6 shadow-sm"
        >
          <div class="mb-4 flex items-start justify-between gap-3">
            <div>
              <h2 class="text-2xl font-semibold text-black">Gallery</h2>
              <p class="mt-1 text-sm text-slate-500">Upload and manage the images customers see first.</p>
            </div>

            <div class="flex flex-col items-end gap-1">
              <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                + Upload images
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                  multiple
                  class="hidden"
                  @change="onGalleryChange"
                />
              </label>
              <p class="text-right text-xs text-slate-400">
                JPG, PNG, WebP. Max 4 MB each.
              </p>
            </div>
          </div>

          <div v-if="galleryItems.length" class="grid flex-1 grid-cols-[repeat(auto-fill,minmax(100px,1fr))] gap-3 overflow-auto pr-1">
            <div
              v-for="image in galleryItems"
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
                class="absolute right-1 top-1 inline-flex p-1 items-center justify-center rounded-lg border border-black/10 bg-white text-slate-700 opacity-0 transition group-hover:opacity-100 hover:border-black"
                @click.stop="removeGalleryImage(image)"
              >
                <Trash class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div
            v-else
            class="flex min-h-0 flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400"
          >
            No gallery images yet
          </div>

          <div v-if="galleryDirty" class="mt-4 shrink-0 -mx-6 -mb-6 rounded-b-2xl border-t border-black/10 bg-white px-6 py-4">
            <div class="flex justify-end gap-3">
              <button
                type="button"
                class="inline-flex rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                @click="resetGalleryChanges"
              >
                Cancel
              </button>
              <button
                type="button"
                class="inline-flex rounded-xl bg-secondary px-4 py-2 text-sm font-semibold text-black shadow-[0_10px_24px_rgba(249,115,22,0.18)] transition hover:bg-[#ffab5c]"
                @click="saveGalleryWithValidation"
              >
                Save changes
              </button>
            </div>
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
import { Trash, X } from 'lucide-vue-next'
import Header from '@/components/admin/Header.vue'
import Sidebar from '@/components/admin/Sidebar.vue'
import { useShopInformation } from '@/composables/useShopInformation'
import { useShopGallery } from '@/composables/useShopGallery'
import { useToastStore } from '@/stores/ToastStore.js'

const {
  form,
  isDirty,
  loading: isLoading,
  shopInformationId,
  fetchShopInformation,
  saveShopInformation,
} = useShopInformation()
const {
  images: galleryItems,
  deletedImageIds: deletedGalleryIds,
  galleryDirty,
  fetchShopImages,
  saveGallery,
} = useShopGallery()
const toast = useToastStore()

const sidebarOpen = ref(false)
const viewMode = ref('contact')
const activeImage = ref(null)

const MAX_IMAGE_SIZE_BYTES = 4096 * 1024
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp']

onMounted(async () => {
  await Promise.all([loadSettings(), loadGallery()])
})

async function loadSettings() {
  try {
    await fetchShopInformation()
  } catch (error) {
    toast.showError('Failed to load shop information.')
  }
}

async function saveSettings() {
  try {
    await saveShopInformation(shopInformationId.value, {
      phone: form.phone || null,
      email: form.email || null,
      address: form.address || null,
      links: form.links
        .map((link) => ({
          label: link.label?.trim() || null,
          url: link.url?.trim() || null,
        }))
        .filter((link) => link.label || link.url),
    })

    toast.show('Shop information saved.')
  } catch (error) {
    toast.showError('Failed to save shop information.')
  }
}

async function loadGallery() {
  try {
    await fetchShopImages()
  } catch (error) {
    toast.showError('Failed to load gallery.')
  }
}

function onGalleryChange(event) {
  const files = Array.from(event.target.files ?? [])
  event.target.value = ''

  for (const file of files) {
    galleryItems.value = [
      {
        id: createGalleryId(),
        preview_url: URL.createObjectURL(file),
        original_url: URL.createObjectURL(file),
        isNew: true,
        file,
      },
      ...galleryItems.value,
    ]
  }
}

function validateGalleryImages() {
  const newImages = galleryItems.value.filter((image) => image.isNew)
  const invalidTypeImage = newImages.find((image) => image.file && !ALLOWED_IMAGE_TYPES.includes(image.file.type))

  if (invalidTypeImage) {
    throw new Error('Only JPG, PNG and WebP images are allowed.')
  }

  const oversizedImage = newImages.find((image) => image.file && image.file.size > MAX_IMAGE_SIZE_BYTES)

  if (oversizedImage) {
    throw new Error('One or more images are too large. Maximum size is 4 MB.')
  }
}

function removeGalleryImage(image) {
  if (image.isNew) {
    galleryItems.value = galleryItems.value.filter((item) => item.id !== image.id)
    return
  }

  deletedGalleryIds.value = Array.from(new Set([...deletedGalleryIds.value, image.id]))
  galleryItems.value = galleryItems.value.filter((item) => item.id !== image.id)

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

async function resetGalleryChanges() {
  await loadGallery()
}

async function saveGalleryWithValidation() {
  try {
    validateGalleryImages()
    await saveGallery()
    toast.show('Gallery saved.')
  } catch (error) {
    toast.showError(error instanceof Error ? error.message : 'Failed to save gallery.')
  }
}

function addLink() {
  form.links.push({
    label: '',
    url: '',
  })
}

function removeLink(index) {
  form.links.splice(index, 1)
}

function markDirty() {}

function resetChanges() {
  loadSettings()
}

function createGalleryId() {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID()
  }

  return `gallery_${Date.now()}_${Math.random().toString(16).slice(2)}`
}

</script>
