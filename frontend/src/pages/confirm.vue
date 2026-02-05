<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { confirmAppointment } from '@/api/index'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'

const route = useRoute()
const router = useRouter()

const status = ref('loading')
const errorMessage = ref('')

onMounted(async () => {
  const appointmentId = route.params.appointmentId
  const expires = route.query.expires
  const signature = route.query.signature

  if (!appointmentId || !expires || !signature) {
    status.value = 'error'
    errorMessage.value = 'Missing confirmation data. Please check the email link.'
    return
  }

  try {
    const response = await confirmAppointment(appointmentId, expires, signature)
    const summary = response?.data?.summary

    if (!summary) {
      throw new Error('Missing summary data')
    }

    router.replace({
      path: '/summary',
      state: { booking: summary }
    })
  } catch (err) {
    status.value = 'error'
    errorMessage.value = 'We could not confirm your booking. The link may be invalid or expired.'
  }
})

const goHome = () => router.push('/')
</script>

<template>
  <div class="min-h-screen w-full flex items-center justify-center bg-background p-4">
    <Card class="w-full max-w-md shadow-lg border-2 border-accent/20">
      <CardHeader class="text-center">
        <CardTitle>Booking Confirmation</CardTitle>
        <CardDescription v-if="status === 'loading'">Confirming your booking...</CardDescription>
        <CardDescription v-else>There was a problem confirming your booking.</CardDescription>
      </CardHeader>
      <CardContent class="space-y-4 text-center">
        <p v-if="status === 'loading'" class="text-muted-foreground">Please wait a moment.</p>
        <p v-else class="text-muted-foreground">{{ errorMessage }}</p>
        <Button v-if="status === 'error'" @click="goHome" class="w-full">Go Home</Button>
      </CardContent>
    </Card>
  </div>
</template>
