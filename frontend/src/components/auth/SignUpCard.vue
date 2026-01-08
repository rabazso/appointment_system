<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardAction, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { register } from '@/api/index'
import { useAuthStore } from '@stores/AuthStore'

const store = useAuthStore();
const emit = defineEmits(['close', 'success', 'switch'])

const data = ref({
  firstName: '',
  lastName : '',
  email: '',
  password: '',
  password_confirmation: ''
})

const errorMessage = ref('')
const loading = ref(false)


async function submit() {

  const fullName = `${data.value.firstName} ${data.value.lastName}`.trim()

  if (containsDigit(fullName)){
    errorMessage.value = 'Name must not contain numbers'
    return
  }
  if(containsSpecialCharacter(fullName)){
    errorMessage.value = 'Name must not contain special characters'
    return
  }

  const password = data.value.password
  const passwordErrors = []

  if (password.length < 8) passwordErrors.push('at least 8 characters')
  if (!/[A-Z]/.test(password)) passwordErrors.push('an uppercase letter')
  if (!/[a-z]/.test(password)) passwordErrors.push('a lowercase letter')
  if (!/[0-9]/.test(password)) passwordErrors.push('a number')
  if (!/[@$!%*?&.]/.test(password)) passwordErrors.push('a special character')

  if (passwordErrors.length > 0) {
    errorMessage.value = 'Password must contain ' + passwordErrors.join(', ')
    return
  }

  if (data.value.password != data.value.password_confirmation){
    errorMessage.value = 'The given passwords must match'
    return
  }

  errorMessage.value = ''
  loading.value = true

  try {
    const response = await register({
    name: fullName,
    email: data.value.email,
    password: data.value.password,})

    store.setToken(response.token)
    store.setUser(response.user.id)

    emit('success', 'Succesfully signed up')
    emit('close')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Something went wrong'
  } finally {
    loading.value = false
  }
}

function containsDigit(string) {
  return /\d/.test(string)
}
function containsSpecialCharacter(string) {
  return /[^a-zA-Z0-9\s]/.test(string);
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
      <div data-testid="errormsg-signup" v-if="errorMessage" class="mb-4 py-2 px-3 rounded text-white bg-red-500">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>First Name</Label>
          <Input data-testid="firstname-input" v-model="data.firstName" type="text" required/>
        </div>
        <div>
          <Label>Last Name</Label>
          <Input data-testid="lastname-input" v-model="data.lastName" type="text" required/>
        </div>
        <div>
          <Label>Email</Label>
          <Input data-testid="email-input" v-model="data.email" required/>
        </div>
        <div>
          <Label>Password</Label>
          <Input data-testid="password-input" v-model="data.password" type="password" required/>
        </div>
        <div>
          <Label>Confirm Password</Label>
          <Input data-testid="confpass-input" v-model="data.password_confirmation" type="password" required/>
        </div>
        <Button data-testid="signup-submit" class="w-full" :disabled="loading">
          {{ loading ? 'Signing up…' : 'Sign Up' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
