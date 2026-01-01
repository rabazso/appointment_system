<script setup>
import BaseHeader from '@components/layout/BaseHeader.vue';
import ContactLayout from '@components/layout/ContactLayout.vue';
import Footer from '@components/layout/Footer.vue';


</script>
<template>
<div class="flex flex-col min-h-screen">
    <BaseHeader/>
    <main class="flex-grow">
        <ContactLayout/>
    </main>
    <Footer/>
</div>
</template>