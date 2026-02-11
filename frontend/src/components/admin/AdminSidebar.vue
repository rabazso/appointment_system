<script setup>
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

defineProps({ reviews: Array })
</script>

<template>
  <div class="space-y-4">
    <Card>
      <CardContent class="grid gap-2">
        <Button class="w-full justify-start" variant="outline">Add new appointment</Button>
      </CardContent>
    </Card>
    <Card>
      <CardHeader>
        <CardTitle>Reviews</CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div v-for="review in reviews" :key="review.id" class="space-y-2">
            <div class="flex justify-between text-sm"><span class="font-medium">{{ review.client }}</span><span class="text-yellow-500">★ {{ review.rating }}</span></div>
            <p class="text-xs text-muted-foreground italic">"{{ review.text }}"</p>
            <div class="border-b pt-2"></div>
        </div>
      </CardContent>
      <CardFooter>
        <Button variant="ghost" size="sm" class="w-full">All reviews</Button>
      </CardFooter>
    </Card>
  </div>
</template>