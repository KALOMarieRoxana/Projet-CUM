import { createContext, useContext, useState, useCallback, useRef, useEffect } from 'react';
import { useAuth } from './AuthContext'; // adaptez le chemin si besoin
import api from '../api/axiosConfig';

const DashboardDataContext = createContext(null);

const POLLING_INTERVAL = 30000; // 30 secondes

/**
 * À placer UNE SEULE FOIS, au-dessus de vos <Routes>, à l'intérieur de <AuthProvider>.
 * Comme ce provider ne se démonte jamais entre les pages, les données qu'il contient
 * (profil, demandes, types d'actes) survivent à la navigation : aller sur Statistiques
 * puis revenir sur le tableau de bord n'entraîne plus de rechargement visible.
 */
export function DashboardDataProvider({ children }) {
  const { utilisateur } = useAuth();

  const [profilDetaille, setProfilDetaille] = useState(null);
  const [mesDemandes, setMesDemandes] = useState([]);
  const [typesActes, setTypesActes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');

  // Événement de notification en temps réel (changement de statut détecté par le polling)
  const [dernierEvenement, setDernierEvenement] = useState(null);
  const [compteurNotifications, setCompteurNotifications] = useState(0);

  const demandesRef = useRef([]);
  const chargeInitialRef = useRef(false);
  const pollingRef = useRef(null);

  useEffect(() => {
    demandesRef.current = mesDemandes;
  }, [mesDemandes]);

  /**
   * Chargement complet. Ne refait l'appel que si jamais chargé, sauf si forcer=true.
   */
  const chargerDonnees = useCallback(async (forcer = false) => {
    if (chargeInitialRef.current && !forcer) return;

    try {
      setChargement(true);
      setErreur('');
      const [resProfil, resDemandes, resTypes] = await Promise.all([
        api.get('/auth/profil'),
        api.get('/demandes/mes-demandes'),
        api.get('/types-actes'),
      ]);
      setProfilDetaille(resProfil.data.utilisateur);
      setMesDemandes(resDemandes.data.demandes || []);
      setTypesActes(resTypes.data.types_actes || resTypes.data || []);
      chargeInitialRef.current = true;
    } catch (err) {
      setErreur('Impossible de charger vos données.');
    } finally {
      setChargement(false);
    }
  }, []);

  /**
   * Rechargement silencieux des demandes (sans loader), avec détection de changement
   * de statut pour déclencher une notification.
   */
  const chargerDemandesSilencieusement = useCallback(async () => {
    try {
      const res = await api.get('/demandes/mes-demandes');
      const nouvellesDemandes = res.data.demandes || [];
      const anciennesDemandes = demandesRef.current;

      let changementDetecte = false;

      nouvellesDemandes.forEach((nouvelle) => {
        const ancienne = anciennesDemandes.find((d) => d.id_demande === nouvelle.id_demande);

        if (ancienne && ancienne.statut !== nouvelle.statut) {
          changementDetecte = true;
          if (nouvelle.statut === 'acceptée') {
            setDernierEvenement({
              type: 'success',
              message: `🎉 Votre demande ${nouvelle.reference || `DEM-${nouvelle.id_demande}`} a été ACCEPTÉE !`,
            });
          } else if (nouvelle.statut === 'refusée') {
            setDernierEvenement({
              type: 'error',
              message: `❌ Votre demande ${nouvelle.reference || `DEM-${nouvelle.id_demande}`} a été REFUSÉE.`,
            });
          }
        }

        if (!ancienne) {
          changementDetecte = true;
          setDernierEvenement({
            type: 'success',
            message: `📄 Nouvelle demande ${nouvelle.reference || `DEM-${nouvelle.id_demande}`} enregistrée.`,
          });
        }
      });

      setMesDemandes(nouvellesDemandes);

      if (changementDetecte) {
        setCompteurNotifications((c) => c + 1);
      }
    } catch (err) {
      console.error('Erreur rechargement silencieux:', err);
    }
  }, []);

  const viderDernierEvenement = useCallback(() => {
    setDernierEvenement(null);
  }, []);

  /**
   * Vide le cache (à appeler à la déconnexion, depuis AuthContext par exemple).
   */
  const reinitialiserCache = useCallback(() => {
    setProfilDetaille(null);
    setMesDemandes([]);
    setTypesActes([]);
    setDernierEvenement(null);
    chargeInitialRef.current = false;
    demandesRef.current = [];
  }, []);

  // Chargement initial dès qu'un citoyen est connecté
  useEffect(() => {
    if (utilisateur) {
      chargerDonnees();
    } else {
      reinitialiserCache();
    }
  }, [utilisateur, chargerDonnees, reinitialiserCache]);

  // Polling automatique toutes les 30s, tant que le provider est monté
  // (donc même si l'utilisateur navigue vers Statistiques ou une autre page)
  useEffect(() => {
    if (!utilisateur) return;

    pollingRef.current = setInterval(() => {
      chargerDemandesSilencieusement();
    }, POLLING_INTERVAL);

    return () => {
      if (pollingRef.current) clearInterval(pollingRef.current);
    };
  }, [utilisateur, chargerDemandesSilencieusement]);

  // Rafraîchir quand l'utilisateur revient sur l'onglet du navigateur
  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.visibilityState === 'visible' && utilisateur) {
        chargerDemandesSilencieusement();
      }
    };

    document.addEventListener('visibilitychange', handleVisibilityChange);
    return () => document.removeEventListener('visibilitychange', handleVisibilityChange);
  }, [utilisateur, chargerDemandesSilencieusement]);

  const valeur = {
    profilDetaille,
    mesDemandes,
    typesActes,
    chargement,
    erreur,
    dernierEvenement,
    compteurNotifications,
    chargerDonnees,
    chargerDemandesSilencieusement,
    viderDernierEvenement,
    reinitialiserCache,
    setMesDemandes, // utile pour mettre à jour localement après une action (ex: annulation)
  };

  return (
    <DashboardDataContext.Provider value={valeur}>
      {children}
    </DashboardDataContext.Provider>
  );
}

export function useDashboardData() {
  const contexte = useContext(DashboardDataContext);
  if (!contexte) {
    throw new Error('useDashboardData doit être utilisé à l\'intérieur de <DashboardDataProvider>');
  }
  return contexte;
}
