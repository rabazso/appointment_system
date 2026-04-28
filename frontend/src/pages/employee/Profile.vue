<template>
  <PageLayout
    role="employee"
    title="Profile"
    description="Update your public profile, photo, and gallery."
  >
    <div class="mx-auto flex w-full min-h-0 flex-1 flex-col gap-4 xl:overflow-hidden">
      <div class="mx-auto flex rounded-2xl bg-white p-1 shadow-sm xl:hidden">
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
          class="min-h-0 flex-1 flex-col overflow-hidden rounded-2xl bg-white p-4 shadow-sm"
        >
          <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
              <div v-if="profileLoading" class="text-sm text-slate-500">
                Loading profile...
              </div>

              <div v-else class="flex min-h-0 flex-1 flex-col overflow-hidden">
                <div class="min-h-0 flex-1 overflow-auto">
                  <div class="mb-4">
                    <h3 class="text-2xl font-semibold text-black">Details</h3>
                    <p class="mt-1 text-sm text-slate-500">Manage your public profile photo and details.</p>
                  </div>

                  <div class="mb-5 flex flex-col gap-3">
                    <p class="text-sm font-semibold">Picture</p>
                    <div class="flex items-start gap-4">
                      <button
                        type="button"
                        class="h-20 w-20 overflow-hidden rounded-full bg-slate-200"
                        @click="openProfileImagePreview"
                      >
                        <img
                          v-if="profile.photo_url"
                          :src="profile.photo_url"
                          alt="Profile image"
                          class="h-full w-full object-cover"
                        >
                      </button>

                      <div class="flex min-w-0 flex-1 flex-col gap-2 pt-1">
                        <p class="max-w-xs text-xs leading-5 text-slate-500">
                          Accepted types: JPG, PNG or WEBP. <br> Max size 4MB
                        </p>

                        <div class="flex flex-wrap gap-2">
                          <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold border border-black/10 transition hover:border-black"
                            @click="triggerProfileImageUpload"
                          >
                            <Upload class="h-4 w-4" />
                            Upload
                          </button>
                          <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-black/10 bg-white px-3 py-2 text-sm font-semibold text-black transition hover:border-black"
                            @click="openDeleteProfilePhotoModal"
                          >
                            <Trash class="h-4 w-4" />
                            Remove
                          </button>
                        </div>

                        <input
                          ref="profileImageInput"
                          type="file"
                          accept="image/*"
                          class="hidden"
                          @change="onProfileImageSelected"
                        >
                      </div>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <div class="space-y-3">
                      <label class="block text-sm font-semibold tracking-wide text-black">Name</label>
                      <div class="relative">
                        <input
                          v-model="profile.name"
                          type="text"
                          class="w-full rounded-xl border bg-white px-4 py-3 pr-12 text-sm text-black outline-none transition focus:border-black"
                          :class="fieldErrors.name ? 'border-rose-500 focus:border-rose-500' : 'border-black/10'"
                          placeholder="Your name"
                        >
                      </div>
                      <p v-if="fieldErrors.name" class="text-xs text-rose-600">{{ fieldErrors.name }}</p>
                    </div>
                    <div class="space-y-3">
                      <label class="block text-sm font-semibold tracking-wide text-black">Description</label>
                      <div class="relative">
                        <textarea
                          v-model="profile.description"
                          rows="4"
                          class="w-full rounded-xl border bg-white px-4 py-3 pr-12 text-sm leading-6 text-black outline-none transition focus:border-black resize-none"
                          :class="fieldErrors.description ? 'border-rose-500 focus:border-rose-500' : 'border-black/10'"
                          placeholder="Write a short bio about your work"
                        ></textarea>
                      </div>
                      <p v-if="fieldErrors.description" class="text-xs text-rose-600">{{ fieldErrors.description }}</p>
                    </div>
                  </div>

                  <section class="mt-8 flex min-h-0 flex-1 flex-col">
                    <div class="flex items-center justify-between gap-3">
                      <h3 class="text-sm font-semibold tracking-wide text-black">Links:</h3>
                      <button
                        type="button"
                        class="rounded-xl border border-black/10 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-black"
                        @click="addLink"
                      >
                        + Add
                      </button>
                    </div>

                    <div class="mt-5 min-h-0 flex-1 space-y-3 overflow-visible pr-1">
                      <p v-if="links.length === 0" class="text-sm text-center text-slate-500">
                        No links added.
                      </p>

                      <div
                        v-for="(link, index) in links"
                        :key="link.id"
                        class="grid gap-3 border-b border-slate-200 pb-4 last:border-b-0 last:pb-0 grid-cols-1 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)_auto] sm:items-center"
                      >
                        <div class="min-w-0">
                          <input
                            v-model="link.label"
                            type="text"
                            placeholder="Instagram"
                            class="w-full rounded-xl border bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                            :class="linkErrorById(link.id)?.label ? 'border-rose-500' : 'border-black/10'"
                          >
                          <p v-if="linkErrorById(link.id)?.label" class="mt-1 text-xs text-rose-600">{{ linkErrorById(link.id)?.label }}</p>
                        </div>
                        <div class="min-w-0">
                          <input
                            v-model="link.url"
                            type="url"
                            placeholder="https://..."
                            class="w-full rounded-xl border bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                            :class="linkErrorById(link.id)?.url ? 'border-rose-500' : 'border-black/10'"
                          >
                          <p v-if="linkErrorById(link.id)?.url" class="mt-1 text-xs text-rose-600">{{ linkErrorById(link.id)?.url }}</p>
                        </div>
                        <div class="flex items-start sm:items-center">
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
                </div>

                <div v-if="profileIsDirty" class="mt-3 shrink-0 border-t border-black/10 bg-white px-0 pt-3">
                  <div class="flex justify-end gap-4">
                    <button
                      type="button"
                      class="rounded-lg border border-slate-300 px-6 py-3 text-sm font-medium text-black transition-opacity hover:bg-slate-50 hover:opacity-90"
                      @click="resetProfileChanges"
                    >
                      Cancel
                    </button>
                    <button
                      type="button"
                      class="rounded-lg bg-orange-400 px-8 py-3 text-sm font-medium text-black transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-70"
                      :disabled="profileSaving"
                      @click="saveProfile"
                    >
                      {{ profileSaving ? 'Saving...' : 'Save' }}
                    </button>
                  </div>
                </div>

              </div>
          </div>
        </section>

        <section
          :class="[viewMode === 'gallery' ? 'flex' : 'hidden', 'xl:flex']"
          class="flex min-h-0 flex-1 flex-col rounded-2xl bg-white p-4 shadow-sm"
        >
          <ImageGalleryManager
            :images="galleryDraft"
            helper-text="JPG, PNG, WebP. Max 4 MB each."
            empty-text="No images uploaded"
            @upload="onGalleryFilesSelected"
            @delete="confirmDeleteGallery"
          />
        </section>
      </div>

      <div
        v-if="profileImagePreviewOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6"
        @click.self="closeProfileImagePreview"
      >
        <div class="relative max-w-3xl">
          <button
            type="button"
            class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
            aria-label="Close image preview"
            @click="closeProfileImagePreview"
          >
            <span class="text-xl leading-none">×</span>
          </button>
          <img
            :src="profile.photo_url"
            alt="Profile image preview"
            class="max-h-[80vh] w-auto max-w-[90vw] rounded-2xl bg-white object-contain shadow-2xl"
          >
        </div>
      </div>

      <ConfirmDeleteModal
        v-if="profilePhotoDeleteTarget"
        title="Delete image"
    description="This will remove your current profile photo."
        question-prefix="Are you sure you want to delete "
        target-name="this image"
        @close="closeDeleteProfilePhotoModal"
        @confirm="confirmDeleteProfilePhoto"
      />

    </div>
  </PageLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Upload, Trash } from 'lucide-vue-next'
import PageLayout from '@/components/PageLayout.vue'
import ConfirmDeleteModal from '@/components/admin/ConfirmDeleteModal.vue'
import ImageGalleryManager from '@/components/gallery/ImageGalleryManager.vue'
import { useToastStore } from '@/stores/ToastStore.js'
import {
  deleteEmployeeProfileGalleryImage,
  getEmployeeProfile,
  patchEmployeeProfile,
  uploadEmployeeProfileAvatar,
  uploadEmployeeProfileGalleryImage,
} from '@/api/index.js'

const profileLoading = ref(true)
const profileSaving = ref(false)
const viewMode = ref('contact')
const profileImageInput = ref(null)
const avatarFile = ref(null)
const profileImagePreviewOpen = ref(false)
const links = ref([])
const galleryDraft = ref([])
const savedProfileSnapshot = ref('')
const profilePhotoDeleteTarget = ref(null)
const fieldErrors = ref({
  name: '',
  description: '',
  links: {},
})
const toast = useToastStore()
const MAX_GALLERY_IMAGE_SIZE_BYTES = 4096 * 1024

const profile = ref({
  name: '',
  description: '',
  photo_url: '',
  gallery: [],
})

const toAbsoluteUrl = (url) => {
  if (!url) return ''
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url
  }

  return `http://backend.vm1.test${url}`
}

const mapProfileResponse = (responseData) => ({
  name: responseData?.name || '',
  description: responseData?.bio || '',
  photo_url: toAbsoluteUrl(responseData?.profile_image?.preview_url || ''),
  links: normalizeLinks(responseData?.links),
  gallery: (responseData?.gallery || []).map((image) => ({
    ...image,
    preview_url: toAbsoluteUrl(image?.preview_url || ''),
    original_url: toAbsoluteUrl(image?.original_url || ''),
  })),
})

const unwrapResourceItem = (payload) => payload?.data ?? payload

const loadProfile = async () => {
  profileLoading.value = true
  clearFieldErrors()

  try {
    const response = await getEmployeeProfile()
    const mapped = mapProfileResponse(unwrapResourceItem(response.data))
    profile.value = mapped
    links.value = mapped.links
    galleryDraft.value = mapped.gallery.map((image) => ({ ...image, file: null, isNew: false }))
    avatarFile.value = null
    savedProfileSnapshot.value = JSON.stringify(snapshotProfileState(mapped, mapped.links, null))
  } catch {
    toast.showError('Failed to load profile data.')
  } finally {
    profileLoading.value = false
  }
}

const triggerProfileImageUpload = () => {
  profileImageInput.value?.click()
}

const openProfileImagePreview = () => {
  if (!profile.value.photo_url) return
  profileImagePreviewOpen.value = true
}

const closeProfileImagePreview = () => {
  profileImagePreviewOpen.value = false
}

const onProfileImageSelected = (event) => {
  const [file] = event.target.files || []
  if (!file) return

  avatarFile.value = file
  profile.value.photo_url = URL.createObjectURL(file)
  profileImagePreviewOpen.value = false
}

const removeProfilePhoto = () => {
  if (avatarFile.value && profile.value.photo_url?.startsWith('blob:')) {
    URL.revokeObjectURL(profile.value.photo_url)
  }

  avatarFile.value = null
  profile.value.photo_url = ''
}

const openDeleteProfilePhotoModal = () => {
  if (!profile.value.photo_url) return
  profilePhotoDeleteTarget.value = { type: 'profile-photo' }
}

const closeDeleteProfilePhotoModal = () => {
  profilePhotoDeleteTarget.value = null
}

const confirmDeleteProfilePhoto = () => {
  if (!profilePhotoDeleteTarget.value) return
  removeProfilePhoto()
  closeDeleteProfilePhotoModal()
}

function addLink() {
  links.value.push({
    id: createLinkId(),
    label: '',
    url: '',
  })
}

function removeLink(index) {
  links.value.splice(index, 1)
}

function normalizeLinks(value) {
  if (!Array.isArray(value)) return []

  return value.map((link) => ({
    id: createLinkId(),
    label: link?.label ?? '',
    url: link?.url ?? '',
  }))
}

const onGalleryFilesSelected = async (files) => {
  if (!files.length) return
  const oversizedFile = files.find((file) => file && file.size > MAX_GALLERY_IMAGE_SIZE_BYTES)
  if (oversizedFile) {
    toast.showError('One or more images are too large. Maximum size is 4 MB.')
    return
  }

  try {
    for (const file of files) {
      const formData = new FormData()
      formData.append('image', file)
      await uploadEmployeeProfileGalleryImage(formData)
    }
    await loadProfile()
  } catch {
    toast.showError('Failed to save image.')
  }
}
const confirmDeleteGallery = async (image) => {
  if (!image?.id) return

  try {
    await deleteEmployeeProfileGalleryImage(image.id)
    await loadProfile()
    toast.show('Changes saved successfully.')
  } catch {
    toast.showError('Failed to delete image.')
  }
}

const saveProfile = async () => {
  clearFieldErrors()
  if (!validateProfileForm()) {
    return
  }

  profileSaving.value = true

  try {
    const payload = {
      name: profile.value.name || '',
      bio: profile.value.description || '',
      links: links.value
        .map((link) => ({
          label: link.label?.trim() || '',
          url: link.url?.trim() || '',
        }))
        .filter((link) => link.label && link.url),
    }

    await patchEmployeeProfile(payload)

    if (avatarFile.value) {
      const formData = new FormData()
      formData.append('image', avatarFile.value)
      await uploadEmployeeProfileAvatar(formData)
    }

    await loadProfile()
    avatarFile.value = null
    toast.show('Profile updated.')
  } catch {
    toast.showError('Failed to update profile.')
  } finally {
    profileSaving.value = false
  }
}

const resetProfileChanges = () => {
  avatarFile.value = null
  loadProfile()
}

function createLinkId() {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID()
  }

  return `link_${Date.now()}_${Math.random().toString(16).slice(2)}`
}

function snapshotProfileState(profileState, linkState, avatarState) {
  return {
    name: profileState?.name || '',
    description: profileState?.description || '',
    links: (linkState || [])
      .map((link) => ({
        label: link.label?.trim() || '',
        url: link.url?.trim() || '',
      }))
      .filter((link) => link.label && link.url),
    avatar: Boolean(avatarState),
  }
}

function clearFieldErrors() {
  fieldErrors.value = {
    name: '',
    description: '',
    links: {},
  }
}

function linkErrorById(linkId) {
  return fieldErrors.value.links?.[linkId] || null
}

function isValidUrl(value) {
  try {
    const parsed = new URL(value)
    return parsed.protocol === 'http:' || parsed.protocol === 'https:'
  } catch {
    return false
  }
}

function validateProfileForm() {
  const errors = {
    name: '',
    description: '',
    links: {},
  }

  const name = profile.value.name?.trim() || ''
  const description = profile.value.description?.trim() || ''

  if (!name) {
    errors.name = 'Name is required.'
  } else if (name.length > 255) {
    errors.name = 'Name cannot be longer than 255 characters.'
  }

  if (description.length > 2000) {
    errors.description = 'Description cannot be longer than 2000 characters.'
  }

  links.value.forEach((link) => {
    const label = link.label?.trim() || ''
    const url = link.url?.trim() || ''

    if (!label && !url) {
      return
    }

    const linkErrors = { label: '', url: '' }

    if (!label) {
      linkErrors.label = 'Label is required when URL is filled.'
    } else if (label.length > 50) {
      linkErrors.label = 'Label cannot be longer than 50 characters.'
    }

    if (!url) {
      linkErrors.url = 'URL is required when label is filled.'
    } else if (url.length > 500) {
      linkErrors.url = 'URL cannot be longer than 500 characters.'
    } else if (!isValidUrl(url)) {
      linkErrors.url = 'Please enter a valid URL (http:// or https://).'
    }

    if (linkErrors.label || linkErrors.url) {
      errors.links[link.id] = linkErrors
    }
  })

  fieldErrors.value = errors

  return !(errors.name || errors.description || Object.keys(errors.links).length)
}

const profileIsDirty = computed(() => {
  return JSON.stringify(snapshotProfileState(profile.value, links.value, avatarFile.value)) !== savedProfileSnapshot.value
})

onMounted(() => {
  loadProfile()
})
</script>
