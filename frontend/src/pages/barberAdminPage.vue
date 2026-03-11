<script setup>
import { computed, onMounted, ref } from 'vue'
import { Pencil, Plus, X } from 'lucide-vue-next'
import AppointmentScheduler from '@/components/admin/AppointmentSchedule.vue'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'
import BarberHeader from '@/components/layout/BarberHeader.vue'
import {
    cancelBarberAppointment,
    completeBarberAppointment,
    deleteBarberGalleryImage,
    getBarberAppointments,
    getBarberProfile,
    getBarberReviews,
    updateBarberProfile,
    uploadBarberGalleryImage
} from '@/api'

const currentTab = ref('appointments')
const loading = ref(true)
const errorMessage = ref('')
const cancelModalOpen = ref(false)
const cancellingAppointment = ref(false)
const pendingCancelAppointmentId = ref(null)
const cancellationReason = ref('')
const cancelModalError = ref('')

const MIN_CANCELLATION_REASON_LENGTH = 30
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
    gallery: []
})

const loadProfile = async () => {
    profileLoading.value = true
    profileError.value = ''
    try {
        const response = await getBarberProfile()
        profile.value = {
            name: response.data?.name || '',
            description: response.data?.description || '',
            photo_url: response.data?.photo_url || '',
            gallery: response.data?.gallery || []
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
            getBarberReviews()
        ])
        appointments.value = appointmentsResponse.data || []
        reviews.value = reviewsResponse.data || []
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Failed to load admin data.'
    } finally {
        loading.value = false
    }
}

const appointmentToCancel = computed(() =>
    appointments.value.find((appointment) => appointment.id === pendingCancelAppointmentId.value) || null
)

const isPastAppointmentCancellation = computed(() => {
    const startDateTime = appointmentToCancel.value?.start_datetime
    if (!startDateTime) return false

    const timestamp = new Date(startDateTime).getTime()
    return Number.isFinite(timestamp) && timestamp < Date.now()
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

const openCancelModal = (appointmentId) => {
    pendingCancelAppointmentId.value = appointmentId
    cancellationReason.value = ''
    cancelModalError.value = ''
    cancelModalOpen.value = true
}

const closeCancelModal = () => {
    cancelModalOpen.value = false
    pendingCancelAppointmentId.value = null
    cancellationReason.value = ''
    cancelModalError.value = ''
}

const onCancelAppointment = (appointmentId) => {
    openCancelModal(appointmentId)
}

const confirmCancelAppointment = async () => {
    if (!pendingCancelAppointmentId.value || !isCancellationReasonValid.value || cancellingAppointment.value) {
        return
    }

    cancellingAppointment.value = true
    cancelModalError.value = ''
    errorMessage.value = ''

    try {
        await cancelBarberAppointment(pendingCancelAppointmentId.value, {
            cancellation_reason: trimmedCancellationReason.value
        })

        appointments.value = appointments.value.map((appointment) =>
            appointment.id === pendingCancelAppointmentId.value
                ? {
                    ...appointment,
                    status: 'cancelled',
                    cancellation_reason: trimmedCancellationReason.value || null
                }
                : appointment
        )

        closeCancelModal()
    } catch (error) {
        cancelModalError.value =
            error.response?.data?.errors?.cancellation_reason?.[0]
            || error.response?.data?.message
            || 'Failed to cancel appointment.'
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
                : appointment
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
            gallery: response.data?.gallery || []
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
    <div class="flex flex-col min-h-screen">
        <BarberHeader @navigate="(id) => currentTab = id" />

        <main class="flex-1 bg-gray-50 dark:bg-gray-900 p-4 md:p-8">
            <div v-if="currentTab === 'appointments'" class="space-y-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Appointments</h1>
                        <p class="text-gray-500 dark:text-gray-400">Your upcoming and past bookings.</p>
                    </div>
                </div>

                <p v-if="errorMessage" class="rounded-md bg-red-100 px-4 py-2 text-sm text-red-700">
                    {{ errorMessage }}
                </p>

                <p v-if="loading" class="text-sm text-gray-500 dark:text-gray-400">
                    Loading admin data...
                </p>

                <div class="grid gap-4 md:grid-cols-7 lg:grid-cols-7">
                    <div class="col-span-4 md:col-span-4 lg:col-span-5">
                        <AppointmentScheduler
                            :appointments="appointments"
                            @cancel-appointment="onCancelAppointment"
                            @complete-appointment="onCompleteAppointment"
                        />
                    </div>
                    <div class="col-span-4 md:col-span-3 lg:col-span-2">
                        <AdminSidebar :reviews="reviews" />
                    </div>
                </div>
            </div>

            <div v-else-if="currentTab === 'time-off'" class="max-w-2xl mx-auto space-y-8 text-center pt-12">
                <h1 class="text-3xl font-bold">Time Off Management</h1>
                <p class="text-gray-500">Choose the type of your time off</p>
                <div class="flex justify-center gap-4">
                    <button class="bg-primary text-white px-6 py-3 rounded-md hover:opacity-90 transition-opacity">Request Vacation</button>
                    <button class="bg-destructive text-white px-6 py-3 rounded-md hover:opacity-90 transition-opacity">Report Sick</button>
                </div>
            </div>

            <div v-else-if="currentTab === 'profile'" class="mx-auto w-full max-w-3xl space-y-6 py-4">
                <h1 class="text-center text-4xl font-bold text-gray-900 dark:text-white">Your profile</h1>

                <p v-if="profileError" class="rounded-md bg-red-100 px-4 py-2 text-sm text-red-700">
                    {{ profileError }}
                </p>
                <p v-if="profileSuccess" class="rounded-md bg-green-100 px-4 py-2 text-sm text-green-700">
                    {{ profileSuccess }}
                </p>
                <p v-if="profileLoading" class="text-sm text-gray-500 dark:text-gray-400">
                    Loading profile...
                </p>

                <div v-else class="space-y-6">
                    <div class="flex flex-col items-center gap-3">
                        <button
                            type="button"
                            @click="triggerProfileImageUpload"
                            class="h-32 w-32 overflow-hidden rounded-full bg-gray-300"
                        >
                            <img
                                v-if="profile.photo_url"
                                :src="profile.photo_url"
                                alt="Profile image"
                                class="h-full w-full object-cover"
                            >
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 text-base" @click="triggerProfileImageUpload">
                            Edit
                            <Pencil class="h-4 w-4" />
                        </button>
                        <input ref="profileImageInput" type="file" accept="image/*" class="hidden" @change="onProfileImageSelected">
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xl font-medium">Name</label>
                        <div class="relative">
                            <input
                                v-model="profile.name"
                                type="text"
                                class="w-full rounded-xl border border-gray-400 bg-white px-4 py-3 pr-12 text-lg"
                                placeholder="Your name"
                            >
                            <Pencil class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-700" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xl font-medium">Description</label>
                        <div class="relative">
                            <textarea
                                v-model="profile.description"
                                rows="3"
                                class="w-full rounded-xl border border-gray-400 bg-white px-4 py-3 pr-12 text-lg"
                                placeholder="Write a short bio about your work"
                            ></textarea>
                            <Pencil class="pointer-events-none absolute right-4 top-4 h-5 w-5 text-gray-700" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xl font-medium">Gallery</label>
                        <div class="rounded-xl border border-gray-400 bg-white p-3">
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
                            class="rounded-full border-2 border-orange-400 px-8 py-3 text-lg font-medium text-orange-500 transition-opacity hover:opacity-90"
                            @click="resetProfileChanges"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-full bg-orange-400 px-10 py-3 text-lg font-medium text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="profileSaving"
                            @click="saveProfile"
                        >
                            {{ profileSaving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="cancelModalOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
                @click.self="closeCancelModal"
            >
                <div class="w-full max-w-lg rounded-xl border bg-background shadow-2xl">
                    <div class="flex items-start justify-between gap-4 border-b px-6 py-4">
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">Cancel appointment?</h2>
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
                            <p class="font-medium text-foreground">{{ appointmentToCancel?.service || 'Service' }}</p>
                            <p class="mt-1 text-muted-foreground">
                                Client: {{ appointmentToCancel?.client || 'Guest' }} | Time: {{ appointmentToCancel?.time || '--:--' }}
                            </p>
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
                            :disabled="cancellingAppointment || !isCancellationReasonValid"
                            @click="confirmCancelAppointment"
                        >
                            {{ cancellingAppointment ? 'Cancelling...' : 'Cancel Appointment' }}
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
