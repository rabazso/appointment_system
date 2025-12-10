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
</script>
<template>
    <section class="bg-primary h-[100vh] flex flex-col">
        <div class="justify-items-center my-auto">
            <Card class="w-full max-w-sm">
                <CardHeader>
                <CardTitle class="text-primary">Create your account</CardTitle>
                <CardAction>
                    <Button variant="link" to="/login">
                    Log In
                    </Button>
                </CardAction>
                </CardHeader>
                <CardContent>
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
                <Button class="w-full" to="/">
                    Login
                </Button>
                </CardFooter>
            </Card>
        </div>
  </section>
</template>
<route lang="yaml">
name: register
meta:
    title: Register
</route>