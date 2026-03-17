<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { AlertCircle, CheckCircle2, LoaderCircle, MailCheck } from 'lucide-vue-next'
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
        description: 'Your account is ready to use. We will take you back to the home page in a moment.',
        icon: CheckCircle2,
        iconClass: 'text-emerald-600',
        panelClass: 'border-emerald-200 bg-emerald-50/90',
      }
    case 'already_verified':
      return {
        title: 'Email Already Verified',
        description: 'Your email was already confirmed earlier. We will send you back home shortly.',
        icon: MailCheck,
        iconClass: 'text-amber-600',
        panelClass: 'border-amber-200 bg-amber-50/90',
      }
    case 'invalid':
      return {
        title: 'Verification Link Problem',
        description: 'This link is invalid or has expired. You can request a new verification email from your account flow.',
        icon: AlertCircle,
        iconClass: 'text-rose-600',
        panelClass: 'border-rose-200 bg-rose-50/90',
      }
    default:
      return {
        title: 'Verifying Your Email',
        description: 'Please wait while we confirm your email address.',
        icon: LoaderCircle,
        iconClass: 'text-primary animate-spin',
        panelClass: 'border-primary/20 bg-primary/5',
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
  <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(216,180,114,0.26),_transparent_45%),linear-gradient(180deg,_#f7f2ea_0%,_#fffaf2_100%)] px-4 py-10">
    <div class="mx-auto flex min-h-[70vh] w-full max-w-3xl items-center justify-center">
      <Card class="w-full overflow-hidden border-stone-200 shadow-2xl shadow-stone-950/10">
        <div class="h-2 w-full bg-[linear-gradient(90deg,_#20160f_0%,_#8a5a31_45%,_#d6a55a_100%)]" />
        <CardHeader class="space-y-6 bg-stone-950 px-8 py-10 text-stone-50">
          <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-white/10 ring-1 ring-white/15">
            <component :is="stateConfig.icon" class="h-7 w-7" :class="stateConfig.iconClass" />
          </div>
          <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-stone-300">Account Verification</p>
            <CardTitle class="font-serif text-3xl leading-tight text-white">{{ stateConfig.title }}</CardTitle>
            <CardDescription class="max-w-xl text-sm leading-6 text-stone-300">
              {{ stateConfig.description }}
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent class="space-y-6 px-8 py-8">
          <div class="rounded-2xl border p-5 shadow-sm" :class="stateConfig.panelClass">
            <p class="text-sm font-medium leading-6 text-stone-900">
              {{ message }}
            </p>
          </div>
          <p v-if="status === 'verified' || status === 'already_verified'" class="text-sm text-stone-600">
            Returning home in 3 seconds.
          </p>
        </CardContent>
        <CardFooter class="px-8 pb-8">
          <Button class="w-full" @click="goHome">Back to Home</Button>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>
