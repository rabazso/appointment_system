import { ref } from 'vue'
import { getShopImages, uploadShopImages } from '@/api/index'

export function useShopGallery() {
  const images = ref([])
  const loading = ref(false)

  async function fetchShopImages() {
    loading.value = true

    try {
      const response = await getShopImages()
      images.value = response.data.data ?? response.data ?? []
      return images.value
    } finally {
      loading.value = false
    }
  }

  async function uploadImages(files) {
    const payload = new FormData()

    for (const file of files) {
      payload.append('images[]', file)
    }

    if (!files.length) return

    await uploadShopImages(payload)
    await fetchShopImages()
  }

  async function deleteImage(imageId) {
    const payload = new FormData()
    payload.append('deleted_ids[]', String(imageId))

    await uploadShopImages(payload)
    await fetchShopImages()
  }

  return {
    images,
    loading,
    fetchShopImages,
    uploadImages,
    deleteImage,
  }
}
