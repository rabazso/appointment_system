<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardAction, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { register } from '@/api/index'

const emit = defineEmits(['close', 'success', 'switch'])

const data = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const errorMessage = ref('')
const loading = ref(false)

async function submit() {
  errorMessage.value = ''
  loading.value = true
  try {
    const response = await register(data.value)
    emit('success', 'Account created successfully')
    emit('close')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Registration failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Card class="w-full p-6 rounded-xl shadow-lg">
    <button class="absolute top-2 right-2 text-muted-foreground hover:text-foreground" @click="$emit('close')">✕</button>

    <CardHeader>
      <CardTitle>Sign Up</CardTitle>
      <CardDescription>Enter your details below</CardDescription>
      <CardAction>
        <Button variant="link" @click="$emit('switch')">Sign In</Button>
      </CardAction>
    </CardHeader>

    <CardContent>
      <div v-if="errorMessage" class="mb-4 py-2 px-3 rounded text-white bg-red-500">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>Name</Label>
          <Input v-model="data.name" type="text" />
        </div>
        <div>
          <Label>Email</Label>
          <Input v-model="data.email" type="email" />
        </div>
        <div>
          <Label>Password</Label>
          <Input v-model="data.password" type="password" />
        </div>
        <div>
          <Label>Confirm Password</Label>
          <Input v-model="data.password_confirmation" type="password" />
        </div>
        <Button class="w-full" :disabled="loading">
          {{ loading ? 'Signing up…' : 'Sign Up' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
