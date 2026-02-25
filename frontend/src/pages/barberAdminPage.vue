<script setup>
import { onMounted, ref } from 'vue'
import AppointmentScheduler from '@/components/admin/AppointmentSchedule.vue'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'
import BarberHeader from '@/components/layout/BarberHeader.vue'
import { cancelBarberAppointment, getBarberAppointments, getBarberReviews } from '@/api'

const currentTab = ref('appointments')
const loading = ref(true)
const errorMessage = ref('')

const appointments = ref([])
const reviews = ref([])

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

const onCancelAppointment = async (appointmentId) => {
    try {
        await cancelBarberAppointment(appointmentId)
        appointments.value = appointments.value.map((appointment) =>
            appointment.id === appointmentId
                ? { ...appointment, status: 'cancelled' }
                : appointment
        )
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Failed to cancel appointment.'
    }
}

onMounted(loadData)
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
                        <AppointmentScheduler :appointments="appointments" @cancel-appointment="onCancelAppointment" />
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

            <div v-else-if="currentTab === 'profile'" class="space-y-8">
                <h1 class="text-3xl font-bold">My Profile</h1>
                <div class="text-gray-400">
                    A profil szerkesztése funkció hamarosan érkezik.
                </div>
            </div>
        </main>
    </div>
</template>
