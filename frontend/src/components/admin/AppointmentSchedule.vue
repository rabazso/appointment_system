<script setup>
import { ref } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Calendar } from '@/components/ui/calendar'
import { X } from 'lucide-vue-next'

const props = defineProps({
  appointments: { type: Array, required: true }
})

const activeTab = ref('appointments')
const selectedDate = ref(new Date())

const getStatusColor = (status) => {
  switch (status) {
    case 'confirmed': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
    case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    case 'completed': return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status) => {
    const labels = {
        confirmed: 'Confirmed',
        pending: 'Waiting for confirmation',
        cancelled: 'Canceled',
        completed: 'Done'
    }
    return labels[status] || status
}
</script>

<template>
  <Card class="border-none shadow-none md:border md:shadow-sm">
    <div class="p-4 border-b">
      <div class="flex space-x-4">
        <button 
          @click="activeTab = 'appointments'" 
          :class="['px-4 py-2 text-sm font-medium rounded-md transition-colors', activeTab === 'appointments' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']">
          Dates
        </button>
      </div>
    </div>

    <CardContent class="p-6">
      <div v-if="activeTab === 'appointments'" class="space-y-4">
        <div v-for="apt in appointments" :key="apt.id" class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
          <div class="flex items-center gap-4">
            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
              {{ apt.client.charAt(0) }}
            </div>
            <div>
              <h3 class="font-semibold">{{ apt.client }}</h3>
              <p class="text-sm text-muted-foreground">{{ apt.service }} • {{ apt.time }}</p>
            </div>
          </div>
          
          <div class="flex items-center gap-4">
            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(apt.status)]">
              {{ getStatusText(apt.status) }}
            </span>
            <Button 
                v-if="['pending', 'confirmed'].includes(apt.status)"
                size="icon" 
                variant="ghost" 
                class="h-8 w-8 text-red-500 hover:text-red-700 hover:bg-red-50" 
                title="Foglalás lemondása"
                @click="emit('cancel-appointment', apt.id)"
            >
                <X class="h-4 w-4" />
            </Button>
          </div>
        </div>

        <div v-if="appointments.length === 0" class="text-center py-10 text-muted-foreground">
            No appointments yet.
        </div>
      </div>

      <div v-else-if="activeTab === 'calendar'" class="flex justify-center">
        <Calendar v-model="selectedDate" class="rounded-md border shadow" />
      </div>
    </CardContent>
  </Card>
</template>