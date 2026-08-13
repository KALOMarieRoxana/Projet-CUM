import { createContext, useContext, useState } from 'react';
import api from '../api/axiosConfig';

const AuthContext = createContext(null);

function extraireDonneesSession(data) {
  const token = data?.token || data?.access_token || data?.bearer_token;
  const utilisateur = data?.utilisateur || data?.user || data?.data?.utilisateur || data?.data?.user || null;
  return { token, utilisateur };
}

function lireUtilisateurDepuisStockage() {
  try {
    const sauvegarde = localStorage.getItem('utilisateur');
    return sauvegarde ? JSON.parse(sauvegarde) : null;
  } catch {
    return null;
  }
}

export function AuthProvider({ children }) {
  const [utilisateur, setUtilisateur] = useState(() => lireUtilisateurDepuisStockage());
  const [chargement, setChargement] = useState(false);
  const [erreur, setErreur] = useState('');

  const enregistrerSession = (data) => {
    const { token, utilisateur } = extraireDonneesSession(data);

    if (!token || !utilisateur) {
      throw new Error('Réponse du serveur incomplète pour la session utilisateur.');
    }

    localStorage.setItem('token', token);
    localStorage.setItem('utilisateur', JSON.stringify(utilisateur));
    setUtilisateur(utilisateur);
  };

  const connecter = async (email, mot_de_passe) => {
    setChargement(true);
    setErreur('');

    try {
      const { data } = await api.post('/auth/connexion', { email, mot_de_passe });
      enregistrerSession(data);
      return true;
    } catch (err) {
      setErreur(err.response?.data?.message || err.message || 'Erreur de connexion au serveur.');
      return false;
    } finally {
      setChargement(false);
    }
  };

  const inscrire = async (formulaire) => {
    setChargement(true);
    setErreur('');

    try {
      const { data } = await api.post('/auth/inscription', formulaire);
      enregistrerSession(data);
      return true;
    } catch (err) {
      setErreur(err.response?.data?.message || err.response?.data?.errors?.email?.[0] || err.message || 'Erreur lors de l\'inscription.');
      return false;
    } finally {
      setChargement(false);
    }
  };

  const deconnecter = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('utilisateur');
    setUtilisateur(null);
  };

  return (
    <AuthContext.Provider value={{ utilisateur, connecter, inscrire, deconnecter, chargement, erreur }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
