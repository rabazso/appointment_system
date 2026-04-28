<script setup>
import { ref } from 'vue'
import { Trash, X } from 'lucide-vue-next'
import ConfirmDeleteModal from '@/components/admin/ConfirmDeleteModal.vue'

const props = defineProps({
  images: {
    type: Array,
    default: () => [],
  },
  uploadLabel: {
    type: String,
    default: '+ Upload images',
  },
  helperText: {
    type: String,
    default: 'JPG, PNG, WebP. Max 4 MB each.',
  },
  emptyText: {
    type: String,
    default: 'No gallery images yet',
  },
  accept: {
    type: String,
    default: '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp',
  },
  deleteModalDescription: {
    type: String,
    default: 'This will permanently remove the selected gallery image.',
  },
})

const emit = defineEmits(['upload', 'delete'])

const activeImage = ref(null)
const imagePendingDelete = ref(null)

function handleUpload(event) {
  const files = Array.from(event.target.files ?? [])
  event.target.value = ''
  if (!files.length) return
  emit('upload', files)
}

function openPreview(image) {
  activeImage.value = image
}

function closePreview() {
  activeImage.value = null
}

function requestDelete(image) {
  imagePendingDelete.value = image
}

function confirmDelete() {
  const image = imagePendingDelete.value
  if (!image) return
  emit('delete', image)
  imagePendingDelete.value = null
}
</script>

<template>
  <div class="mb-4 flex items-start justify-between gap-3">
    <div>
      <h2 class="text-2xl font-semibold text-black">Gallery</h2>
      <p class="mt-1 text-sm text-slate-500">Upload and manage the images customers see first.</p>
    </div>

    <div class="flex flex-col items-end gap-1">
      <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        {{ props.uploadLabel }}
        <input
          type="file"
          :accept="props.accept"
          multiple
          class="hidden"
          @change="handleUpload"
        />
      </label>
      <p class="text-right text-xs text-slate-400">
        {{ props.helperText }}
      </p>
    </div>
  </div>

  <div v-if="props.images.length" class="grid flex-1 grid-cols-[repeat(auto-fill,minmax(100px,1fr))] gap-3 overflow-auto pr-1">
    <div
      v-for="image in props.images"
      :key="image.id"
      class="group relative aspect-square cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
      @click="openPreview(image)"
    >
      <img
        :src="image.preview_url"
        :alt="`Gallery image ${image.id}`"
        class="h-full w-full object-cover"
      />
      <button
        type="button"
        class="absolute right-1 top-1 inline-flex items-center justify-center rounded-lg border border-black/10 bg-white p-1 text-slate-700 opacity-0 transition group-hover:opacity-100 hover:border-black"
        @click.stop="requestDelete(image)"
      >
        <Trash class="h-4 w-4" />
      </button>
    </div>
  </div>

  <div
    v-else
    class="flex min-h-0 flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-400"
  >
    {{ props.emptyText }}
  </div>

  <div
    v-if="activeImage"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-6"
    @click="closePreview"
  >
    <div class="relative max-h-full max-w-6xl" @click.stop>
      <button
        type="button"
        class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-slate-900 shadow-sm transition hover:bg-white"
        @click="closePreview"
      >
        <X class="h-5 w-5" />
      </button>

      <img
        :src="activeImage.original_url || activeImage.preview_url"
        :alt="`Gallery image ${activeImage.id}`"
        class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl"
      />
    </div>
  </div>

  <ConfirmDeleteModal
    v-if="imagePendingDelete"
    title="Delete image"
    :description="props.deleteModalDescription"
    question-prefix="Are you sure you want to delete "
    target-name="this image"
    @close="imagePendingDelete = null"
    @confirm="confirmDelete"
  />
</template>
