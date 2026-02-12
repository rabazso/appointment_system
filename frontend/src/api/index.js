import axios from 'axios';
import { useAuthStore } from '@stores/AuthStore.js'

const API = axios.create({
    baseURL: 'http://backend.vm1.test/api',
    
});

API.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});


export const register = async (data) => {

    const response = await API.post('/register', data);
    const token = response.data.token;
    const user_id = response.data.user.id;

    const store = useAuthStore();
    
    if (token) store.setToken(token);
    if (user_id) store.setUser(user_id);

    return response.data;
};

export const login = async (data) => {

    const response = await API.post('/login', data);
    const token = response.data.token;
    const user_id = response.data.user.id;

    const store = useAuthStore();

    if (token) store.setToken(token);
    if (user_id) store.setUser(user_id);

    return response.data;
};


export const logout = async () => {
    const response = await API.post('/logout');

    const store = useAuthStore();

    store.setToken(null);
    store.setUser(null);
    
    return response.data;
};

export const getServices = () => API.get('/services');
export const getEmployees = () => API.get('/employees');
export const getAppointmentByServiceAndDate = (serviceId, selectedDate) => API.get(`/appointments?service_id=${serviceId}&selected_date=${selectedDate}`);
export const getAppointmentsByServiceAndDateAndEmployee = (serviceId, selectedDate, employeeId) => API.get(`/appointments?service_id=${serviceId}&selected_date=${selectedDate}&employee_id=${employeeId}`);
export const getEmployeesByService = (serviceId) => API.get(`/employees?service_id=${serviceId}`);
export const getEmployeesByServiceAndAppointment = (serviceId, appointment) => API.get(`/employees?service_id=${serviceId}&appointment=${appointment}`);
export const getReviews = () => API.get('/reviews');
export const postAppointment = (serviceId, employeeId, appointmentStart, customerId) => API.post(`/appointments?service_id=${serviceId}&employee_id=${employeeId}&appointment_start=${appointmentStart}&customer_id=${customerId}`);
export const postGuest = (name, email) => API.post(`/guest?name=${name}&email=${email}`);
export const confirmAppointment = (appointmentId, expires, signature) => API.get(`/appointments/confirm/${appointmentId}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`);
export const getUserAppointments = () => API.get('/user/appointments');
export const cancelUserAppointment = (appointmentId) => API.post(`/user/appointments/${appointmentId}/cancel`);
