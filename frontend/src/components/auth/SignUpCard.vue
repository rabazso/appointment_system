<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardAction, CardDescription, CardContent } from '@/components/ui/card'
import { register } from '@/api/index'
import { Check, X } from 'lucide-vue-next'
import { useToastStore } from '@/stores/ToastStore.js'

const emit = defineEmits(['close', 'success', 'switch'])
const toast = useToastStore()

const errorMessage = ref('')
const loading = ref(false)

async function submitHandler(formData) {
  errorMessage.value = ''
  loading.value = true

  const fullName = `${formData.firstName} ${formData.lastName}`.trim()

  try {
    const response = await register({
      name: fullName,
      email: formData.email,
      password: formData.password,
    })

    emit('success', response.message || 'Successfully signed up')
    emit('close')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Something went wrong'
    toast.showError('Failed to sign up.')
  } finally {
    loading.value = false
  }
}


const hasValue = computed(() => password.value.length > 0)
const password = ref('')
const passwordRules = computed(() => [
  {
    key: 'length',
    label: 'At least 8 characters',
    valid: password.value.length >= 8,
  },
  {
    key: 'uppercase',
    label: 'At least one uppercase letter',
    valid: /[A-Z]/.test(password.value),
  },
  {
    key: 'lowercase',
    label: 'At least one lowercase letter',
    valid: /[a-z]/.test(password.value),
  },
  {
    key: 'number',
    label: 'At least one number',
    valid: /[0-9]/.test(password.value),
  },
  {
    key: 'special',
    label: 'At least one special character',
    valid: /[@$!%*?&.]/.test(password.value),
  },
])

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
      <FormKit
        type="form"
        :submit-label="loading ? 'Signing up…' : 'Sign up'"
        data-testid="signup-form"
        autocomplete="off"
        :submit-attrs="{ 'data-testid': 'signup-submit', disabled: loading }"
        @submit="submitHandler"
        :incomplete-message="false"
      >
        <FormKit data-testid="firstname-input" type="text" name="firstName" label="First Name" placeholder="John" validation="required|alpha:latin" />
        <FormKit data-testid="lastname-input" type="text" name="lastName" label="Last Name" placeholder="Doe" validation="required|alpha:latin" />
        <FormKit data-testid="email-input" type="text" name="email" label="Email" placeholder="barbershop@example.com" validation="required|email" />
        <FormKit data-testid="password-input" v-model="password" type="password" name="password" label="Password" placeholder="Your password" validation-visibility="live"
          :validation="[['required'], ['length', 8], ['matches', /[A-Z]/], ['matches', /[a-z]/], ['matches', /[0-9]/], ['matches', /[@$!%*?&.]/]]"
          :classes="{messages: 'hidden'}" />
        <div class="rounded-xl border border-zinc-200 p-4">
          <p class="mb-3 text-sm font-medium text-foreground">
            Password requirements
          </p>

          <ul class="space-y-2 text-sm">
            <li v-for="rule in passwordRules" :key="rule.key" class="flex items-center gap-2">
              <Check v-if="rule.valid" class="h-4 w-4 text-green-600"/>
              <X v-else class="h-4 w-4 text-red-500"/>

              <span :class="!hasValue ? 'text-muted-foreground' : rule.valid ? 'text-green-700' : 'text-destructive-foreground' ">
                {{ rule.label }}
              </span>
            </li>
          </ul>
        </div>
        <FormKit data-testid="confpass-input" type="password" name="password_confirm" label="Confirm password" placeholder="Confirm password" validation="required|confirm"/>
        <div
        v-if="errorMessage"
        data-testid="errormsg-signup"
        class="text-sm text-destructive-foreground mt-1"
        >
          {{ errorMessage }}
        </div>
      </FormKit>
    </CardContent>
  </Card>
</template>
