<script setup>
import SignInCard from '@/components/employee/auth/SignInCard.vue'
import Header from '@/components/admin/Header.vue'
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-8">
    <div class="mx-auto w-full max-w-5xl">
      <Header
        title="Employee Sign In"
        description="Sign in to access the employee dashboard"
        :show-menu="false"
        :show-action="false"
      />

      <div class="mt-10 flex justify-center">
        <SignInCard />
      </div>
    </div>
  </main>
</template>
