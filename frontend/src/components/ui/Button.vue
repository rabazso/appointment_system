<template>
  <button :type="type" :class="class" @click="handleClick">
    <slot />
  </button>
</template>
<script setup>
import { useRouter } from "vue-router"

const props = defineProps({
  to: {
    type: [String, Object],
    required: true,
  },
  type: {
    type: String,
    default: "button",
  },
  class: {
    type: String,
    default: "",
  },
})

const emit = defineEmits(["click"])
const router = useRouter()

function handleClick(event) {
  emit("click", event)
  router.push(props.to)
}
</script>