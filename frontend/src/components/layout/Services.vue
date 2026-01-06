<script setup>
import {Card, CardHeader, CardContent, CardTitle, CardDescription} from '@components/ui/card'
import {Button} from '@components/ui/button'
    import { getServices } from '@/api/index'
import {ref, onMounted } from 'vue';

const services = ref([])
    onMounted(async ()=>{
        services.value = (await getServices()).data
    })

</script>
<template>
    <section id="services" class="py-15 bg-primary text-primary-foreground scroll-mt-15">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Our Services</h2>
                <p class="text-lg text-card-foreground/70 max-w-2xl mx-auto">Premium grooming solutions tailored to every man's style and needs</p>
            </div>
            <div class="grid md:grid-cols-3 justify-items-center">
                <Card v-for="service in services" :key="service.id" class="bg-card w-[70%] text-center border-2 border-accent/20 hover:border-accent/60 transition-colors duration-400 cursor-default px-8 py-20">
                    <CardHeader>
                        <CardTitle class="text-2xl text-card-foreground font-bold mb-4">{{ service.name }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <CardDescription class="text-lg text-card-foreground/70 mb-8 leading-6">{{ service.description }}</CardDescription>
                    </CardContent>
                </Card>
            </div>
            <div class="text-center mt-20">
                <Button class="text-lg md:text-xl font-bold p-8" to="/booking">
                    Book your appointment
                </Button>
            </div>
        </div>
    </section>
</template>