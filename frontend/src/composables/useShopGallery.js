import { computed, ref } from 'vue'
import { getShopImages, uploadShopImages } from '@/api/index'

export function useShopGallery() {
  const images = ref([])
  const deletedImageIds = ref([])
  const loading = ref(false)
  const initialSnapshot = ref('')

  const galleryDirty = computed(() => {
    return JSON.stringify(snapshotGalleryState(images.value, deletedImageIds.value)) !== initialSnapshot.value
  })

  async function fetchShopImages() {
    loading.value = true

    try {
      const response = await getShopImages()
      images.value = response.data.data ?? response.data ?? []
      deletedImageIds.value = []
      initialSnapshot.value = JSON.stringify(snapshotGalleryState(images.value, []))
      return images.value
    } finally {
      loading.value = false
    }
  }

  async function saveGallery() {
    const newImages = images.value.filter((item) => item.isNew)

    const payload = new FormData()
    for (const image of newImages) {
      payload.append('images[]', image.file)
    }
    for (const id of deletedImageIds.value) {
      payload.append('deleted_ids[]', String(id))
    }

    if (newImages.length || deletedImageIds.value.length) {
      await uploadShopImages(payload)
    }

    await fetchShopImages()
  }

  function snapshotGalleryState(items, deletedIds) {
    return {
      items: items.map((item) => ({
        id: item.id,
        isNew: Boolean(item.isNew),
        preview_url: item.preview_url,
        original_url: item.original_url,
      })),
      deletedIds: [...deletedIds].sort(),
    }
  }

  return {
    images,
    deletedImageIds,
    galleryDirty,
    initialSnapshot,
    loading,
    fetchShopImages,
    saveGallery,
  }
}
