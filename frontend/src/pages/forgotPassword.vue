<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import BaseHeader from '@components/layout/BaseHeader.vue'
import { forgotPassword } from '@/api/index'

const email = ref('')
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

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
  successMessage.value = ''
  errorMessage.value = ''
  loading.value = true

  try {
    const { data } = await forgotPassword(email.value)
    successMessage.value = data?.message || 'Reset link sent. Check your inbox.'
  } catch (error) {
    errorMessage.value = readApiError(error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <BaseHeader variant="background" />
  <div class="min-h-screen bg-background px-4 py-10">
    <div class="mx-auto w-full max-w-md rounded-xl border bg-background p-6 text-background-foreground shadow-md">
      <h1 class="text-2xl font-bold">Forgot Password</h1>
      <p class="mt-2 text-sm text-muted-foreground">
        Enter your email and we will send a reset link.
      </p>

      <div v-if="successMessage" class="mt-4 rounded-md border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">
        {{ errorMessage }}
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div>
          <Label for="email">Email</Label>
          <Input id="email" v-model="email" type="email" required />
        </div>

        <Button class="w-full" :disabled="loading">
          {{ loading ? 'Sending...' : 'Send reset link' }}
        </Button>
      </form>
    </div>
  </div>
</template>
