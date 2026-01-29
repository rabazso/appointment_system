<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Check } from 'lucide-vue-next'

const router = useRouter()
const booking = ref(null)

onMounted(() => {
  const stateData = history.state.booking

  if (!stateData) {
    router.push('/')
    return
  }

  booking.value = stateData
})

const handleGoHome = () => {
  router.push('/')
}
</script>
<template>
    
    <Card v-if="booking" class="w-full max-w-md shadow-lg border-2 border-accent/20">
      
      <CardHeader class="flex flex-col items-center justify-center space-y-4 text-center pb-6">
        <div class="rounded-full bg-green-100 p-4 dark:bg-green-900/30">
          <Check class="w-8 h-8 text-green-600 dark:text-green-400" />
        </div>
        
        <CardTitle class="text-2xl font-bold">
          Your appointment was successful
        </CardTitle>
        <CardDescription>
            Your appointments details
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="rounded-lg border border-accent/30 bg-accent/10 p-6 space-y-3">
          
          <div class="flex justify-between items-center border-b border-accent/20 pb-2 last:border-0 last:pb-0">
            <p class="text-muted-foreground">Service:</p>
            <p class="font-semibold text-foreground">{{ booking.serviceName }}</p>
          </div>

          <div class="flex justify-between items-center border-b border-accent/20 pb-2 last:border-0 last:pb-0">
            <p class="text-muted-foreground">Barber:</p>
            <p class="font-semibold text-foreground">{{ booking.barberName }}</p>
          </div>

          <div class="flex justify-between items-center border-b border-accent/20 pb-2 last:border-0 last:pb-0">
            <p class="text-muted-foreground">Date:</p>
            <p class="font-semibold text-foreground">{{ booking.date }}</p>
          </div>

          <div class="flex justify-between items-center border-b border-accent/20 pb-2 last:border-0 last:pb-0">
            <p class="text-muted-foreground">Time:</p>
            <p class="font-semibold text-foreground">{{ booking.time }}</p>
          </div>

          <div class="flex justify-between items-center pt-2">
            <p class="text-muted-foreground font-medium">Total:</p>
            <p class="font-bold text-lg text-primary">${{ booking.price }}</p>
          </div>

        </div>
      </CardContent>

      <CardFooter class="flex justify-center pt-4 pb-8">
        <Button @click="handleGoHome" class="w-full h-12 text-lg font-semibold" size="lg">
          Continue 
        </Button>
      </CardFooter>

    </Card>

</template>