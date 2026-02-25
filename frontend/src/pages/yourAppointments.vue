<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Footer from '@components/layout/Footer.vue'
import BaseHeader from '@components/layout/BaseHeader.vue'
import YourAppointmentsLayout from '@components/layout/YourAppointmentsLayout.vue'

const router = useRouter()
const appointments = ref([])
const loading = ref(true)

const getStatusColor = (status) => {
  switch (status) {
    case 'upcoming': return 'bg-blue-500/10 text-blue-600 border-blue-500/20'
    case 'completed': return 'bg-green-500/10 text-green-600 border-green-500/20'
    case 'cancelled': return 'bg-red-500/10 text-red-600 border-red-500/20'
    default: return 'bg-gray-500/10 text-gray-600 border-gray-500/20'
  }
}
</script>

<template>
<div class="flex flex-col min-h-screen">
  <BaseHeader></BaseHeader>
  <main class="flex-grow">
    <YourAppointmentsLayout></YourAppointmentsLayout>
  </main>
  <Footer></Footer>
</div>
</template>