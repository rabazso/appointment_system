<template>
  <PageLayout
    current-section="profile"
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
              <div v-if="profileError" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700">
                {{ profileError }}
              </div>
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
                          Accepted types: JPG, PNG or WEBP. <br> Max size 5MB
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
                            @click="removeProfilePhoto"
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
                          class="w-full rounded-xl border border-black/10 bg-white px-4 py-3 pr-12 text-sm text-black outline-none transition focus:border-black"
                          placeholder="Your name"
                        >
                      </div>
                    </div>
                    <div class="space-y-3">
                      <label class="block text-sm font-semibold tracking-wide text-black">Description</label>
                      <div class="relative">
                        <textarea
                          v-model="profile.description"
                          rows="4"
                          class="w-full rounded-xl border border-black/10 bg-white px-4 py-3 pr-12 text-sm leading-6 text-black outline-none transition focus:border-black resize-none"
                          placeholder="Write a short bio about your work"
                        ></textarea>
                      </div>
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
                            class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                          >
                        </div>
                        <div class="min-w-0">
                          <input
                            v-model="link.url"
                            type="url"
                            placeholder="https://..."
                            class="w-full rounded-xl border border-black/10 bg-white px-3 py-2 text-sm outline-none transition hover:border-black"
                          >
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
          <div class="mb-4 flex items-start justify-between gap-3">
            <div>
              <h2 class="text-2xl font-semibold text-black">Gallery</h2>
              <p class="mt-1 text-sm text-slate-500">Upload and manage the images customers see first.</p>
            </div>

            <button
              type="button"
              class="inline-flex items-center justify-center rounded-full border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              @click="triggerGalleryUpload"
            >
              + Upload images
            </button>
          </div>

          <div class="flex min-h-0 flex-1 flex-col rounded-2xl border border-black/10 bg-white p-3">
            <div v-if="galleryDraft.length" class="grid grid-cols-[repeat(auto-fill,minmax(96px,1fr))] gap-3 overflow-auto pr-1">
              <div
                v-for="image in galleryDraft"
                :key="image.id"
                class="group relative aspect-square overflow-hidden rounded-xl border border-black/10 bg-slate-100"
              >
                <img :src="image.preview_url" alt="Work sample" class="h-full w-full object-cover">
                <button
                  type="button"
                  class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-900 transition hover:bg-white"
                  @click="openDeleteGalleryModal(image)"
                >
                  <X class="h-4 w-4" />
                </button>
              </div>

            </div>

            <div
              v-else
              class="flex min-h-0 flex-1 items-center justify-center rounded-xl border-black/15 text-sm text-slate-400"
            >
              No images uploaded
            </div>

            <input
              ref="galleryInput"
              type="file"
              accept="image/*"
              multiple
              class="hidden"
              @change="onGalleryFilesSelected"
            >
          </div>

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
        v-if="galleryDeleteTarget"
        title="Delete image"
        description="This will remove the image from your public gallery."
        question-prefix="Are you sure you want to delete "
        target-name="this image"
        @close="closeDeleteGalleryModal"
        @confirm="confirmDeleteGallery"
      />
    </div>
  </PageLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Upload, Trash, X } from 'lucide-vue-next'
import PageLayout from '@/components/employee/PageLayout.vue'
import ConfirmDeleteModal from '@/components/admin/ConfirmDeleteModal.vue'
import {
  deleteEmployeeProfileGalleryImage,
  getEmployeeProfile,
  patchEmployeeProfile,
  uploadEmployeeProfileAvatar,
  uploadEmployeeProfileGalleryImage,
} from '@/api/index.js'

const profileLoading = ref(true)
const profileSaving = ref(false)
const profileError = ref('')
const viewMode = ref('contact')
const profileImageInput = ref(null)
const galleryInput = ref(null)
const avatarFile = ref(null)
const profileImagePreviewOpen = ref(false)
const links = ref([])
const galleryDraft = ref([])
const savedProfileSnapshot = ref('')
const galleryDeleteTarget = ref(null)

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
  profileError.value = ''

  try {
    const response = await getEmployeeProfile()
    const mapped = mapProfileResponse(unwrapResourceItem(response.data))
    profile.value = mapped
    links.value = mapped.links
    galleryDraft.value = mapped.gallery.map((image) => ({ ...image, file: null, isNew: false }))
    avatarFile.value = null
    savedProfileSnapshot.value = JSON.stringify(snapshotProfileState(mapped, mapped.links, null))
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to load profile data.'
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

const triggerGalleryUpload = () => {
  galleryInput.value?.click()
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

const onGalleryFilesSelected = async (event) => {
  const files = Array.from(event.target.files || [])
  if (!files.length) return

  profileError.value = ''
  try {
    for (const file of files) {
      const formData = new FormData()
      formData.append('image', file)
      await uploadEmployeeProfileGalleryImage(formData)
    }
    await loadProfile()
  } catch {
    profileError.value = 'Failed to save image.'
  } finally {
    event.target.value = ''
  }
}

const openDeleteGalleryModal = (image) => {
  galleryDeleteTarget.value = image
}

const closeDeleteGalleryModal = () => {
  galleryDeleteTarget.value = null
}

const confirmDeleteGallery = async () => {
  if (!galleryDeleteTarget.value?.id) return

  profileError.value = ''
  try {
    await deleteEmployeeProfileGalleryImage(galleryDeleteTarget.value.id)
    await loadProfile()
    closeDeleteGalleryModal()
  } catch {
    profileError.value = 'Failed to delete image.'
  }
}

const saveProfile = async () => {
  profileSaving.value = true
  profileError.value = ''

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
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to update profile.'
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

const profileIsDirty = computed(() => {
  return JSON.stringify(snapshotProfileState(profile.value, links.value, avatarFile.value)) !== savedProfileSnapshot.value
})

onMounted(() => {
  loadProfile()
})
</script>
