import axios from 'axios';
import { useAuthStore } from '@stores/AuthStore.js'

export const API = axios.create({
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
    const user_name = response.data.customer.name;
    const role = response.data.user.role;

    const store = useAuthStore();
    
    if (token) store.setToken(token);
    if (user_id) store.setUser(user_id);
    if (user_name) store.setName(user_name);
    if (role) store.setRole(role);

    return response.data;
};

const authenticate = async (url, data) => {

    const response = await API.post(url, data);
    const token = response.data.token;
    const user_id = response.data.user.id;
    const user_name = response.data.display_name;
    const role = response.data.user.role;

    const store = useAuthStore();

    if (token) store.setToken(token);
    if (user_id) store.setUser(user_id);
    if (user_name) store.setName(user_name);
    if (role) store.setRole(role);

    return response.data;
};

export const login = (data) => authenticate('/login', data)
export const adminLogin = (data) => authenticate('/admin/login', data)
export const employeeLogin = (data) => authenticate('/employee/login', data)


export const logout = async () => {
    const response = await API.post('/logout');

    const store = useAuthStore();

    store.setToken(null);
    store.setUser(null);
    store.setName(null);
    store.setRole(null);
    
    return response.data;
};

export const getCurrentUser = () => API.get('/user');
export const getBarberAppointments = () => API.get('/barber/appointments');
export const cancelBarberAppointment = (appointmentId, payload) =>
    API.post(`/barber/appointments/${appointmentId}/cancel`, payload);
export const getBarberReviews = () => API.get('/barber/reviews');
export const getBarberProfile = () => API.get('/barber/profile');
export const updateBarberProfile = (payload) => API.post('/barber/profile', payload, {
    headers: { 'Content-Type': 'multipart/form-data' }
});
export const uploadBarberGalleryImage = (payload) => API.post('/barber/profile/gallery', payload, {
    headers: { 'Content-Type': 'multipart/form-data' }
});
export const deleteBarberGalleryImage = (galleryId) => API.delete(`/barber/profile/gallery/${galleryId}`);

export const getServices = () => API.get('/services');
export const getEmployees = () => API.get('/employees');
export const getEmployeeSchedules = (employeeId) => API.get(`/employees/${employeeId}/schedules`);
export const createEmployeeSchedule = (employeeId, payload) => API.post(`/employees/${employeeId}/schedules`, payload);
export const updateEmployeeSchedule = (id, payload) => API.put(`/employee-schedules/${id}`, payload);
export const deleteEmployeeSchedule = (id) => API.delete(`/employee-schedules/${id}`);
export const getEmployeeServices = (employeeId) => API.get(`/employees/${employeeId}/services`);
export const createEmployeeServices = (employeeId, payload) => API.post(`/employees/${employeeId}/services`, payload);
export const updateEmployeeServices = (id, payload) => API.put(`/employee-services/${id}`, payload);
export const deleteEmployeeServices = (id) => API.delete(`/employee-services/${id}`);
export const getAppointmentByServiceAndDate = (serviceId, selectedDate) => API.get(`/appointments?service_id=${serviceId}&selected_date=${selectedDate}`);
export const getAppointmentsByServiceAndDateAndEmployee = (serviceId, selectedDate, employeeId) => API.get(`/appointments?service_id=${serviceId}&selected_date=${selectedDate}&employee_id=${employeeId}`);
export const getEmployeesByService = (serviceId) => API.get(`/employees?service_id=${serviceId}`);
export const getEmployeesByServiceAndAppointment = (serviceId, appointment) => API.get(`/employees?service_id=${serviceId}&appointment=${appointment}`);
export const getReviews = () => API.get('/reviews');
export const postReview = (payload) => API.post('/reviews', payload);
export const postAppointment = (payload) => API.post('/appointments', payload);
export const postGuest = (name, email) => API.post('/guest', { name, email });
export const confirmAppointment = (appointmentId, expires, signature) => API.get(`/appointments/confirm/${appointmentId}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`);
export const verifyEmailAddress = (userId, hash, expires, signature) =>
    API.get(`/email/verify/${userId}/${hash}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`);
export const getUserAppointments = () => API.get('/user/appointments');
export const cancelUserAppointment = (appointmentId) => API.post(`/user/appointments/${appointmentId}/cancel`);
export const forgotPassword = (email) => API.post('/forgot-password', { email });
export const resetPassword = (payload) => API.post('/reset-password', payload);
export const completeBarberAppointment = (appointmentId) => API.post(`/barber/appointments/${appointmentId}/complete`);

export const postService = (payload) => API.post('/services', payload);
export const putService = (id, payload) => API.put(`/services/${id}`, payload);
export const patchService = (id, payload) => API.patch(`/services/${id}`, payload)
export const deleteService = (id) => API.delete(`/services/${id}`);

export const getShopSpecialDaysByMonth = (month) => API.get('/shop-special-days/month', {
    params: { month },
});
export const postShopSpecialDay = (payload) => API.post('/shop-special-days', payload);
export const patchShopSpecialDay = (id, payload) => API.patch(`/shop-special-days/${id}`, payload);
export const deleteShopSpecialDay = (id) => API.delete(`/shop-special-days/${id}`);

export const getShopOpeningHours = () => API.get('/shop-opening-hours');
export const postShopOpeningHour = (payload) => API.post('/shop-opening-hours', payload);
export const patchShopOpeningHour = (id, payload) => API.patch(`/shop-opening-hours/${id}`, payload);

export const getEmployeeTimeOffRequestsByMonth = (month) => API.get('/employee-time-off-requests/month', {
    params: { month },
});
export const getEmployeeTimeOffRequests = (params = {}) => API.get('/employee-time-off-requests', { params });
export const postEmployeeTimeOffRequest = (payload) => API.post('/employee-time-off-requests', payload);
export const patchEmployeeTimeOffRequest = (id, payload) => API.patch(`/employee-time-off-requests/${id}`, payload);
