import axios from 'axios';
import { useAuthStore } from '@stores/AuthStore.js'

export const API = axios.create({
    baseURL: 'http://backend.vm1.test/api',
});

const withAuth = (authType, config = {}) => ({
    ...config,
    authType,
});

function getAuthToken(type) {
    return localStorage.getItem(`${type}_token`);
}

API.interceptors.request.use((config) => {
    if (config.authType) {
        const token = getAuthToken(config.authType);
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
    }

    delete config.authType;
    return config;
});

function saveSession(type, payload) {
    const store = useAuthStore();

    store.setSession(type, {
        token: payload.token ?? null,
        user_id: payload.user?.id ?? null,
        user_name: payload.display_name ?? payload.customer?.name ?? null,
        role: payload.user?.role ?? null,
    });
}

export const register = async (data) => {
    const response = await API.post('/register', data);
    saveSession('customer', response.data);

    return response.data;
};

const authenticate = async (url, data, type) => {
    const response = await API.post(url, data);
    saveSession(type, response.data);

    return response.data;
};

export const login = (data) => authenticate('/login', data, 'customer')
export const adminLogin = (data) => authenticate('/admin/login', data, 'admin')
export const employeeLogin = (data) => authenticate('/employee/login', data, 'employee')

export const logout = async (type = 'customer') => {
    const store = useAuthStore();

    if (!getAuthToken(type)) {
        store.clearSession(type);
        return { message: 'Logged out' };
    }

    const response = await API.post('/logout', null, withAuth(type));
    store.clearSession(type);

    return response.data;
};

export const getCurrentUser = (type = 'customer') => API.get('/user', withAuth(type));

export const getEmployeeAppointments = (params = {}) => API.get('/employee/appointments', withAuth('employee', { params }));
export const getAdminAppointments = (params = {}) => API.get('/admin/appointments', withAuth('admin', { params }));
export const cancelEmployeeAppointment = (appointmentId, payload) =>
    API.post(`/employee/appointments/${appointmentId}/cancel`, payload, withAuth('employee'));
export const completeEmployeeAppointment = (appointmentId) => API.post(`/employee/appointments/${appointmentId}/complete`, null, withAuth('employee'));
export const markEmployeeAppointmentNoShow = (appointmentId) => API.post(`/employee/appointments/${appointmentId}/no-show`, null, withAuth('employee'));

export const getEmployeeProfile = () => API.get('/employee/profile', withAuth('employee'));
export const patchEmployeeProfile = (payload) => API.patch('/employee/profile', payload, withAuth('employee'));
export const uploadEmployeeProfileAvatar = (payload) => API.post('/employee/profile/avatar', payload, withAuth('employee', {
    headers: { 'Content-Type': 'multipart/form-data' }
}));
export const uploadEmployeeProfileGalleryImage = (payload) => API.post('/employee/profile/gallery', payload, withAuth('employee', {
    headers: { 'Content-Type': 'multipart/form-data' }
}));
export const deleteEmployeeProfileGalleryImage = (galleryId) => API.delete(`/employee/profile/gallery/${galleryId}`, withAuth('employee'));

export const getEmployeeOwnTimeOffRequests = () => API.get('/employee/time-off-requests', withAuth('employee'));
export const postEmployeeOwnTimeOffRequest = (payload) => API.post('/employee/time-off-requests', payload, withAuth('employee'));
export const cancelEmployeeOwnTimeOffRequest = (id) => API.delete(`/employee/time-off-requests/${id}`, withAuth('employee'));
export const getEmployeeShopHolidaysByMonth = (month) => API.get('/employee/shop-holidays/month', withAuth('employee', { params: { month } }));
export const getEmployeeReviews = () => API.get('/employee/reviews', withAuth('employee'));
export const patchEmployeeReviewVisibility = (id, payload) => API.patch(`/employee/reviews/${id}`, payload, withAuth('employee'));
export const getAdminReviews = (params = {}) => API.get('/admin/reviews', withAuth('admin', { params }));
export const patchAdminReviewVisibility = (id, payload) => API.patch(`/admin/reviews/${id}`, payload, withAuth('admin'));

export const getServices = () => API.get('/services');
export const getEmployees = () => API.get('/employees');
export const getEmployeeById = (id) => API.get(`/employees/${id}`);

export const createEmployee = (payload) => API.post('/employees', payload, withAuth('admin'));
export const deleteEmployee = (id) => API.delete(`/employees/${id}`, withAuth('admin'));
export const getEmployeeSchedules = (employeeId) => API.get(`/employees/${employeeId}/schedules`, withAuth('admin'));
export const createEmployeeSchedule = (employeeId, payload) => API.post(`/employees/${employeeId}/schedules`, payload, withAuth('admin'));
export const updateEmployeeSchedule = (id, payload) => API.put(`/employee-schedules/${id}`, payload, withAuth('admin'));
export const deleteEmployeeSchedule = (id) => API.delete(`/employee-schedules/${id}`, withAuth('admin'));
export const getEmployeeServices = (employeeId) => API.get(`/employees/${employeeId}/services`, withAuth('admin'));
export const createEmployeeServices = (employeeId, payload) => API.post(`/employees/${employeeId}/services`, payload, withAuth('admin'));
export const updateEmployeeServices = (id, payload) => API.put(`/employee-services/${id}`, payload, withAuth('admin'));
export const deleteEmployeeServices = (id) => API.delete(`/employee-services/${id}`, withAuth('admin'));
export const getEmployeeAvailability = (employeeId) => API.get(`/employees/${employeeId}/availability`, withAuth('admin'));
export const createEmployeeAvailability = (employeeId, payload) => API.post(`/employees/${employeeId}/availability`, payload, withAuth('admin'));
export const updateEmployeeAvailability = (id, payload) => API.put(`/employee-availability/${id}`, payload, withAuth('admin'));
export const deleteEmployeeAvailability = (id) => API.delete(`/employee-availability/${id}`, withAuth('admin'));
export const getEmployeeBookingRules = (employeeId) => API.get(`/employees/${employeeId}/booking-rules`, withAuth('admin'));
export const createEmployeeBookingRules = (employeeId, payload) => API.post(`/employees/${employeeId}/booking-rules`, payload, withAuth('admin'));
export const updateEmployeeBookingRules = (id, payload) => API.put(`/employee-booking-rules/${id}`, payload, withAuth('admin'));
export const deleteEmployeeBookingRules = (id) => API.delete(`/employee-booking-rules/${id}`, withAuth('admin'));

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
export const postReview = (payload) => API.post('/reviews', payload, withAuth('customer'));
export const postAppointment = (payload) => API.post('/appointments', payload, withAuth('customer'));
export const confirmAppointment = (appointmentId, expires, signature) => API.get(`/appointments/confirm/${appointmentId}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`);
export const verifyEmailAddress = (userId, hash, expires, signature) =>
    API.get(`/email/verify/${userId}/${hash}?expires=${encodeURIComponent(expires)}&signature=${encodeURIComponent(signature)}`);
export const getUserAppointments = () => API.get('/user/appointments', withAuth('customer'));
export const cancelUserAppointment = (appointmentId) => API.post(`/user/appointments/${appointmentId}/cancel`, null, withAuth('customer'));
export const forgotPassword = (email) => API.post('/forgot-password', { email });
export const resetPassword = (payload) => API.post('/reset-password', payload);

export const getEmployeeOwnConfigurationServices = () => API.get('/employee/configuration/services', withAuth('employee'));
export const getEmployeeOwnConfigurationSchedules = () => API.get('/employee/configuration/schedules', withAuth('employee'));
export const getEmployeeOwnConfigurationAvailability = () => API.get('/employee/configuration/availability', withAuth('employee'));
export const getEmployeeOwnConfigurationBookingRules = () => API.get('/employee/configuration/booking-rules', withAuth('employee'));


export const previewEmployeeScheduleAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-schedule', payload, withAuth('admin'));
export const previewEmployeeServicesAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-services', payload, withAuth('admin'));
export const previewEmployeeAvailabilityAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-availability', payload, withAuth('admin'));
export const previewEmployeeBookingRulesAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/employee-booking-rules', payload, withAuth('admin'));
export const previewServiceAvailabilityAffectedAppointments = (payload) => API.post('/admin/appointments/affected-preview/service-availability', payload, withAuth('admin'));
export const cancelAdminAppointment = (appointmentId, payload) => API.post(`/appointments/${appointmentId}/cancel`, payload, withAuth('admin'));
export const completeAdminAppointment = (appointmentId) => API.post(`/appointments/${appointmentId}/complete`, null, withAuth('admin'));
export const noShowAdminAppointment = (appointmentId) => API.post(`/appointments/${appointmentId}/no-show`, null, withAuth('admin'));
export const postService = (payload) => API.post('/services', payload, withAuth('admin'));
export const putService = (id, payload) => API.put(`/services/${id}`, payload, withAuth('admin'));
export const patchService = (id, payload) => API.patch(`/services/${id}`, payload, withAuth('admin'))
export const deleteService = (id) => API.delete(`/services/${id}`, withAuth('admin'));
export const getServiceAvailability = (serviceId) => API.get(`/services/${serviceId}/availability`, withAuth('admin'));
export const createServiceAvailability = (serviceId, payload) => API.post(`/services/${serviceId}/availability`, payload, withAuth('admin'));
export const updateServiceAvailability = (id, payload) => API.put(`/service-availability/${id}`, payload, withAuth('admin'));
export const deleteServiceAvailability = (id) => API.delete(`/service-availability/${id}`, withAuth('admin'));

export const getShopSpecialDaysByMonth = (month) => API.get('/shop-special-days/month', withAuth('admin', {
    params: { month },
}));
export const getShopSpecialDayByDate = (date) => API.get('/shop-special-days/date', withAuth('admin', {
    params: { date },
}));
export const postShopSpecialDay = (payload) => API.post('/shop-special-days', payload, withAuth('admin'));
export const patchShopSpecialDay = (id, payload) => API.patch(`/shop-special-days/${id}`, payload, withAuth('admin'));
export const deleteShopSpecialDay = (id) => API.delete(`/shop-special-days/${id}`, withAuth('admin'));

export const getShopOpeningHours = () => API.get('/shop-opening-hours');
export const postShopOpeningHour = (payload) => API.post('/shop-opening-hours', payload, withAuth('admin'));
export const patchShopOpeningHour = (id, payload) => API.patch(`/shop-opening-hours/${id}`, payload, withAuth('admin'));
export const getShopInformation = () => API.get('/shop-information');
export const patchShopInformation = (id, payload) => API.patch(`/shop-information/${id}`, payload, withAuth('admin'));
export const getShopImages = () => API.get('/shop-images');
export const uploadShopImage = (payload) => API.post('/shop-images', payload, withAuth('admin', {
    headers: { 'Content-Type': 'multipart/form-data' }
}));
export const uploadShopImages = (payload) => API.post('/shop-images/batch', payload, withAuth('admin', {
    headers: { 'Content-Type': 'multipart/form-data' }
}));
export const deleteShopImage = (id) => API.delete(`/shop-images/${id}`, withAuth('admin'));

export const getEmployeeTimeOffRequestsByMonth = (month) => API.get('/employee-time-off-requests/month', withAuth('admin', {
    params: { month },
}));
export const getEmployeeTimeOffRequests = (params = {}) => API.get('/employee-time-off-requests', withAuth('admin', { params }));
export const postEmployeeTimeOffRequest = (payload) => API.post('/employee-time-off-requests', payload, withAuth('admin'));
export const patchEmployeeTimeOffRequest = (id, payload) => API.patch(`/employee-time-off-requests/${id}`, payload, withAuth('admin'));
