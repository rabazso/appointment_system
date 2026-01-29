<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'


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

const getStatusText = (status) => {
  switch (status) {
    case 'upcoming': return 'Upcoming'
    case 'completed': return 'Completed'
    case 'cancelled': return 'Cancelled'
    default: return status
  }
}


const cancelAppointment = (id) => {
  const confirmed = confirm("Are you sure you want to cancel this appointment?")
  if (!confirmed) return

  const appointment = appointments.value.find(apt => apt.id === id)
  if (appointment) {
    appointment.status = 'cancelled'
  }
}

onMounted(async () => {
  setTimeout(() => {
    appointments.value = [
      {
        id: 1,
        service: { name: 'Regular Haircut' },
        barber: { name: 'Crispy Chris' },
        date: '2025-02-15',
        time: '14:30',
        price: 30,
        status: 'upcoming' 
      },
      {
        id: 2,
        service: { name: 'Regular Haircut' },
        barber: { name: 'Haircut Harry' },
        date: '2025-01-10',
        time: '10:00',
        price: 30,
        status: 'completed'
      },
      {
        id: 3,
        service: { name: 'Fullbox' },
        barber: { name: 'Loud Lucy' },
        date: '2025-03-01',
        time: '16:00',
        price: 60,
        status: 'upcoming'
      },
      {
        id: 4,
        service: { name: 'Regular Haircut' },
        barber: { name: 'Bouncy Bella' },
        date: '2025-03-01',
        time: '16:00',
        price: 30,
        status: 'upcoming'
      },
      {
        id: 5,
        service: { name: 'Perfect Haircut' },
        barber: { name: 'Blowout Ben' },
        date: '2025-03-01',
        time: '16:00',
        price: 45,
        status: 'upcoming'
      }
    ]
    loading.value = false
  }, 800)
})

const handleNewBooking = () => {
  router.push('/booking')
}
</script>
<template>
    <div class="min-h-100vh bg-background p-4 md:p-8">
    <div class="max-w-6xl mx-auto space-y-8">
      
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-6">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-foreground">Your Appointments</h1>
        </div>
        <Button @click="handleNewBooking" class="gap-2 shadow-sm">
          Book New Appointment
        </Button>
      </div>

      <div v-if="loading" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div class="h-64 rounded-xl bg-muted/50 animate-pulse border-2 border-dashed border-muted" v-for="i in 3" :key="i"></div>
      </div>

      <div v-else-if="appointments.length === 0" class="flex flex-col items-center justify-center py-16 border-2 border-dashed rounded-xl bg-accent/5">
        <div class="p-4 rounded-full bg-muted mb-4">
           <Scissors class="w-8 h-8 text-muted-foreground" />
        </div>
        <h3 class="font-semibold text-xl mb-2">No appointments yet</h3>
        <Button @click="handleNewBooking">
          Book Now
        </Button>
      </div>

      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        
        <Card v-for="apt in appointments" :key="apt.id" class="border-accent/30 bg-accent/10 shadow-sm transition-all hover:border-accent/50 flex flex-col">
          
          <CardHeader class="pb-3 pt-5">
            <div class="flex justify-between items-start gap-2">
              <CardTitle class="text-xl font-bold leading-tight">{{ apt.service.name }}</CardTitle>
              <span :class="`px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider border ${getStatusColor(apt.status)}`">
                {{ getStatusText(apt.status) }}
              </span>
            </div>
          </CardHeader>

          <CardContent class="space-y-3 pb-6 flex-1 flex flex-col">
            
            <div class="flex justify-between items-center border-b border-accent/20 pb-2 last:border-0 last:pb-0">
              <p class="text-muted-foreground text-sm">Barber</p>
              <p class="font-semibold text-foreground text-sm">{{ apt.barber.name }}</p>
            </div>

            <div class="flex justify-between items-center border-b border-accent/20 pb-2 last:border-0 last:pb-0">
              <p class="text-muted-foreground text-sm">Date</p>
              <p class="font-semibold text-foreground text-sm">{{ apt.date }}</p>
            </div>

            <div class="flex justify-between items-center border-b border-accent/20 pb-2 last:border-0 last:pb-0">
              <p class="text-muted-foreground text-sm">Time</p>
              <p class="font-semibold text-foreground text-sm">{{ apt.time }}</p>
            </div>

             <div class="flex justify-between items-center pt-2">
              <p class="text-muted-foreground text-sm font-medium">Price</p>
              <p class="font-bold text-lg text-primary">${{ apt.price }}</p>
            </div>

            <div class="flex-1"></div>

            <div v-if="apt.status === 'upcoming'" class="pt-4 mt-2 border-t border-accent/20">
              <Button 
                variant="destructive" 
                class="w-full gap-2 opacity-90 hover:opacity-100 transition-opacity" 
                size="sm"
                @click="cancelAppointment(apt.id)"
              >
                Cancel Appointment
              </Button>
            </div>

          </CardContent>
        </Card>

      </div>

    </div>
    </div>
</template>