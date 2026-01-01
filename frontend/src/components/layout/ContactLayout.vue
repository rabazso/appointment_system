<script setup>
const address = "Budapest, Clark Ádám tér"
const encodedAddress = encodeURIComponent(address)
const mapURL = `https://maps.google.com/maps?q=${encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed`
</script>
<template>
    <div class="min-h-full">
        <h1 class="text-4xl text-center font-bold mb-8 mt-8 pb-5">Contact us!</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start mx-auto">
            <div class="text-2xl md:mx-5 mx-auto">
                <p class="mb-8">
                    <span class="font-bold mr-2">📍 Visit us!</span>Budapest 1234 Barber utca 67
                </p>
                <p class="mb-8">
                    <span class="mr-2 font-bold">📞 Call us!</span> 012345678
                </p>
                <p class="mb-8">
                    <span class="mr-2 font-bold">✉︎ E-mail us! </span> barber@shop.com
                </p>
                <div class="mt-4">
                    <p class="flex items-start">
                        <span class="font-bold mr-2">🕒 Opening hours:</span>Mon-Fri: 9am-8pm
                    </p>
                    <p class="ml-56">
                        Sat: 10am-5pm
                    </p>
                    <p class="ml-56">
                        Sun: closed
                    </p>
                </div>
            </div>
            <div class="mx-auto h-96 w-full">
                <iframe :src="mapURL" frameborder="0" class="mx-auto w-95 h-full rounded-lg shadow-lg"></iframe>
            </div>
        </div>
    </div>
</template>