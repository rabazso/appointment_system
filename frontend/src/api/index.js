import axios from 'axios';

const API = axios.create({
    baseURL: 'http:backend.vm1.test',
});

export const getServices = () => API.get('/services');
export const getEmployees = () => API.get('/employees');
export const getEmployeesByService= (serviceId) => API.get(`/employees?service_id=${serviceId}`);
export const getAppointmentByService = (serviceId) => API.get(`/appointments?service_id=${serviceId}`);
export const getEmployeesByServiceAndAppointment = (serviceId, appointment) => API.get(`/employees?service_id=${serviceId}&appointment=${appointment}`);
export const getAppointmentsByServiceAndEmployee = (serviceId, employeeId) => API.get(`/appointments?service_id=${serviceId}&employee_id=${employeeId}`);
export const getReviews = () => API.get('/reviews')