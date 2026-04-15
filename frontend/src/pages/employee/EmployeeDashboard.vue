<script setup>
import Header from '@/components/admin/Header.vue'
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-8">
    <div class="mx-auto w-full max-w-6xl">
      <Header
        title="Employee Dashboard"
        description="Manage appointments, profile updates, and availability."
        :show-menu="false"
        :show-action="false"
      />

      <section class="rounded-2xl bg-white p-8 shadow-sm">
        <p class="text-sm text-gray-500">Employee dashboard is ready for the next layout pass.</p>
      </section>
    </div>
  </main>
</template>
