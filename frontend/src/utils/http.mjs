import axios from 'axios'
import { router } from '@/router/index'

const axiosClient = axios.create({
    baseURL: import.meta.env.VITE_BACKEND_URL,
    withCredentials: true,
    withXSRFToken: true
})

axiosClient.interceptors.response.use((response) => {
  return response;
}, error => {
  if (error.response && error.response.status === 401) {
    router.push({name: 'Login'});
  }
  throw error;
})

export default axiosClient