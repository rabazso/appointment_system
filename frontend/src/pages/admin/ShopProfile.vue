<template>
  <PageLayout
    role="admin"
    title="Shop Profile"
    description="Manage your public contact details and gallery"
  >

      <div class="mx-auto mb-4 flex rounded-2xl bg-white p-1 shadow-sm xl:hidden">
        <button
          type="button"
          class="rounded-md px-4 py-2 font-semibold transition"
          :class="viewMode === 'contact' ? 'bg-secondary' : 'bg-white'"
          @click="viewMode = 'contact'"
        >
          Contact
        </button>
        <button
          type="button"
          class="rounded-md px-4 py-2 font-semibold transition"
          :class="viewMode === 'gallery' ? 'bg-secondary' : 'bg-white'"
          @click="viewMode = 'gallery'"
        >
          Gallery
        </button>
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
              <div
                class="rounded-xl border bg-white px-4 py-3 transition"
                :class="fieldErrors.phone ? 'border-rose-500 focus-within:border-rose-500' : 'border-black/10 focus-within:border-black'"
              >
                <input
                  v-model="form.phone"
                  type="text"
                  placeholder="+36 ..."
                  class="w-full bg-transparent text-sm outline-none"
                  @input="handleFieldInput('phone')"
                />
              </div>
              <p v-if="fieldErrors.phone" class="text-xs text-rose-600">{{ fieldErrors.phone }}</p>
            </div>

            <div class="min-w-0 space-y-2">
              <label class="text-sm font-semibold text-slate-900">Email</label>
              <div
                class="rounded-xl border bg-white px-4 py-3 transition"
                :class="fieldErrors.email ? 'border-rose-500 focus-within:border-rose-500' : 'border-black/10 focus-within:border-black'"
              >
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="hello@shop.com"
                  class="w-full bg-transparent text-sm outline-none"
                  @input="handleFieldInput('email')"
                />
              </div>
              <p v-if="fieldErrors.email" class="text-xs text-rose-600">{{ fieldErrors.email }}</p>
            </div>

            <div class="space-y-2 md:col-span-2">
              <label class="text-sm font-semibold text-slate-900">Address</label>
              <div
                class="rounded-xl border bg-white px-4 py-3 transition"
                :class="fieldErrors.address ? 'border-rose-500 focus-within:border-rose-500' : 'border-black/10 focus-within:border-black'"
              >
                <input
                  v-model="form.address"
                  type="text"
                  placeholder="Street, city"
                  class="w-full bg-transparent text-sm outline-none"
                  @input="handleFieldInput('address')"
                />
              </div>
              <p v-if="fieldErrors.address" class="text-xs text-rose-600">{{ fieldErrors.address }}</p>
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
                    class="w-full rounded-xl border bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                    :class="linkErrorByIndex(index)?.label ? 'border-rose-500' : 'border-black/10'"
                    @input="handleLinkInput(index, 'label')"
                  />
                  <p v-if="linkErrorByIndex(index)?.label" class="mt-1 text-xs text-rose-600">{{ linkErrorByIndex(index)?.label }}</p>
                </div>
                <div class="min-w-0">
                  <input
                    v-model="link.url"
                    type="url"
                    placeholder="https://..."
                    class="w-full rounded-xl border bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                    :class="linkErrorByIndex(index)?.url ? 'border-rose-500' : 'border-black/10'"
                    @input="handleLinkInput(index, 'url')"
                  />
                  <p v-if="linkErrorByIndex(index)?.url" class="mt-1 text-xs text-rose-600">{{ linkErrorByIndex(index)?.url }}</p>
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
          <ImageGalleryManager
            :images="galleryItems"
            @upload="onGalleryUpload"
            @delete="deleteGalleryImage"
          />
        </section>
      </div>
  </PageLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Trash } from 'lucide-vue-next'
import PageLayout from '@/components/PageLayout.vue'
import ImageGalleryManager from '@/components/gallery/ImageGalleryManager.vue'
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
  fetchShopImages,
  uploadImages,
  deleteImage,
} = useShopGallery()
const toast = useToastStore()

const viewMode = ref('contact')
const fieldErrors = ref({
  phone: '',
  email: '',
  address: '',
  links: {},
})

const MAX_IMAGE_SIZE_BYTES = 4096 * 1024
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp']

onMounted(async () => {
  await Promise.all([loadSettings(), loadGallery()])
})

async function loadSettings() {
  try {
    await fetchShopInformation()
    clearFieldErrors()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
}

async function saveSettings() {
  clearFieldErrors()
  if (!validateForm()) {
    return
  }

  try {
    await saveShopInformation(shopInformationId.value, {
      phone: form.phone?.trim() || null,
      email: form.email?.trim() || null,
      address: form.address?.trim() || null,
      links: form.links
        .map((link) => ({
          label: link.label?.trim() || null,
          url: link.url?.trim() || null,
        }))
        .filter((link) => link.label || link.url),
    })

    toast.show('Changes saved successfully.')
  } catch (error) {
    applyServerValidationErrors(error?.response?.data?.errors)
    toast.showError('Failed to save changes.')
  }
}

async function loadGallery() {
  try {
    await fetchShopImages()
  } catch (error) {
    toast.showError('Failed to load data.')
  }
}

async function onGalleryUpload(files) {
  try {
    validateGalleryImages(files)
    await uploadImages(files)
    toast.show('Changes saved successfully.')
  } catch {
    toast.showError('Failed to save image.')
  }
}

function validateGalleryImages(files) {
  const invalidTypeImage = files.find((file) => file && !ALLOWED_IMAGE_TYPES.includes(file.type))

  if (invalidTypeImage) {
    throw new Error('Only JPG, PNG and WebP images are allowed.')
  }

  const oversizedImage = files.find((file) => file && file.size > MAX_IMAGE_SIZE_BYTES)

  if (oversizedImage) {
    throw new Error('One or more images are too large. Maximum size is 4 MB.')
  }
}

async function deleteGalleryImage(image) {
  try {
    await deleteImage(image.id)
    toast.show('Changes saved successfully.')
  } catch {
    toast.showError('Failed to save changes.')
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
  fieldErrors.value.links = {}
}

function resetChanges() {
  clearFieldErrors()
  loadSettings()
}

function clearFieldErrors() {
  fieldErrors.value = {
    phone: '',
    email: '',
    address: '',
    links: {},
  }
}

function handleFieldInput(field) {
  if (fieldErrors.value[field]) {
    fieldErrors.value[field] = ''
  }
}

function handleLinkInput(index, field) {
  const linkErrors = fieldErrors.value.links?.[index]
  if (!linkErrors) return

  if (linkErrors[field]) {
    linkErrors[field] = ''
  }

  if (!linkErrors.label && !linkErrors.url) {
    delete fieldErrors.value.links[index]
  }
}

function linkErrorByIndex(index) {
  return fieldErrors.value.links?.[index] || null
}

function isValidEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

function isValidUrl(value) {
  try {
    const parsed = new URL(value)
    return parsed.protocol === 'http:' || parsed.protocol === 'https:'
  } catch {
    return false
  }
}

function validateForm() {
  const errors = {
    phone: '',
    email: '',
    address: '',
    links: {},
  }

  const phone = form.phone?.trim() || ''
  const email = form.email?.trim() || ''

  if (phone.length > 255) {
    errors.phone = 'Phone cannot be longer than 255 characters.'
  }

  if (email.length > 255) {
    errors.email = 'Email cannot be longer than 255 characters.'
  } else if (email && !isValidEmail(email)) {
    errors.email = 'Please enter a valid email address.'
  }

  form.links.forEach((link, index) => {
    const label = link.label?.trim() || ''
    const url = link.url?.trim() || ''
    const linkErrors = { label: '', url: '' }

    if (label.length > 255) {
      linkErrors.label = 'Label cannot be longer than 255 characters.'
    }

    if (url.length > 2048) {
      linkErrors.url = 'URL cannot be longer than 2048 characters.'
    } else if (url && !isValidUrl(url)) {
      linkErrors.url = 'Please enter a valid URL (http:// or https://).'
    }

    if (linkErrors.label || linkErrors.url) {
      errors.links[index] = linkErrors
    }
  })

  fieldErrors.value = errors
  return !(errors.phone || errors.email || errors.address || Object.keys(errors.links).length)
}

function applyServerValidationErrors(errors) {
  if (!errors || typeof errors !== 'object') return

  Object.entries(errors).forEach(([key, messages]) => {
    const message = Array.isArray(messages) ? messages[0] : messages
    if (!message) return

    if (key === 'phone' || key === 'email' || key === 'address') {
      fieldErrors.value[key] = String(message)
      return
    }

    const linkMatch = key.match(/^links\.(\d+)\.(label|url)$/)
    if (linkMatch) {
      const index = Number(linkMatch[1])
      const field = linkMatch[2]
      if (!fieldErrors.value.links[index]) {
        fieldErrors.value.links[index] = { label: '', url: '' }
      }
      fieldErrors.value.links[index][field] = String(message)
    }
  })
}

</script>
