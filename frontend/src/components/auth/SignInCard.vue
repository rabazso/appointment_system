<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardDescription, CardAction, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { login } from '@/api/index'
import { useAuthStore } from '@stores/AuthStore.js'

const store = useAuthStore()
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

    store.setToken(response.token)
    store.setUser(response.user.id)

    emit('success', 'Successfully signed in')
    emit('close')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Something went wrong'
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
        <Button data-testid="signupbtn" variant="link" @click="$emit('switch')">Sign Up</Button>
      </CardAction>
    </CardHeader>

    <CardContent>
      <div data-testid="errormsg-login" v-if="errorMessage" class="mb-4 py-2 px-3 rounded text-white bg-red-500">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>Email</Label>
          <Input data-testid="email-input" v-model="data.email" />
        </div>
        <div>
          <Label>Password</Label>
          <Input data-testid="password-input" v-model="data.password" type="password" />
        </div>
        <Button data-testid="login-submit" class="w-full" :disabled="loading">
          {{ loading ? 'Signing in…' : 'Sign In' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
