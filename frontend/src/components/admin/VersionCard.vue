<template>
  <article
    class="cursor-pointer rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:bg-slate-50/60"
    @click="$emit('view')"
  >
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div class="min-w-0 flex-1">
        <slot>
        </slot>

        <div class="mt-3 flex flex-col items-start">
          <span
            class="text-sm font-semibold p-1"
          >
            {{ current ? 'valid since: ' : 'valid from: ' }}{{validFrom}}
          </span>
          <span v-if="validTo" class="text-sm font-semibold p-1 ">
            {{ current ? 'valid until: ' : 'valid to: ' }}{{validTo}}
          </span>
        </div>
      </div>

      <div class="flex shrink-0 gap-2 self-start">
        <button
          type="button"
          class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
          @click.stop="$emit('edit')"
        >
          <Pencil class="h-4 w-4" />
        </button>
        <button
          v-if="!current"
          type="button"
          class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-black/10 bg-white text-gray-500 transition hover:bg-slate-50"
          @click.stop="$emit('delete')"
        >
          <Trash class="h-4 w-4" />
        </button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { Pencil, Trash } from 'lucide-vue-next'

defineEmits(['view', 'edit', 'delete'])

const props = defineProps({
  validFrom: {
    type: String,
    required: true,
  },
  validTo: {
    type: String,
  },
  index: {
    type: Number,
    required: true,
  }
})

const current = computed(() => props.index === 0)

</script>
