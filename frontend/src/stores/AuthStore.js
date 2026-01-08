import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { setAuthToken, setUserId, logout as apiLogout } from '@/api';

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null);
  const user_id = ref(localStorage.getItem('user_id') || null);

  const isLoggedIn = computed(() => !!token.value);

  function setToken(newToken) {
    token.value = newToken;
    setAuthToken(newToken);
  }

  function setUser(newUserId) {
    user_id.value = newUserId;
    setUserId(newUserId);
  }

  async function logout() {
    try {
      await apiLogout();
    } catch (err) {
      console.error(err);
    } finally {
      token.value = null;
      user_id.value = null;
      setAuthToken(null);
      setUserId(null);
    }
  }

  return { token, user_id, isLoggedIn, setToken, setUser, logout };
});
