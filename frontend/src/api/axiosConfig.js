import axios from 'axios';

// Adresse de l'API Laravel (php artisan serve)
const API_BASE_URL = 'http://127.0.0.1:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 60000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // Pas de withCredentials : on utilise un Bearer token (Sanctum), pas des cookies
});

// Routes publiques : un 401 ici ne doit PAS déconnecter ni recharger la page
const ROUTES_PUBLIQUES = [
  '/auth/connexion',
  '/auth/login',
  '/auth/inscription',
  '/auth/register',
  '/auth/verifier-email',
  '/auth/renvoyer-verification',
];

// Ajoute le token à chaque requête s'il existe
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Gère les sessions expirées (401) uniquement sur les routes protégées
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const url = error.config?.url || '';
    const estPublique = ROUTES_PUBLIQUES.some((route) => url.includes(route));

    if (error.response?.status === 401 && !estPublique) {
      localStorage.removeItem('token');
      localStorage.removeItem('utilisateur');
      window.location.href = '/connexion';
    }

    return Promise.reject(error);
  }
);

export default api;