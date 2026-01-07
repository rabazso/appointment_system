import axios from 'axios';

const API = axios.create({
    baseURL: 'http://backend.vm1.test/api',
});

export const setAuthToken = (token) => {
    if (token) {
        localStorage.setItem('token', token);  
        API.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        localStorage.removeItem('token');  
        delete API.defaults.headers.common['Authorization'];
    }
};

export const setUserId = (user_id) => {
    if (user_id) {
        localStorage.setItem('user_id', user_id);
    } else {
        localStorage.removeItem('user_id');
    }
};

export const register = async (data) => {
    const response = await API.post('/register', data);
    const token = response.data.token;
    const user_id = response.data.user?.id;

    if (token) setAuthToken(token);
    if (user_id) setUserId(user_id);

    return response.data;
};

export const login = async (data) => {
    const response = await API.post('/login', data);
    const token = response.data.token;
    const user_id = response.data.user?.id;

    if (token) setAuthToken(token);
    if (user_id) setUserId(user_id);

    return response.data;
};

export const logout = async () => {
    const token = localStorage.getItem('token');
    const response = await API.post('/logout', {}, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    setAuthToken(null);
    setUserId(null);
    
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
export const postGuest = (name, email, phone) => API.post(`/guest?name=${name}&email=${email}&phone=${phone}`);
