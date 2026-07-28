import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
  withCredentials: true,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      const hadToken = !!localStorage.getItem('token');
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      // Only reload when a session token existed (expired/mid-use),
      // not on login-failure (wrong creds).
      if (hadToken) {
        window.location.reload();
      }
    }
    return Promise.reject(err);
  },
);

export default api;
