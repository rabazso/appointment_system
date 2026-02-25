import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { logout as apiLogout } from '@/api';

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null);
  const user_id = ref(localStorage.getItem('user_id') || null);
  const role = ref(localStorage.getItem('role') || null);

  const isLoggedIn = computed(() => !!token.value);

  function setToken(newToken) {
    token.value = newToken;
    if (newToken) localStorage.setItem('token', newToken);
    else localStorage.removeItem('token');
  }

  function setUser(newUserId) {
    user_id.value = newUserId;
    if (newUserId) localStorage.setItem('user_id', newUserId);
    else localStorage.removeItem('user_id');
  }

  function setRole(newRole) {
    role.value = newRole;
    if (newRole) localStorage.setItem('role', newRole);
    else localStorage.removeItem('role');
  }

  async function logout() {
    try {
      await apiLogout();
    } catch (err) {
      console.error(err);
    } finally {
      setToken(null);
      setUser(null);
      setRole(null);
    }
  }

  return { token, user_id, role, isLoggedIn, setToken, setUser, setRole, logout };
});
