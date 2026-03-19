<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { AlertCircle, Mail, MailCheck } from 'lucide-vue-next'
import { verifyEmailAddress } from '@/api/index'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'

const REDIRECT_DELAY_MS = 3000

const route = useRoute()
const router = useRouter()

const status = ref('loading')
const message = ref('We are checking your verification link now.')
let redirectTimeoutId = null

const stateConfig = computed(() => {
  switch (status.value) {
    case 'verified':
      return {
        title: 'Your Email Is Verified',
        description: 'Your account is ready to use.',
        icon: Mail,
        iconClass: 'text-emerald-600',
        iconWrapperClass: 'bg-emerald-100',
      }
    case 'already_verified':
      return {
        title: 'Email Already Verified',
        description: 'This email address was already confirmed earlier.',
        icon: MailCheck,
        iconClass: 'text-emerald-600',
        iconWrapperClass: 'bg-emerald-100',
      }
    case 'invalid':
      return {
        title: 'Verification Link Problem',
        description: 'This verification link is invalid or has expired.',
        icon: AlertCircle,
        iconClass: 'text-rose-600',
        iconWrapperClass: 'bg-rose-100',
      }
    default:
      return {
        title: 'Verifying Your Email',
        description: 'Please wait while we confirm your email address.',
        icon: Mail,
        iconClass: 'text-primary',
        iconWrapperClass: 'bg-primary/10',
      }
  }
})

onMounted(async () => {
  const userId = route.params.id
  const hash = route.params.hash
  const expires = route.query.expires
  const signature = route.query.signature

  if (!userId || !hash || !expires || !signature) {
    status.value = 'invalid'
    message.value = 'We could not find the full verification details in this link.'
    return
  }

  try {
    const response = await verifyEmailAddress(userId, hash, expires, signature)
    status.value = response.data?.status || 'verified'
    message.value = response.data?.message || 'Your email has been verified successfully.'

    if (status.value === 'verified' || status.value === 'already_verified') {
      redirectTimeoutId = window.setTimeout(() => {
        router.push('/')
      }, REDIRECT_DELAY_MS)
    }
  } catch (error) {
    status.value = 'invalid'
    message.value = error.response?.data?.message || 'We could not verify your email with this link.'
  }
})

onBeforeUnmount(() => {
  if (redirectTimeoutId) {
    window.clearTimeout(redirectTimeoutId)
  }
})

const goHome = () => router.push('/')
</script>

<template>
  <div class="min-h-screen bg-[#f5f5f5] px-4 py-10">
    <div class="mx-auto flex min-h-[70vh] w-full max-w-3xl items-center justify-center">
      <Card class="w-full max-w-2xl border border-black/5 bg-white shadow-xl shadow-black/5">
        <CardHeader class="items-center space-y-6 px-8 pt-10 text-center">
          <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-full" :class="stateConfig.iconWrapperClass">
            <component :is="stateConfig.icon" class="h-7 w-7" :class="stateConfig.iconClass" />
          </div>
          <div class="space-y-2">
            <CardTitle class="text-3xl font-bold leading-tight text-black">{{ stateConfig.title }}</CardTitle>
            <CardDescription class="mx-auto max-w-xl text-base leading-7 text-slate-500">
              {{ stateConfig.description }}
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent class="space-y-5 px-8 py-6 text-center">
          <p class="text-base leading-7 text-slate-600">
            {{ message }}
          </p>
          <p v-if="status === 'verified' || status === 'already_verified'" class="text-sm text-slate-500">
            Returning home in 3 seconds.
          </p>
        </CardContent>
        <CardFooter class="px-8 pb-10">
          <Button class="h-12 w-full rounded-xl bg-[#f69436] text-base font-semibold text-black hover:bg-[#ec8a2f]" @click="goHome">
            Back to Home
          </Button>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>
