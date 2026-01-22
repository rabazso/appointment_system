<script setup>
    import {Button} from '@components/ui/button'
    import {Card, CardHeader, CardContent, CardTitle, CardDescription} from '@components/ui/card'
    import { getEmployees } from '@/api/index'
import {ref, onMounted } from 'vue';
    const forwardPage = (barber) => {
        return '/barbers/' + barber.id
    }
    function getImageSrc(name) {
      return `../../../public/images/${name}.png`;
    }
const barbers = ref([])
    onMounted(async ()=>{
        barbers.value = (await getEmployees()).data
    })
    
</script>
<template>
    <section id="barbers" class="py-15 bg-background scroll-mt-15">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Meet Our Barbers</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">Handpicked professionals with years of experience in modern and classic barbering techniques</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <Card v-for="barber in barbers" :key="barber?.id" class="hover:scale-105 transition-transform duration-300">
                    <CardHeader>
                        <img :src="getImageSrc(barber.name)" class="w-full h-full rounded-lg">
                    </CardHeader>
                    <CardContent>
                        <CardTitle class="text-xl font-bold text-primary mb-1">{{ barber.name }}</CardTitle>
                        <CardDescription class="text-accent font-semibold">{{ barber.bio }}</CardDescription>
                    </CardContent>
                </Card>
            </div>
            <div class="text-center mt-8">
                <Button variant="secondary" class="text-lg md:text-xl font-bold w-full" to="/barbers">
                    See More...
                </Button>
            </div>
        </div>
    </section>
</template>