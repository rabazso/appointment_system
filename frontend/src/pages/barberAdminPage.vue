<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'

import AppointmentScheduler from '@/components/admin/AppointmentSchedule.vue'
import AdminSidebar from '@/components/admin/AdminSidebar.vue'
import BaseHeader from '@components/layout/BaseHeader.vue'

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
    <BaseHeader></BaseHeader>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 md:p-8 space-y-8">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-gray-500 dark:text-gray-400">Hello X Y!</p>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="outline" class="hidden md:flex items-center gap-2">
               <span>Today</span>
            </Button>
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
</template>