<script setup>
import { ref, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { getEmployees } from '@/api/index'


const barbers = ref([])


onMounted(async () => {
  try {
    const response = await getEmployees()
    barbers.value = response.data 
  } catch (error) {
    console.error('Nem sikerült betölteni a barbereket:', error)
  }
})


const imageLocation = (barber) => {
  if (barber.image) {
    return '/images/' + barber.image
  }
  return '/images/barber_placeholder.png'
}
</script>

<template>
    <section id="barbers" class="bg-white">
        <div v-if="barbers.length === 0" class="text-center py-16">
            <p>Loading barbers...</p>
        </div>

        <div v-for="(barber, index) in barbers" :key="barber.id" :class="['py-16 px-4 sm:px-6 lg:px-8 flex justify-center', index % 2 === 1 ? 'bg-black text-white' : 'bg-white text-black']">
            <div class="max-w-4xl w-full flex flex-col md:flex-row items-center md:items-start gap-8">
                <div class="w-64 h-64 bg-gray-300 rounded-lg overflow-hidden flex-shrink-0">
                    <img :src="imageLocation(barber)" :alt="barber.name" class="w-full h-full object-cover">
                </div>
                <div class="flex flex-col text-left space-y-4">
                    <h2 class="text-4xl font-bold">{{ barber.name }}</h2>
                    
                    <p class="text-accent font-bold uppercase tracking-tight text-sm">
                        {{ barber.shortdesc || barber.specialization || 'Professional Barber' }}
                    </p>
                    
                    <p class="max-w-md opacity-90 leading-relaxed">
                        {{ barber.longdesc || barber.bio || 'No description available.' }}
                    </p>
                    
                    <div class="pt-4">
                        <Button as-child class="px-7 py-7 text-lg" to="/booking">
                            Book your appointment
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>