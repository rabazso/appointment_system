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
            shortdesc: 'Haircuts',
            longdesc: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
        },
        {
            id: 2,
            name: 'Barber2',
            image: 'barber_placeholder.png',
            shortdesc: 'Shaves',
            longdesc: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
        },
        {
            id: 3,
            name: 'Barber3',
            image: 'barber_placeholder.png',
            shortdesc: 'Beard',
            longdesc: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
        }
    ]
</script>
<template>
    <section id="barbers" class="bg-white">
        <div v-for="(barber, index) in barbers" :key="barber.id" :class="['py-16 px-4 sm:px-6 lg:px-8 flex justify-center', index % 2 === 1 ? 'bg-black text-white' : 'bg-white text-black']">
            <div class="max-w-4xl w-full flex flex-col md:flex-row items-center md:items-start gap-8">
                <div class="w-64 h-64 bg-gray-300 rounded-lg overflow-hidden flex-shrink-0">
                    <img :src="imageLocation(barber)" :alt="barber.name"  class="w-full h-full object-cover">
                </div>
                <div>
                    
                </div>



                <Card v-for="barber in barbers" :key="barber.id" class="hover:scale-105 transition-transform duration-300" :to="forwardPage(barber)">
                    <CardHeader>
                        
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