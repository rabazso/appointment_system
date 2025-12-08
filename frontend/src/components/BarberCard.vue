<script setup>
    import {defineProps, computed} from 'vue'
    const {barber} = defineProps({
        barber: Object
    })

    const imageLocation = (()=>{
        return new URL(`../../public/${barber.photo_url}`)
    })

    function ImageLocation(barber){
        return "../../public/" + barber.photo_url
    }
</script>
<template>
    <div>
        <img :src="mageLocation">
        <h2></h2>
        <p></p>
    </div>
</template>