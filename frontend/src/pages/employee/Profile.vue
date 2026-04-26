<script setup>
import { onMounted, ref } from 'vue'
import { Pencil, Plus, X } from 'lucide-vue-next'
import PageLayout from '@/components/employee/PageLayout.vue'
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
const profileSuccess = ref('')
const profileImageInput = ref(null)
const galleryInput = ref(null)
const avatarFile = ref(null)

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
    profile.value = mapProfileResponse(unwrapResourceItem(response.data))
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to load profile data.'
  } finally {
    profileLoading.value = false
  }
}

const triggerProfileImageUpload = () => {
  profileImageInput.value?.click()
}

const triggerGalleryUpload = () => {
  galleryInput.value?.click()
}

const onProfileImageSelected = (event) => {
  const [file] = event.target.files || []
  if (!file) return

  avatarFile.value = file
  profile.value.photo_url = URL.createObjectURL(file)
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
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to upload one or more gallery images.'
  } finally {
    event.target.value = ''
  }
}

const removeGalleryImage = async (imageId) => {
  profileError.value = ''
  try {
    await deleteEmployeeProfileGalleryImage(imageId)
    await loadProfile()
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to delete image.'
  }
}

const saveProfile = async () => {
  profileSaving.value = true
  profileError.value = ''
  profileSuccess.value = ''

  try {
    const payload = {
      name: profile.value.name || '',
      bio: profile.value.description || '',
    }

    await patchEmployeeProfile(payload)

    if (avatarFile.value) {
      const formData = new FormData()
      formData.append('image', avatarFile.value)
      await uploadEmployeeProfileAvatar(formData)
    }

    await loadProfile()
    avatarFile.value = null
    profileSuccess.value = 'Profile updated successfully.'
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to update profile.'
  } finally {
    profileSaving.value = false
  }
}

const resetProfileChanges = () => {
  avatarFile.value = null
  profileSuccess.value = ''
  loadProfile()
}

onMounted(() => {
  loadProfile()
})
</script>

<template>
  <PageLayout
    current-section="profile"
    title="Profile"
    description="Update your public profile, photo, and gallery."
  >
    <div class="mx-auto w-full max-w-4xl">
      <section class="rounded-2xl bg-white p-6 shadow-sm md:p-8">
      <p v-if="profileError" class="rounded-md bg-red-100 px-4 py-2 text-sm text-red-700">
        {{ profileError }}
      </p>
      <p v-if="profileSuccess" class="rounded-md bg-green-100 px-4 py-2 text-sm text-green-700">
        {{ profileSuccess }}
      </p>
      <p v-if="profileLoading" class="text-sm text-gray-500">
        Loading profile...
      </p>

      <div v-else class="space-y-6">
        <div class="flex flex-col items-center gap-3">
          <button
            type="button"
            class="h-32 w-32 overflow-hidden rounded-full bg-gray-300"
            @click="triggerProfileImageUpload"
          >
            <img
              v-if="profile.photo_url"
              :src="profile.photo_url"
              alt="Profile image"
              class="h-full w-full object-cover"
            >
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 text-base"
            @click="triggerProfileImageUpload"
          >
            Edit
            <Pencil class="h-4 w-4" />
          </button>
          <input
            ref="profileImageInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="onProfileImageSelected"
          >
        </div>

        <div class="space-y-4">
          <label class="block text-lg font-medium">Name</label>
          <div class="relative">
            <input
              v-model="profile.name"
              type="text"
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 pr-12 text-base"
              placeholder="Your name"
            >
            <Pencil class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500" />
          </div>
        </div>

        <div class="space-y-4">
          <label class="block text-lg font-medium">Description</label>
          <div class="relative">
            <textarea
              v-model="profile.description"
              rows="4"
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 pr-12 text-base"
              placeholder="Write a short bio about your work"
            ></textarea>
            <Pencil class="pointer-events-none absolute right-4 top-4 h-5 w-5 text-gray-500" />
          </div>
        </div>

        <div class="space-y-4">
          <label class="block text-lg font-medium">Gallery</label>
          <div class="rounded-xl border border-gray-300 bg-white p-3">
            <div class="flex flex-wrap gap-3">
              <div
                v-for="image in profile.gallery"
                :key="image.id"
                class="relative h-24 w-24 overflow-hidden rounded bg-gray-200"
              >
                <img :src="image.preview_url" alt="Work sample" class="h-full w-full object-cover">
                <button
                  type="button"
                  class="absolute -right-2 -top-2 rounded-full bg-black p-1 text-white"
                  @click="removeGalleryImage(image.id)"
                >
                  <X class="h-4 w-4" />
                </button>
              </div>

              <button
                type="button"
                class="flex h-24 w-24 items-center justify-center rounded bg-gray-200 text-black"
                @click="triggerGalleryUpload"
              >
                <Plus class="h-8 w-8" />
              </button>
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
        </div>

        <div class="flex justify-end gap-4 pt-4">
          <button
            type="button"
            class="rounded-lg border border-orange-400 px-6 py-3 text-sm font-medium text-orange-500 transition-opacity hover:opacity-90"
            @click="resetProfileChanges"
          >
            Cancel
          </button>
          <button
            type="button"
            class="rounded-lg bg-orange-400 px-8 py-3 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-70"
            :disabled="profileSaving"
            @click="saveProfile"
          >
            {{ profileSaving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </div>
      </section>
    </div>
  </PageLayout>
</template>
