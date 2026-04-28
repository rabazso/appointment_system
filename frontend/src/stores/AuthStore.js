import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { logout as apiLogout } from '@/api';

function saveValue(key, value) {
  if (value) localStorage.setItem(key, value);
  else localStorage.removeItem(key);
}

export const useAuthStore = defineStore('auth', () => {
  const customer_token = ref(localStorage.getItem('customer_token') || null);
  const customer_user_id = ref(localStorage.getItem('customer_user_id') || null);
  const customer_user_name = ref(localStorage.getItem('customer_user_name') || null);
  const customer_role = ref(localStorage.getItem('customer_role') || null);

  const admin_token = ref(localStorage.getItem('admin_token') || null);
  const admin_user_id = ref(localStorage.getItem('admin_user_id') || null);
  const admin_user_name = ref(localStorage.getItem('admin_user_name') || null);
  const admin_role = ref(localStorage.getItem('admin_role') || null);

  const employee_token = ref(localStorage.getItem('employee_token') || null);
  const employee_user_id = ref(localStorage.getItem('employee_user_id') || null);
  const employee_user_name = ref(localStorage.getItem('employee_user_name') || null);
  const employee_role = ref(localStorage.getItem('employee_role') || null);

  const sessions = {
    customer: {
      token: customer_token,
      user_id: customer_user_id,
      user_name: customer_user_name,
      role: customer_role,
    },
    admin: {
      token: admin_token,
      user_id: admin_user_id,
      user_name: admin_user_name,
      role: admin_role,
    },
    employee: {
      token: employee_token,
      user_id: employee_user_id,
      user_name: employee_user_name,
      role: employee_role,
    },
  };

  const isCustomerLoggedIn = computed(() => !!customer_token.value);
  const isAdminLoggedIn = computed(() => !!admin_token.value);
  const isEmployeeLoggedIn = computed(() => !!employee_token.value);

  function setSession(type, data) {
    const session = sessions[type]
    if ('token' in data) {
      session.token.value = data.token
      saveValue(`${type}_token`, data.token)
    }
    if ('user_id' in data) {
      session.user_id.value = data.user_id
      saveValue(`${type}_user_id`, data.user_id)
    }
    if ('user_name' in data) {
      session.user_name.value = data.user_name
      saveValue(`${type}_user_name`, data.user_name)
    }
    if ('role' in data) {
      session.role.value = data.role
      saveValue(`${type}_role`, data.role)
    }
  }

  function clearSession(type = 'customer') {
    setSession(type, {token: null, user_id: null, user_name: null, role: null});
  }

  async function logout(type = 'customer') {
    await apiLogout(type).finally(() => {
      clearSession(type);
    });
  }

  return {
    customer_token,
    customer_user_id,
    customer_user_name,
    customer_role,
    admin_token,
    admin_user_id,
    admin_user_name,
    admin_role,
    employee_token,
    employee_user_id,
    employee_user_name,
    employee_role,
    isCustomerLoggedIn,
    isAdminLoggedIn,
    isEmployeeLoggedIn,
    setSession,
    clearSession,
    logout,
  };
});
