<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'

const router = useRouter()
const booking = ref(null)

onMounted(() => {
  const stateData = history.state.booking
  if (stateData) {
    booking.value = stateData
  }
})

const goHome = () => router.push('/')
</script>

<template>
  <div class="min-h-screen w-full flex items-center justify-center bg-background p-4">
    <Card class="w-full max-w-md shadow-lg border-2 border-accent/20">
      <CardHeader class="text-center">
        <CardTitle>Check Your Email</CardTitle>
        <CardDescription>We sent a confirmation link. Please confirm your booking.</CardDescription>
      </CardHeader>
      <CardContent class="space-y-4">
        <div v-if="booking" class="rounded-lg border border-accent/30 bg-accent/10 p-4 space-y-2">
          <div class="flex justify-between items-center">
            <p class="text-muted-foreground">Service:</p>
            <p class="font-semibold text-foreground">{{ booking.serviceName }}</p>
          </div>
          <div class="flex justify-between items-center">
            <p class="text-muted-foreground">Barber:</p>
            <p class="font-semibold text-foreground">{{ booking.barberName }}</p>
          </div>
          <div class="flex justify-between items-center">
            <p class="text-muted-foreground">Date:</p>
            <p class="font-semibold text-foreground">{{ booking.date }}</p>
          </div>
          <div class="flex justify-between items-center">
            <p class="text-muted-foreground">Time:</p>
            <p class="font-semibold text-foreground">{{ booking.time }}</p>
          </div>
        </div>
        <p class="text-sm text-muted-foreground text-center">Once you confirm, we will show your summary.</p>
      </CardContent>
      <CardFooter>
        <Button @click="goHome" class="w-full">Back to Home</Button>
      </CardFooter>
    </Card>
  </div>
</template>
