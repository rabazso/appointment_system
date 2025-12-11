<script setup>
import { Button } from '@/components/ui/button'
import {
  Card,
  CardAction,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {ref} from 'vue'
import axiosClient from '@utils/http.mjs'

const data = ref({
    email: '',
    password: ''
})
const errorMessage = ref('')

function submit(){
    axiosClient.get('/sanctum/csrf-cookie').then(response => {
        axiosClient.post('/login', data.value).then(response => {
            router.push({name: Home})
        }).catch(error => {
            console.log(error.response)
            errorMessage.value = error.response.data.message
        })
    })
}
</script>
<template>
    <section class="bg-primary h-[100vh] flex flex-col">
        <div class="justify-items-center my-auto">
            <Card class="w-full max-w-sm">
                <CardHeader>
                <CardTitle class="text-primary">Login to your account</CardTitle>
                <CardDescription>
                    Enter your email below to login to your account
                </CardDescription>
                <CardAction>
                    <Button variant="link" to="/signup">
                    Sign Up
                    </Button>
                </CardAction>
                </CardHeader>
                <CardContent>
                    <div v-if="errorMessage" class="mt-4 py-2 px-3 rounded text-white bg-red-400">
                    {{errorMessage}}
                    </div>
                <form>
                    <div class="grid w-full items-center gap-4">
                    <div class="flex flex-col space-y-1.5">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" placeholder="test@example.com" />
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <Label for="password">Password</Label>
                        <Input id="password" type="password" />
                    </div>
                    </div>
                </form>
                </CardContent>
                <CardFooter class="flex flex-col gap-2">
                <Button class="w-full" @click="submit">
                    Login
                </Button>
                </CardFooter>
            </Card>
        </div>
  </section>
</template>