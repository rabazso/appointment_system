<script setup>
const address = "Budapest, Clark Ádám tér"
const encodedAddress = encodeURIComponent(address)
const mapURL = `https://maps.google.com/maps?q=${encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed`
</script>
<template>
    <div>
        <h1 class="text-2xl text-center font-bold mb-4 mt-4">Contact</h1>
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
        <iframe :src="mapURL" frameborder="0"></iframe>
    </div>
</template>