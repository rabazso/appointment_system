import { computed, reactive, ref } from 'vue'
import { getShopInformation, patchShopInformation } from '@/api/index'

export function useShopInformation() {
  const loading = ref(false)
  const shopInformationId = ref(null)
  const form = reactive({
    phone: '',
    email: '',
    address: '',
    links: [],
  })
  const initialSnapshot = ref('')

  const isDirty = computed(() => JSON.stringify(snapshotForm()) !== initialSnapshot.value)

  async function fetchShopInformation() {
    loading.value = true

    try {
      const response = await getShopInformation()
      const settings = response.data?.data ?? response.data

      shopInformationId.value = settings.id
      form.phone = settings.phone ?? ''
      form.email = settings.email ?? ''
      form.address = settings.address ?? ''
      form.links = normalizeLinks(settings.links)
      initialSnapshot.value = JSON.stringify(snapshotForm())

      return settings
    } catch (error) {
      throw error
    } finally {
      loading.value = false
    }
  }

  async function saveShopInformation(id = shopInformationId.value, payload) {
    if (!id) return null

    try {
      const response = await patchShopInformation(id, payload)
      initialSnapshot.value = JSON.stringify(snapshotForm())
      return response.data
    } catch (error) {
      throw error
    }
  }

  function snapshotForm() {
    return {
      phone: form.phone,
      email: form.email,
      address: form.address,
      links: form.links.map((link) => ({
        label: link.label,
        url: link.url,
      })),
    }
  }

  function normalizeLinks(links) {
    if (!Array.isArray(links)) return []

    return links.map((link) => ({
      label: link?.label ?? '',
      url: link?.url ?? '',
    }))
  }

  return {
    form,
    initialSnapshot,
    isDirty,
    loading,
    shopInformationId,
    fetchShopInformation,
    saveShopInformation,
  }
}
