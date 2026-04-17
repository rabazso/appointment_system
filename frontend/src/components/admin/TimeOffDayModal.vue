<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    @click.self="emit('close')"
  >
    <div class="relative flex w-96 flex-col rounded-2xl bg-white p-4 pt-12">
      <button class="absolute top-2 right-2" @click="emit('close')">
        <X class="w-8 h-8" />
      </button>

      <div class="flex items-center justify-between gap-3 pr-4">
        <div>
          <h2 class="text-2xl font-semibold">Time offs on</h2>
          <p class="text-base font-semibold">{{ date }}</p>
        </div>
        <Button @click="emit('add')">Add time off</Button>
      </div>

      <div class="mt-4 flex flex-col gap-2">
        <div
          v-for="item in timeOffs"
          :key="item.id"
          class="overflow-hidden rounded-lg border border-black/10 bg-white transition hover:border-black"
        >
          <div
            class="grid items-center gap-3 px-3 py-2 text-sm [grid-template-columns:minmax(0,1fr)_auto_auto]"
            :class="item.status !== 'cancelled' ? 'cursor-pointer' : ''"
            @click="toggleExpandableItem(item)"
          >
            <div class="min-w-0">
              <p class="truncate font-semibold">{{ item.name || item.employee }}</p>
              <p v-if="item.name" class="truncate text-gray-500">{{ item.employee }}</p>
              <p v-if="item.note" class="mt-1 line-clamp-2 min-h-[2lh] text-sm">{{ item.note }}</p>
            </div>

            <span
              class="rounded-md px-2 py-1 text-xs font-semibold capitalize"
              :class="getStatusBadgeClass(item.status)"
            >
              {{ item.status }}
            </span>

            <button
              v-if="item.status !== 'cancelled'"
              type="button"
              class="pointer-events-none inline-flex h-7 w-7 items-center justify-center text-black"
              tabindex="-1"
              aria-hidden="true"
            >
              <ChevronDown v-if="expandedTimeOffId === item.id" class="h-4 w-4" />
              <ChevronRight v-else class="h-4 w-4" />
            </button>
          </div>

          <div v-if="item.status !== 'cancelled' && expandedTimeOffId === item.id" class="border-t border-black/10 px-3 py-2">
            <div v-if="item.status === 'pending'" class="grid grid-cols-2 gap-2">
              <button
                type="button"
                class="rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold"
                @click="changeStatus(item, 'rejected')"
              >
                Reject
              </button>
              <Button class="rounded-xl text-sm" @click="changeStatus(item, 'approved')">Approve</Button>
            </div>

            <Button v-else-if="item.status === 'approved'" class="w-full rounded-xl text-sm" @click="changeStatus(item, 'cancelled')">
              Cancel
            </Button>

            <Button v-else-if="item.status === 'rejected'" class="w-full rounded-xl text-sm" @click="changeStatus(item, 'approved')">
              Approve anyway
            </Button>

            <Button v-else class="w-full rounded-xl text-sm" @click="changeStatus(item, 'approved')">
              Approve
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { ChevronDown, ChevronRight, X } from 'lucide-vue-next'
import Button from '@/components/admin/Button.vue'

defineProps({
  date: {
    type: String,
    required: true,
  },
  timeOffs: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'add', 'status-change'])

const expandedTimeOffId = ref(null)

function toggleExpandableItem(timeOff) {
  if (timeOff.status === 'cancelled') return

  expandedTimeOffId.value = expandedTimeOffId.value === timeOff.id ? null : timeOff.id
}

function changeStatus(timeOff, status) {
  expandedTimeOffId.value = null

  emit('status-change', {
    id: timeOff.id,
    status,
  })
}

function getStatusBadgeClass(status) {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-900'
  if (status === 'pending') return 'bg-black/10 text-black'
  if (status === 'rejected') return 'bg-rose-100 text-rose-900'
  if (status === 'cancelled') return 'border border-slate-400 bg-white text-slate-500 line-through'
  return 'bg-black/10 text-black'
}
</script>
