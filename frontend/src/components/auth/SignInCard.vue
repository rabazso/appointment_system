<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardDescription, CardAction, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { login } from '@/api/index'

const emit = defineEmits(['close', 'success', 'switch'])

const data = ref({
  email: '',
  password: ''
})
const errorMessage = ref('')
const loading = ref(false)

async function submit() {
  errorMessage.value = ''
  loading.value = true
  try {
    const response = await login(data.value)
    emit('success', 'Successfully signed in')
    emit('close')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Card class="w-full p-6 rounded-xl shadow-lg">
    <button class="absolute top-2 right-2 text-muted-foreground hover:text-foreground" @click="$emit('close')">✕</button>

    <CardHeader>
      <CardTitle>Sign In</CardTitle>
      <CardDescription>Enter your credentials below</CardDescription>
      <CardAction>
        <Button variant="link" @click="$emit('switch')">Sign Up</Button>
      </CardAction>
    </CardHeader>

    <CardContent>
      <div v-if="errorMessage" class="mb-4 py-2 px-3 rounded text-white bg-red-500">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>Email</Label>
          <Input v-model="data.email" type="email" />
        </div>
        <div>
          <Label>Password</Label>
          <Input v-model="data.password" type="password" />
        </div>
        <Button class="w-full" :disabled="loading">
          {{ loading ? 'Signing in…' : 'Sign In' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
