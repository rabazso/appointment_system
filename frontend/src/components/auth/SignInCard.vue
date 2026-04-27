<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardDescription, CardAction, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { login } from '@/api/index'
import { useAuthStore } from '@stores/AuthStore.js'
import { useToastStore } from '@/stores/ToastStore.js'

const store = useAuthStore()
const toast = useToastStore()
const emit = defineEmits(['close', 'success', 'switch'])

const data = ref({
  email: '',
  password: ''
})
const errorMessage = ref('')
const loading = ref(false)

async function submitHandler() {
  errorMessage.value = ''
  loading.value = true
  try {
    const response = await login(data.value)

    store.setToken(response.token)
    store.setUser(response.user.id)
    store.setRole(response.user.role)

    emit('success', 'Successfully signed in')
    emit('close')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Something went wrong'
    toast.showError('Failed to sign in.')
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
      

      <FormKit 
        type="form"
        :submit-label="loading ? 'Signing in…' : 'Sign in'"
        data-testid="signin-form"
        autocomplete="off"
        :submit-attrs="{ 'data-testid': 'signin-submit', disabled: loading }"
        @submit="submitHandler"
        :incomplete-message="false">
        <FormKit data-testid="email-input" v-model="data.email" type="text" name="email" label="Email" placeholder="barbershop@example.com" validation="required|email" />
        <FormKit data-testid="password-input" v-model="data.password" type="password" name="password" label="Password" placeholder="Your password" validation="required" />
        <div data-testid="errormsg-login" v-if="errorMessage" class="text-sm text-destructive-foreground mt-1">
          {{ errorMessage }}
        </div>
        <div class="text-right">
          <RouterLink to="/forgot-password" class="text-sm underline hover:opacity-80">Forgot password?</RouterLink>
        </div>
      </FormKit>
    </CardContent>
  </Card>
</template>
