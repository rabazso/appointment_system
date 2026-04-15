<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Pencil, Plus, X } from 'lucide-vue-next'
import Header from '@/components/admin/Header.vue'
import ReviewSidebar from '@/components/admin/AdminSidebar.vue'
import EmployeeAppointmentsBoard from '@/components/employee/EmployeeAppointmentsBoard.vue'
import EmployeeSidebar from '@/components/employee/EmployeeSidebar.vue'
import {
  cancelBarberAppointment,
  completeBarberAppointment,
  deleteBarberGalleryImage,
  getBarberAppointments,
  getBarberProfile,
  getBarberReviews,
  updateBarberProfile,
  uploadBarberGalleryImage,
} from '@/api'

const sidebarOpen = ref(false)
const currentSection = ref('appointments')
const loading = ref(true)
const errorMessage = ref('')
const cancelModalOpen = ref(false)
const cancellingAppointment = ref(false)
const selectedAppointmentIds = ref([])
const pendingCancelAppointmentIds = ref([])
const cancellationReason = ref('')
const cancelModalError = ref('')

const CANCELLABLE_STATUSES = ['pending', 'confirmed']
const MIN_CANCELLATION_REASON_LENGTH = 10
const MAX_CANCELLATION_REASON_LENGTH = 500

const appointments = ref([])
const reviews = ref([])
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

const sectionMeta = computed(() => ({
  appointments: {
    title: 'Your Appointments',
    description: 'Manage your craft and curate your day\'s gallery.',
  },
  profile: {
    title: 'Profile',
    description: 'Update your public profile, photo, and gallery.',
  },
  'time-off': {
    title: 'Time Off',
    description: 'Manage availability and upcoming time-off requests.',
  },
}[currentSection.value]))

const loadProfile = async () => {
  profileLoading.value = true
  profileError.value = ''
  try {
    const response = await getBarberProfile()
    profile.value = {
      name: response.data?.name || '',
      description: response.data?.description || '',
      photo_url: response.data?.photo_url || '',
      gallery: response.data?.gallery || [],
    }
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to load profile data.'
  } finally {
    profileLoading.value = false
  }
}

const loadData = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const [appointmentsResponse, reviewsResponse] = await Promise.all([
      getBarberAppointments(),
      getBarberReviews(),
    ])
    appointments.value = appointmentsResponse.data || []
    reviews.value = reviewsResponse.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to load dashboard data.'
  } finally {
    loading.value = false
  }
}

const cancellableAppointmentIdSet = computed(() => new Set(
  appointments.value
    .filter((appointment) => CANCELLABLE_STATUSES.includes(appointment.status))
    .map((appointment) => appointment.id),
))

const selectedCancellableAppointmentIds = computed(() =>
  selectedAppointmentIds.value.filter((appointmentId) => cancellableAppointmentIdSet.value.has(appointmentId)),
)

const appointmentsToCancel = computed(() => {
  if (!pendingCancelAppointmentIds.value.length) {
    return []
  }

  const appointmentMap = new Map(
    appointments.value.map((appointment) => [appointment.id, appointment]),
  )

  return pendingCancelAppointmentIds.value
    .map((appointmentId) => appointmentMap.get(appointmentId))
    .filter(Boolean)
})

const cancellationSelectionCount = computed(() => appointmentsToCancel.value.length)

watch(cancellableAppointmentIdSet, (allowedIdSet) => {
  selectedAppointmentIds.value = selectedAppointmentIds.value.filter((appointmentId) =>
    allowedIdSet.has(appointmentId),
  )
  pendingCancelAppointmentIds.value = pendingCancelAppointmentIds.value.filter((appointmentId) =>
    allowedIdSet.has(appointmentId),
  )
}, { immediate: true })

const getAppointmentTimestamp = (appointment) => {
  const rawValue = appointment?.start_datetime
  if (!rawValue) return Number.NaN

  return new Date(rawValue).getTime()
}

const isPastAppointmentCancellation = computed(() => {
  if (!appointmentsToCancel.value.length) {
    return false
  }

  return appointmentsToCancel.value.every((appointment) => {
    const timestamp = getAppointmentTimestamp(appointment)
    return Number.isFinite(timestamp) && timestamp < Date.now()
  })
})

const isCancellationReasonRequired = computed(() => !isPastAppointmentCancellation.value)
const trimmedCancellationReason = computed(() => cancellationReason.value.trim())
const cancellationReasonLength = computed(() => trimmedCancellationReason.value.length)

const isCancellationReasonValid = computed(() => {
  if (!isCancellationReasonRequired.value) {
    return cancellationReasonLength.value <= MAX_CANCELLATION_REASON_LENGTH
  }

  return cancellationReasonLength.value >= MIN_CANCELLATION_REASON_LENGTH
    && cancellationReasonLength.value <= MAX_CANCELLATION_REASON_LENGTH
})

const openCancelModal = (appointmentIds) => {
  const normalizedIds = Array.from(new Set(appointmentIds))
    .filter((appointmentId) => cancellableAppointmentIdSet.value.has(appointmentId))

  if (!normalizedIds.length) {
    return
  }

  pendingCancelAppointmentIds.value = normalizedIds
  cancellationReason.value = ''
  cancelModalError.value = ''
  cancelModalOpen.value = true
}

const closeCancelModal = () => {
  cancelModalOpen.value = false
  pendingCancelAppointmentIds.value = []
  cancellationReason.value = ''
  cancelModalError.value = ''
}

const onCancelAppointment = (appointmentId) => {
  openCancelModal([appointmentId])
}

const onToggleAppointmentSelection = (appointmentId) => {
  if (!cancellableAppointmentIdSet.value.has(appointmentId)) {
    return
  }

  const exists = selectedAppointmentIds.value.includes(appointmentId)
  selectedAppointmentIds.value = exists
    ? selectedAppointmentIds.value.filter((id) => id !== appointmentId)
    : [...selectedAppointmentIds.value, appointmentId]
}

const clearAppointmentSelection = () => {
  selectedAppointmentIds.value = []
}

const onCancelSelectedAppointments = () => {
  openCancelModal(selectedCancellableAppointmentIds.value)
}

const extractCancellationError = (error) =>
  error.response?.data?.errors?.cancellation_reason?.[0]
  || error.response?.data?.message
  || 'Failed to cancel appointment.'

const confirmCancelAppointment = async () => {
  const appointmentIds = [...pendingCancelAppointmentIds.value]
  if (!appointmentIds.length || !isCancellationReasonValid.value || cancellingAppointment.value) {
    return
  }

  cancellingAppointment.value = true
  cancelModalError.value = ''
  errorMessage.value = ''

  try {
    const reason = trimmedCancellationReason.value
    const results = await Promise.allSettled(
      appointmentIds.map((appointmentId) =>
        cancelBarberAppointment(appointmentId, { cancellation_reason: reason })),
    )

    const cancelledIds = []
    let firstFailureMessage = ''

    results.forEach((result, index) => {
      if (result.status === 'fulfilled') {
        cancelledIds.push(appointmentIds[index])
        return
      }

      if (!firstFailureMessage) {
        firstFailureMessage = extractCancellationError(result.reason)
      }
    })

    if (cancelledIds.length) {
      const cancelledIdSet = new Set(cancelledIds)

      appointments.value = appointments.value.map((appointment) =>
        cancelledIdSet.has(appointment.id)
          ? {
            ...appointment,
            status: 'cancelled',
            cancellation_reason: reason || null,
          }
          : appointment,
      )

      selectedAppointmentIds.value = selectedAppointmentIds.value.filter(
        (appointmentId) => !cancelledIdSet.has(appointmentId),
      )
    }

    const failedCount = appointmentIds.length - cancelledIds.length

    if (failedCount === 0) {
      closeCancelModal()
      return
    }

    if (cancelledIds.length) {
      const cancelledIdSet = new Set(cancelledIds)
      pendingCancelAppointmentIds.value = appointmentIds.filter(
        (appointmentId) => !cancelledIdSet.has(appointmentId),
      )
    }

    cancelModalError.value = cancelledIds.length
      ? `Cancelled ${cancelledIds.length} appointment${cancelledIds.length === 1 ? '' : 's'}. ${failedCount} failed. ${firstFailureMessage}`
      : firstFailureMessage
  } catch (error) {
    cancelModalError.value = extractCancellationError(error)
  } finally {
    cancellingAppointment.value = false
  }
}

const onCompleteAppointment = async (appointmentId) => {
  try {
    await completeBarberAppointment(appointmentId)
    appointments.value = appointments.value.map((appointment) =>
      appointment.id === appointmentId
        ? { ...appointment, status: 'completed' }
        : appointment,
    )
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to complete appointment.'
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
      const response = await uploadBarberGalleryImage(formData)
      profile.value.gallery.unshift(response.data)
    }
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to upload one or more gallery images.'
  } finally {
    event.target.value = ''
  }
}

const removeGalleryImage = async (imageId) => {
  profileError.value = ''
  try {
    await deleteBarberGalleryImage(imageId)
    profile.value.gallery = profile.value.gallery.filter((item) => item.id !== imageId)
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to delete image.'
  }
}

const saveProfile = async () => {
  profileSaving.value = true
  profileError.value = ''
  profileSuccess.value = ''
  try {
    const formData = new FormData()
    formData.append('name', profile.value.name || '')
    formData.append('description', profile.value.description || '')
    if (avatarFile.value) {
      formData.append('photo', avatarFile.value)
    }

    const response = await updateBarberProfile(formData)
    profile.value = {
      name: response.data?.name || '',
      description: response.data?.description || '',
      photo_url: response.data?.photo_url || '',
      gallery: response.data?.gallery || [],
    }
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

onMounted(async () => {
  await Promise.all([loadData(), loadProfile()])
})
</script>

<template>
  <div class="flex h-dvh overflow-hidden bg-slate-100">
    <EmployeeSidebar
      :is-open="sidebarOpen"
      :current-section="currentSection"
      @close="sidebarOpen = false"
      @select-section="currentSection = $event"
    />

    <main class="flex min-h-0 flex-1 flex-col overflow-y-auto p-4 md:p-8">
      <Header
        :title="sectionMeta.title"
        :description="sectionMeta.description"
        :show-action="false"
        @menu-click="sidebarOpen = true"
      />

      <div v-if="currentSection === 'appointments'" class="space-y-6">
        <p v-if="errorMessage" class="rounded-md bg-red-100 px-4 py-2 text-sm text-red-700">
          {{ errorMessage }}
        </p>

        <p v-if="loading" class="text-sm text-gray-500">
          Loading dashboard data...
        </p>

        <div class="grid gap-4 md:grid-cols-7 lg:grid-cols-7">
          <div class="col-span-4 md:col-span-4 lg:col-span-5">
            <EmployeeAppointmentsBoard
              :appointments="appointments"
              :selected-appointment-ids="selectedCancellableAppointmentIds"
              @cancel-appointment="onCancelAppointment"
              @complete-appointment="onCompleteAppointment"
              @toggle-appointment-selection="onToggleAppointmentSelection"
              @clear-appointment-selection="clearAppointmentSelection"
              @cancel-selected-appointments="onCancelSelectedAppointments"
            />
          </div>
          <div class="col-span-4 md:col-span-3 lg:col-span-2">
            <ReviewSidebar :reviews="reviews" />
          </div>
        </div>
      </div>

      <div v-else-if="currentSection === 'time-off'" class="mx-auto w-full max-w-4xl">
        <section class="rounded-2xl bg-white p-6 shadow-sm md:p-8">
          <h2 class="text-2xl font-semibold text-black">Time Off Management</h2>
          <p class="mt-2 text-sm text-gray-500">Choose the type of your time off request.</p>

          <div class="mt-8 flex flex-col gap-4 sm:flex-row">
            <button
              type="button"
              class="rounded-lg bg-primary px-6 py-3 text-sm font-medium text-white transition-opacity hover:opacity-90"
            >
              Request Vacation
            </button>
            <button
              type="button"
              class="rounded-lg bg-destructive px-6 py-3 text-sm font-medium text-white transition-opacity hover:opacity-90"
            >
              Report Sick
            </button>
          </div>
        </section>
      </div>

      <div v-else-if="currentSection === 'profile'" class="mx-auto w-full max-w-4xl">
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
                    <img :src="image.image_url" alt="Work sample" class="h-full w-full object-cover">
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

      <div
        v-if="cancelModalOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
        @click.self="closeCancelModal"
      >
        <div class="w-full max-w-lg rounded-xl border bg-background shadow-2xl">
          <div class="flex items-start justify-between gap-4 border-b px-6 py-4">
            <div>
              <h2 class="text-lg font-semibold text-foreground">
                {{ cancellationSelectionCount > 1 ? 'Cancel appointments?' : 'Cancel appointment?' }}
              </h2>
              <p class="mt-1 text-sm text-muted-foreground">
                <template v-if="isCancellationReasonRequired">
                  Add a clear reason for the client before canceling.
                </template>
                <template v-else>
                  This appointment is in the past, so reason is optional for a no-show.
                </template>
              </p>
            </div>
            <button
              type="button"
              class="text-muted-foreground transition-colors hover:text-foreground"
              :disabled="cancellingAppointment"
              @click="closeCancelModal"
            >
              <X class="h-5 w-5" />
            </button>
          </div>

          <div class="space-y-4 px-6 py-4">
            <div class="rounded-lg border bg-muted/20 p-3 text-sm">
              <template v-if="cancellationSelectionCount === 1">
                <p class="font-medium text-foreground">{{ appointmentsToCancel[0]?.service || 'Service' }}</p>
                <p class="mt-1 text-muted-foreground">
                  Client: {{ appointmentsToCancel[0]?.client || 'Guest' }} | Time: {{ appointmentsToCancel[0]?.time || '--:--' }}
                </p>
              </template>
              <template v-else>
                <p class="font-medium text-foreground">
                  {{ cancellationSelectionCount }} appointments selected
                </p>
                <ul class="mt-2 space-y-1 text-muted-foreground">
                  <li
                    v-for="appointment in appointmentsToCancel.slice(0, 5)"
                    :key="appointment.id"
                    class="truncate"
                  >
                    {{ appointment.client || 'Guest' }} - {{ appointment.service || 'Service' }} - {{ appointment.time || '--:--' }}
                  </li>
                </ul>
                <p v-if="cancellationSelectionCount > 5" class="mt-2 text-xs text-muted-foreground">
                  And {{ cancellationSelectionCount - 5 }} more...
                </p>
              </template>
            </div>

            <div class="space-y-2">
              <label for="barber-cancellation-reason" class="text-sm font-medium text-foreground">
                Cancellation reason
                <span v-if="!isCancellationReasonRequired" class="text-muted-foreground">(optional)</span>
              </label>
              <textarea
                id="barber-cancellation-reason"
                v-model="cancellationReason"
                rows="4"
                :maxlength="MAX_CANCELLATION_REASON_LENGTH"
                :disabled="cancellingAppointment"
                :placeholder="isCancellationReasonRequired
                  ? 'Please explain why this appointment is being cancelled...'
                  : 'Optional note for this no-show cancellation...'"
                class="w-full rounded-lg border border-border bg-transparent px-3 py-2 text-foreground outline-none transition focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/40"
              ></textarea>
              <p
                class="text-xs"
                :class="isCancellationReasonRequired && !isCancellationReasonValid ? 'text-red-600' : 'text-muted-foreground'"
              >
                {{ cancellationReasonLength }} / {{ MAX_CANCELLATION_REASON_LENGTH }} characters
                <template v-if="isCancellationReasonRequired">
                  (minimum {{ MIN_CANCELLATION_REASON_LENGTH }})
                </template>
                <template v-else>
                  (optional for past appointments)
                </template>
              </p>
            </div>

            <p v-if="cancelModalError" class="rounded-md bg-red-100 px-3 py-2 text-sm text-red-700">
              {{ cancelModalError }}
            </p>
          </div>

          <div class="flex justify-end gap-2 border-t px-6 py-4">
            <button
              type="button"
              class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="cancellingAppointment"
              @click="closeCancelModal"
            >
              Keep Appointment
            </button>
            <button
              type="button"
              class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="cancellingAppointment || !isCancellationReasonValid || cancellationSelectionCount === 0"
              @click="confirmCancelAppointment"
            >
              {{
                cancellingAppointment
                  ? 'Cancelling...'
                  : cancellationSelectionCount > 1
                    ? `Cancel ${cancellationSelectionCount} Appointments`
                    : 'Cancel Appointment'
              }}
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
