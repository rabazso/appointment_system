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

export const getEmployeeAppointments = (params = {}) => API.get('/employee/appointments', { params });
export const cancelEmployeeAppointment = (appointmentId, payload) =>
    API.post(`/employee/appointments/${appointmentId}/cancel`, payload);
export const completeEmployeeAppointment = (appointmentId) => API.post(`/employee/appointments/${appointmentId}/complete`);
export const markEmployeeAppointmentNoShow = (appointmentId) => API.post(`/employee/appointments/${appointmentId}/no-show`);

export const getEmployeeProfile = () => API.get('/employee/profile');
export const patchEmployeeProfile = (payload) => API.patch('/employee/profile', payload);
export const uploadEmployeeProfileAvatar = (payload) => API.post('/employee/profile/avatar', payload, {
    headers: { 'Content-Type': 'multipart/form-data' }
});
export const uploadEmployeeProfileGalleryImage = (payload) => API.post('/employee/profile/gallery', payload, {
    headers: { 'Content-Type': 'multipart/form-data' }
});
export const deleteEmployeeProfileGalleryImage = (galleryId) => API.delete(`/employee/profile/gallery/${galleryId}`);

export const getEmployeeOwnTimeOffRequests = () => API.get('/employee/time-off-requests');
export const postEmployeeOwnTimeOffRequest = (payload) => API.post('/employee/time-off-requests', payload);
export const cancelEmployeeOwnTimeOffRequest = (id) => API.delete(`/employee/time-off-requests/${id}`);

export const getBarberReviews = () => API.get('/reviews');

export const getServices = () => API.get('/services');
export const getEmployees = () => API.get('/employees');
export const createEmployee = (payload) => API.post('/employees', payload);
export const deleteEmployee = (id) => API.delete(`/employees/${id}`);
export const getEmployeeSchedules = (employeeId) => API.get(`/employees/${employeeId}/schedules`);
export const createEmployeeSchedule = (employeeId, payload) => API.post(`/employees/${employeeId}/schedules`, payload);
export const updateEmployeeSchedule = (id, payload) => API.put(`/employee-schedules/${id}`, payload);
export const deleteEmployeeSchedule = (id) => API.delete(`/employee-schedules/${id}`);
export const getEmployeeServices = (employeeId) => API.get(`/employees/${employeeId}/services`);
export const createEmployeeServices = (employeeId, payload) => API.post(`/employees/${employeeId}/services`, payload);
export const updateEmployeeServices = (id, payload) => API.put(`/employee-services/${id}`, payload);
export const deleteEmployeeServices = (id) => API.delete(`/employee-services/${id}`);
export const getEmployeeAvailability = (employeeId) => API.get(`/employees/${employeeId}/availability`);
export const createEmployeeAvailability = (employeeId, payload) => API.post(`/employees/${employeeId}/availability`, payload);
export const updateEmployeeAvailability = (id, payload) => API.put(`/employee-availability/${id}`, payload);
export const deleteEmployeeAvailability = (id) => API.delete(`/employee-availability/${id}`);
export const getEmployeeBookingRules = (employeeId) => API.get(`/employees/${employeeId}/booking-rules`);
export const createEmployeeBookingRules = (employeeId, payload) => API.post(`/employees/${employeeId}/booking-rules`, payload);
export const updateEmployeeBookingRules = (id, payload) => API.put(`/employee-booking-rules/${id}`, payload);
export const deleteEmployeeBookingRules = (id) => API.delete(`/employee-booking-rules/${id}`);
export const getBookingServices = () => API.get('/booking/services');
export const getBookingEmployees = (serviceIds) => API.get('/booking/employees', {
    params: { service_ids: serviceIds },
});
export const getBookingSummary = (serviceIds, employeeId, appointmentStart) => API.get('/booking/summary', {
    params: {
        service_ids: serviceIds,
        employee_id: employeeId,
        appointment_start: appointmentStart,
    },
});
export const getBookingDays = (serviceIds, employeeId, month) => API.get('/booking/days', {
    params: {
        service_ids: serviceIds,
        employee_id: employeeId,
        month,
    },
});
export const getBookingSlots = (serviceIds, employeeId, selectedDate) => API.get('/booking/slots', {
    params: {
        service_ids: serviceIds,
        employee_id: employeeId,
        selected_date: selectedDate,
    },
});
export const getReviews = () => API.get('/reviews');
export const postReview = (payload) => API.post('/reviews', payload);
export const postAppointment = (payload) => API.post('/appointments', payload);
export const confirmAppointment = (appointmentId, expires, signature) => API.get(`/appointments/confirm/${appointmentId}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`);
export const verifyEmailAddress = (userId, hash, expires, signature) =>
    API.get(`/email/verify/${userId}/${hash}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`);
export const getUserAppointments = () => API.get('/user/appointments');
export const cancelUserAppointment = (appointmentId) => API.post(`/user/appointments/${appointmentId}/cancel`);
export const forgotPassword = (email) => API.post('/forgot-password', { email });
export const resetPassword = (payload) => API.post('/reset-password', payload);
export const completeBarberAppointment = (appointmentId) => API.post(`/employee/appointments/${appointmentId}/complete`);
export const previewEmployeeScheduleAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-schedule', payload);
export const previewEmployeeServicesAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-services', payload);
export const previewEmployeeAvailabilityAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-availability', payload);
export const previewEmployeeBookingRulesAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-booking-rules', payload);
export const previewServiceAvailabilityAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/service-availability', payload);
export const cancelAdminAppointment = (appointmentId, payload) => API.post(`/appointments/${appointmentId}/cancel`, payload);

export const postService = (payload) => API.post('/services', payload);
export const putService = (id, payload) => API.put(`/services/${id}`, payload);
export const patchService = (id, payload) => API.patch(`/services/${id}`, payload)
export const deleteService = (id) => API.delete(`/services/${id}`);
export const getServiceAvailability = (serviceId) => API.get(`/services/${serviceId}/availability`);
export const createServiceAvailability = (serviceId, payload) => API.post(`/services/${serviceId}/availability`, payload);
export const updateServiceAvailability = (id, payload) => API.put(`/service-availability/${id}`, payload);
export const deleteServiceAvailability = (id) => API.delete(`/service-availability/${id}`);

export const getShopSpecialDaysByMonth = (month) => API.get('/shop-special-days/month', {
    params: { month },
});
export const getShopSpecialDayByDate = (date) => API.get('/shop-special-days/date', {
    params: { date },
});
export const postShopSpecialDay = (payload) => API.post('/shop-special-days', payload);
export const patchShopSpecialDay = (id, payload) => API.patch(`/shop-special-days/${id}`, payload);
export const deleteShopSpecialDay = (id) => API.delete(`/shop-special-days/${id}`);

export const getShopOpeningHours = () => API.get('/shop-opening-hours');
export const postShopOpeningHour = (payload) => API.post('/shop-opening-hours', payload);
export const patchShopOpeningHour = (id, payload) => API.patch(`/shop-opening-hours/${id}`, payload);
export const getShopImages = () => API.get('/shop-images');
export const uploadShopImage = (payload) => API.post('/shop-images', payload, {
    headers: { 'Content-Type': 'multipart/form-data' }
});
export const deleteShopImage = (id) => API.delete(`/shop-images/${id}`);

export const getEmployeeTimeOffRequestsByMonth = (month) => API.get('/employee-time-off-requests/month', {
    params: { month },
});
export const getEmployeeTimeOffRequests = (params = {}) => API.get('/employee-time-off-requests', { params });
export const postEmployeeTimeOffRequest = (payload) => API.post('/employee-time-off-requests', payload);
export const patchEmployeeTimeOffRequest = (id, payload) => API.patch(`/employee-time-off-requests/${id}`, payload);
