<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import AppointmentScheduler from '@/components/admin/AppointmentSchedule.vue'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'
import BarberHeader from '@/components/layout/BarberHeader.vue'

const currentTab = ref('appointments')

const appointments = ref([
    { id: 1, client: 'Eros Pista', service: 'Regular haircut', time: '10:00', status: 'pending' },
    { id: 2, client: 'Vicc Elek', service: 'Perfect haircut', time: '11:30', status: 'confirmed' },
    { id: 3, client: 'Edes Anna', service: 'Fullbox', time: '13:00', status: 'completed' },
])

const reviews = ref([
    { id: 1, client: 'Kiss Odon', rating: 5, text: 'Nice work!' },
    { id: 2, client: 'Kovacs Janos', rating: 4, text: 'I had to wait a bit' },
])
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <BarberHeader @navigate="(id) => currentTab = id" />

        <main class="flex-1 bg-gray-50 dark:bg-gray-900 p-4 md:p-8">
            <div v-if="currentTab === 'appointments'" class="space-y-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Appointments</h1>
                        <p class="text-gray-500 dark:text-gray-400">Hello X Y, itt vannak a mai foglalásaid.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-7 lg:grid-cols-7">
                    <div class="col-span-4 md:col-span-4 lg:col-span-5">
                        <AppointmentScheduler :appointments="appointments" />
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