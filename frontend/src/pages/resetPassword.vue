<script setup>
import { onBeforeUnmount, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import BaseHeader from '@components/layout/BaseHeader.vue'
import { resetPassword } from '@/api/index'

const route = useRoute()
const router = useRouter()

const token = ref(String(route.query.token || ''))
const email = ref(String(route.query.email || ''))
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const countdown = ref(0)
let redirectTimer = null
let countdownTimer = null

function readApiError(error) {
  const errors = error.response?.data?.errors
  if (errors) {
    const firstField = Object.keys(errors)[0]
    if (firstField && Array.isArray(errors[firstField]) && errors[firstField][0]) {
      return errors[firstField][0]
    }
  }

  return error.response?.data?.message || 'Something went wrong'
}

async function submit() {
  if (redirectTimer) {
    clearTimeout(redirectTimer)
    redirectTimer = null
  }
  if (countdownTimer) {
    clearInterval(countdownTimer)
    countdownTimer = null
  }

  successMessage.value = ''
  errorMessage.value = ''
  countdown.value = 0

  if (!token.value || !email.value) {
    errorMessage.value = 'Missing reset token or email in URL.'
    return
  }

  loading.value = true

  try {
    const { data } = await resetPassword({
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    successMessage.value = data?.message || 'Password reset successful. You can log in now.'
    password.value = ''
    passwordConfirmation.value = ''
    countdown.value = 3

    if (redirectTimer) {
      clearTimeout(redirectTimer)
    }
    redirectTimer = setTimeout(() => {
      router.push('/')
    }, 3000)

    countdownTimer = setInterval(() => {
      if (countdown.value > 0) {
        countdown.value -= 1
      } else if (countdownTimer) {
        clearInterval(countdownTimer)
        countdownTimer = null
      }
    }, 1000)
  } catch (error) {
    errorMessage.value = readApiError(error)
  } finally {
    loading.value = false
  }
}

onBeforeUnmount(() => {
  if (redirectTimer) {
    clearTimeout(redirectTimer)
  }
  if (countdownTimer) {
    clearInterval(countdownTimer)
  }
})
</script>

<template>
  <BaseHeader variant="background" />
  <div class="min-h-screen bg-background px-4 py-10">
    <div class="mx-auto w-full max-w-md rounded-xl border bg-background p-6 text-background-foreground shadow-md">
      <h1 class="text-2xl font-bold">Reset Password</h1>
      <p class="mt-2 text-sm text-muted-foreground">
        Set your new password below.
      </p>

      <div v-if="successMessage" class="mt-4 rounded-md border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
        {{ successMessage }}
      </div>
      <div v-if="successMessage && countdown > 0" class="mt-2 text-sm text-muted-foreground">
        You are getting redirected ({{ countdown }})
      </div>
      <div v-if="errorMessage" class="mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">
        {{ errorMessage }}
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div>
          <Label for="email">Email</Label>
          <Input id="email" v-model="email" type="email" required />
        </div>

        <div>
          <Label for="password">New Password</Label>
          <Input id="password" v-model="password" type="password" required />
        </div>

        <div>
          <Label for="password_confirmation">Confirm Password</Label>
          <Input id="password_confirmation" v-model="passwordConfirmation" type="password" required />
        </div>

        <Button class="w-full" :disabled="loading">
          {{ loading ? 'Resetting...' : 'Reset password' }}
        </Button>
      </form>
    </div>
  </div>
</template>
