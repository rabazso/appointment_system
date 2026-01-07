import axios from 'axios';

const API = axios.create({
    baseURL: 'http://backend.vm1.test/api',
});

export const setAuthToken = (token) => {
    if (token) {
        API.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        delete API.defaults.headers.common['Authorization'];
    }
};

export const register = async (data) => {
    const response = await API.post('/register', data);
    const token = response.data.token;
    if (token) setAuthToken(token);
    return response.data;
};

export const login = async (data) => {
    const response = await API.post('/login', data);
    const token = response.data.token;
    if (token) setAuthToken(token);
    return response.data;
};

export const logout = async () => {
    const response = await API.post('/logout');
    setAuthToken(null);
    return response.data;
};

export const getServices = () => API.get('/services');
export const getEmployees = () => API.get('/employees');
export const getAppointmentByServiceAndDate = (serviceId, selectedDate) => API.get(`/appointments?service_id=${serviceId}&selected_date=${selectedDate}`);
export const getAppointmentsByServiceAndDateAndEmployee = (serviceId, selectedDate, employeeId) => API.get(`/appointments?service_id=${serviceId}&selected_date=${selectedDate}&employee_id=${employeeId}`);
export const getEmployeesByService = (serviceId) => API.get(`/employees?service_id=${serviceId}`);
export const getEmployeesByServiceAndAppointment = (serviceId, appointment) => API.get(`/employees?service_id=${serviceId}&appointment=${appointment}`);
export const getReviews = () => API.get('/reviews');