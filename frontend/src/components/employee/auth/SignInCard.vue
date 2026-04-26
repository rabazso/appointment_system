<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { employeeLogin } from '@/api'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'

const router = useRouter()

const data = ref({
  email: '',
  password: '',
})

const errorMessage = ref('')

async function submitHandler() {
  errorMessage.value = ''
  try {
    await employeeLogin(data.value)
    router.push('/employee/appointments')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Something went wrong'
  }
}
</script>

<template>
  <Card class="w-full max-w-md rounded-xl p-6 shadow-lg">
    <CardHeader>
      <CardTitle>Sign In</CardTitle>
      <CardDescription>Enter your employee credentials</CardDescription>
    </CardHeader>

    <CardContent>
      <FormKit type="form" autocomplete="off" :incomplete-message="false" @submit="submitHandler">
        <FormKit v-model="data.email" type="text" name="employee_email" label="Email" validation="required|email" autocomplete="off" autocapitalize="none" spellcheck="false"/>
        <FormKit v-model="data.password" type="password" name="employee_password" label="Password" validation="required"/>
        <div v-if="errorMessage" class="mt-1 text-sm text-destructive-foreground">
          {{ errorMessage }}
        </div>
      </FormKit>
    </CardContent>
  </Card>
</template>
