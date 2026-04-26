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
  <div class="min-h-screen bg-[#f5f5f5] px-4 py-10">
    <div class="mx-auto flex min-h-[70vh] w-full max-w-3xl items-center justify-center">
      <Card class="w-full max-w-2xl border border-[#f0c99f] bg-white shadow-xl shadow-black/5">
        <CardHeader class="px-6 pt-8 text-center md:px-10">
          <CardTitle class="text-3xl font-bold text-black">Check Your Email</CardTitle>
          <CardDescription class="mx-auto max-w-lg text-base leading-8 text-slate-600">
            We sent a confirmation link. Please confirm your booking.
          </CardDescription>
      </CardHeader>
      <CardContent class="space-y-5 px-6 md:px-10">
        <div v-if="booking" class="rounded-2xl border border-[#f0c99f] bg-[#fbefe1] p-5">
          <div class="flex min-h-12 items-center justify-between py-1">
            <p class="text-lg text-slate-600">Service:</p>
            <p class="text-lg font-bold text-black">{{ booking.serviceName }}</p>
          </div>
          <div class="flex min-h-12 items-center justify-between border-t border-black/10 py-1">
            <p class="text-lg text-slate-600">Barber:</p>
            <p class="text-lg font-bold text-black">{{ booking.barberName }}</p>
          </div>
          <div class="flex min-h-12 items-center justify-between border-t border-black/10 py-1">
            <p class="text-lg text-slate-600">Date:</p>
            <p class="text-lg font-bold text-black">{{ booking.date }}</p>
          </div>
          <div class="flex min-h-12 items-center justify-between border-t border-black/10 py-1">
            <p class="text-lg text-slate-600">Time:</p>
            <p class="text-lg font-bold text-black">{{ booking.time }}</p>
          </div>
          <div class="flex min-h-12 items-center justify-between border-t border-black/10 py-1">
            <p class="text-lg text-slate-600">Total duration:</p>
            <p class="text-lg font-bold text-black">
              {{ booking.duration !== null && booking.duration !== undefined ? `${booking.duration} min` : '-' }}
            </p>
          </div>
        </div>
        <p class="text-center text-base text-slate-600">Once you confirm, we will show your summary.</p>
      </CardContent>
      <CardFooter class="px-6 pb-8 md:px-10">
        <Button @click="goHome" class="h-12 w-full rounded-xl bg-[#f69436] text-base font-semibold text-black hover:bg-[#ec8a2f]">Back to Home</Button>
      </CardFooter>
      </Card>
    </div>
  </div>
</template>
