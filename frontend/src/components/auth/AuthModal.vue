<script setup>
import { ref } from 'vue'
import SignInCard from './SignInCard.vue'
import SignUpCard from './SignUpCard.vue'

const props = defineProps({
  initialMode: { type: String, default: 'login' }
})
const emit = defineEmits(['close', 'success'])

const mode = ref(props.initialMode)

function switchMode() {
  mode.value = mode.value === 'login' ? 'register' : 'login'
}
</script>

<template>
  <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
    <div class="relative w-full max-w-md">
      <SignInCard
        v-if="mode === 'login'"
        @close="emit('close')"
        @success="emit('success', $event)"
        @switch="switchMode"
      />
      <SignUpCard
        v-else
        @close="emit('close')"
        @success="emit('success', $event)"
        @switch="switchMode"
      />
    </div>
  </div>
</template>
