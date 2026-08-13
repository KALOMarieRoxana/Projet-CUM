import axios from 'axios';

const apiBaseUrl =
  import.meta.env.VITE_API_BASE_URL ||
  process.env.REACT_APP_API_BASE_URL ||
  'http://127.0.0.1:8000/api';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 60000,
   headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
    console.log('Token envoyé:', token);
  } else {
    console.error('Aucun token trouvé dans le localStorage.');
  }

  return config;
},
 (error) => {
    return Promise.reject(error);
  }
);

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('utilisateur');
      window.location.href = '/connexion';
    }

    return Promise.reject(error);
  }
);

export default api;