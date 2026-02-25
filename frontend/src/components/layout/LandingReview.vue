<script setup>
import { Button } from '@components/ui/button';
import {Star} from "lucide-vue-next"
import {Card, CardContent, CardTitle, CardDescription, CardHeader} from '@components/ui/card'
import {ref, onMounted} from 'vue'
import { getReviews } from '@/api/index';

const reviews = ref([])
const users = ref([])

onMounted(async ()=>{
    const response = await getReviews()
    reviews.value = response.data?.data
})

onMounted(async ()=>{
    const response = await getUser
})
</script>
<template>
<section id="reviews" class="py-15 bg-background scroll-mt-15">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Customer feedback</h2>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">We Love Our Customer Reviews! We are so thankful for the amazing and sincere feedback we have received from our customers!</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <Card v-for="review in reviews" :key="review?.id" class="border-2 border-border py-8 px-5 rounded-lg">
                <CardContent class="space-y-1.5 flex flex-col">
                    <CardTitle class="text-lg font-semibold text-primary mb-1">{{ review. }}</CardTitle>
                    <CardDescription class="flex items-center gap-1 flex-nowrap"><Star v-for="star in review.rating" :key="star" class="w-4 h-4 text-primary fill-accent"></Star></CardDescription>
                    <CardDescription class="text-accent font-semibold text-xl min-h-6">{{ review.comment || '' }}</CardDescription>
                </CardContent>
            </Card>
        </div>
        <div class="text-center mt-50">
            <Button variant="outlinebackground" class="text-lg md:text-xl font-bold w-fit px-20 py-8">
                Leave a review
            </Button>
        </div>
    </div>
</section>
</template>
