<script setup>
const title = import.meta.env.VITE_APP_NAME
</script>
<template>
    <footer class="bg-white text-black p-8 md:grid md:grid-cols-3 md:gap-8 flex flex-col items-start gap-8">
        <div class="col-span-1">
            <h2 class="text-2xl font-bold mb-4">Contact</h2>
            <p class="flex items-start mb-2">
                <span class="mr-2">📍</span>Budapest 1234 Barber utca 67
            </p>
            <p class="flex items-center mb-2">
                <span class="mr-2">📞</span> 012345678
            </p>
            <p class="flex items-center mb-2">
                <span class="mr-2">✉︎</span> barber@shop.com
            </p>
            <div class="mt-4">
                <p class="flex items-start">
                    <span class="mr-2">🕒</span>Mon-Fri: 9am-8pm
                </p>
                <p class="ml-6">
                    Sat: 10am-5pm
                </p>
                <p class="ml-6">
                    Sun: closed
                </p>
            </div>
        </div>
        <div class="col-span-1 flex flex-col items-center justify-start">
            <div class="flex items-center mt-4">
                <div class="w-8 h-8 bg-accent rounded-lg flex items-center justify-center">
                    <span class="font-bold text-primary text-lg block">✂</span>
                </div>
                <span class="text-xl font-bold block ml-2">{{ title }}</span>
            </div>
        </div>
        <div class="col-span-1 md:text-right">
            <h2 class="text-2xl font-bold mb-4">Quick Links</h2>
            <ul>
                <li class="mb-2">Home</li>
                <li class="mb-2">Our Barbers</li>
                <li class="mb-2">Contact</li>
                <li class="mb-2">Book Now</li>
            </ul>
        </div>
    </footer>
</template>