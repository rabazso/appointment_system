<script setup>
import { Button } from '@components/ui/button'

const emit = defineEmits(['close', 'guest', 'login'])

function handleGuestBooking() {
  emit('guest')
  emit('close')
}

function handleLogin() {
  emit('login')
  emit('close')
}
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md text-center">
      <h2 class="text-2xl font-bold mb-4">Continue Booking</h2>
      <p class="text-gray-600 mb-6">
        Would you like to continue as a guest or sign in?
      </p>

      <div class="flex flex-col gap-3">
        <Button data-testid="guest-btn" @click="handleGuestBooking">Continue as Guest</Button>
        <Button data-testid="login-btn" @click="handleLogin">Sign In</Button>
      </div>

      <button data-testid="cancel-auth-choice" class="mt-4 text-sm text-gray-500 hover:underline" @click="$emit('close')">
        Cancel
      </button>
    </div>
  </div>
</template>
