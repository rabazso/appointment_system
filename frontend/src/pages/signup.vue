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
import { router } from '@/router/index'
import {ref} from 'vue'
import axiosClient from '@utils/http.mjs'

const data = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = ref({
    name: [],
    email: [],
    password: []
})

function submit(){
    axiosClient.get('sanctum/csrf-cookie').then(response => {
        axiosClient.post('/register', data.value).then(response => {
            router.push({name: Home})
        }).catch(error => {
            console.log(error.response.data)
            errors.value = error.response.data.errors
        })
    })
}
</script>
<template>
    <section class="bg-primary h-[100vh] flex flex-col">
        <div class="justify-items-center my-auto">
            <Card class="w-full max-w-sm">
                <CardHeader>
                <CardTitle class="text-primary w-full">Create your account</CardTitle>
                <CardAction>
                    <Button variant="link" to="/login">
                        Log In
                    </Button>
                </CardAction>
                <CardDescription class="">Already have an account?</CardDescription>
                </CardHeader>
                <CardContent>
                <form>
                    <div class="grid w-full items-center gap-4">
                        <div class="flex flex-col space-y-1.5">
                            <Label for="name">Name</Label>
                            <Input id="name" type="text" placeholder="Example Name" />
                            <p v-if="errors.name" class="text-sm text-red-600">
                                {{ errors.name[0] }}
                            </p>
                        </div>
                        <div class="flex flex-col space-y-1.5">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" placeholder="test@example.com" />
                            <p v-if="errors.email" class="text-sm text-red-600">
                                {{ errors.email[0] }}
                            </p>
                        </div>
                        <div class="flex flex-col space-y-1.5">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" />
                            <p v-if="errors.password" class="text-sm text-red-600">
                                {{ errors.password[0] }}
                            </p>
                        </div>
                        <div class="flex flex-col space-y-1.5">
                            <Label for="passwordConfirmation">Repeat Password</Label>
                            <Input id="passwordConfirmation" type="password" />
                        </div>
                    </div>
                </form>
                </CardContent>
                <CardFooter class="flex flex-col gap-2">
                <Button @click="submit" class="w-full">
                    Create an Account
                </Button>
                </CardFooter>
            </Card>
        </div>
  </section>
</template>