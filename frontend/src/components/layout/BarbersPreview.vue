<script setup>
    import {Button} from '@components/ui/button'
    import {Card, CardHeader, CardContent, CardTitle, CardDescription} from '@components/ui/card'
    const imageLocation = (barber)=>{
        return '/images/' + barber.image
    }
    const forwardPage = (barber) => {
        return '/barbers/' + barber.id
    }
    const barbers = [
        {
            id: 1,
            name: 'Barber1',
            image: 'barber_placeholder.png',
            shortdesc: 'Haircuts'
        },
        {
            id: 2,
            name: 'Barber2',
            image: 'barber_placeholder.png',
            shortdesc: 'Shaves'
        },
        {
            id: 3,
            name: 'Barber3',
            image: 'barber_placeholder.png',
            shortdesc: 'Beard'
        }
    ]
</script>
<template>
    <section id="barbers" class="py-15 bg-background">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Meet Our Barbers</h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto">Handpicked professionals with years of experience in modern and classic barbering techniques</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <Card v-for="barber in barbers" :key="barber.id" class="hover:scale-105 transition-transform duration-300" :to="forwardPage(barber)">
                    <CardHeader>
                        <img :src="imageLocation(barber)" :alt="barber.name"  class="w-full h-full rounded-lg">
                    </CardHeader>
                    <CardContent>
                        <CardTitle class="text-xl font-bold text-primary mb-1">{{ barber.name }}</CardTitle>
                        <CardDescription class="text-accent font-semibold">{{ barber.shortdesc }}</CardDescription>
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