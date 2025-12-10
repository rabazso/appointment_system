<script setup>
import BaseLayout from '@layouts/BaseLayout.vue'
import { useCounter } from '@stores/CounterStore.mjs'

const counter = useCounter()
</script>

<template>
  <BaseLayout>
  </BaseLayout>
</template>

<route lang="yaml">
name: home
meta:
  title: Home
</route>
