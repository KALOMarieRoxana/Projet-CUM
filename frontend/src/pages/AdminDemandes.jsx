import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import '../styles/animations.css';

const COULEURS_STATUT = {
  'en attente': { bg: '#FFF6E0', texte: '#9A7B14' },
  'acceptée': { bg: '#E3F2E6', texte: '#2E7D32' },
  'refusée': { bg: '#FBEAE7', texte: '#A13D2B' },
};

export default function AdminDemandes() {
  const { utilisateur, deconnecter } = useAuth();
  const navigate = useNavigate();
  const [demandes, setDemandes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');

  useEffect(() => {
    if (!utilisateur) return navigate('/connexion');
    chargerDemandes();
  }, [utilisateur]);

  const chargerDemandes = async () => {
    try {
      setChargement(true);
      const { data } = await api.get('/admin/demandes');
      setDemandes(data.demandes);
    } catch (err) {
      setErreur('Impossible de charger les demandes (accès réservé aux admins).');
    } finally {
      setChargement(false);
    }
  };

  const changerStatut = async (idDemande, nouveauStatut) => {
    try {
      await api.post('/admin/demandes/statut', { id_demande: idDemande, statut: nouveauStatut });
      setDemandes((prev) => prev.map((d) => (d.id_demande === idDemande ? { ...d, statut: nouveauStatut } : d)));
    } catch (err) {
      setErreur('Erreur lors de la mise à jour.');
    }
  };

  const gererDeconnexion = () => {
    deconnecter();
    navigate('/connexion');
  };

  if (!utilisateur) return null;

  return (
    <div className="tableau-bord">
      <div className="tb-entete">
        <div>
          <div className="dossier-eyebrow" style={{ color: 'var(--sceau)' }}>Espace administrateur</div>
          <h2 style={{ margin: '4px 0 0', fontFamily: "'Fraunces', serif" }}>Demandes des citoyens</h2>
        </div>
        <button onClick={gererDeconnexion} className="bouton-tamponner" style={{ padding: '10px 18px', fontSize: 13, background: 'var(--ardoise)' }}>
          Se déconnecter
        </button>
      </div>

      <div className="tb-carte">
        {erreur && <div className="message-erreur">{erreur}</div>}

        {chargement ? (
          <p style={{ color: 'var(--ardoise)' }}>Chargement…</p>
        ) : demandes.length === 0 ? (
          <p style={{ color: 'var(--ardoise)' }}>Aucune demande pour le moment.</p>
        ) : (
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13.5 }}>
            <thead>
              <tr style={{ textAlign: 'left', borderBottom: '2px solid #EFE8D8' }}>
                <th style={{ padding: '10px 8px' }}>N°</th>
                <th style={{ padding: '10px 8px' }}>Citoyen</th>
                <th style={{ padding: '10px 8px' }}>Type d'acte</th>
                <th style={{ padding: '10px 8px' }}>Prix</th>
                <th style={{ padding: '10px 8px' }}>Statut</th>
                <th style={{ padding: '10px 8px' }}>Action</th>
              </tr>
            </thead>
            <tbody>
              {demandes.map((d) => {
                const couleurs = COULEURS_STATUT[d.statut] || COULEURS_STATUT['en attente'];
                return (
                  <tr key={d.id_demande} style={{ borderBottom: '1px solid #F1EBDD' }}>
                    <td style={{ padding: '10px 8px' }}>{d.id_demande}</td>
                    <td style={{ padding: '10px 8px' }}>{d.citoyen?.nom} {d.citoyen?.prenom}</td>
                    <td style={{ padding: '10px 8px' }}>{d.type_acte?.nom || '—'}</td>
                    <td style={{ padding: '10px 8px' }}>{Number(d.prix).toLocaleString('fr-FR')} Ar</td>
                    <td style={{ padding: '10px 8px' }}>
                      <span style={{
                        padding: '3px 10px', borderRadius: 20, fontSize: 12, fontWeight: 600,
                        background: couleurs.bg, color: couleurs.texte
                      }}>
                        {d.statut}
                      </span>
                    </td>
                    <td style={{ padding: '10px 8px', display: 'flex', gap: 8 }}>
                      <button onClick={() => changerStatut(d.id_demande, 'acceptée')} style={{ border: 'none', background: 'none', color: '#2E7D32', cursor: 'pointer', fontSize: 12, fontWeight: 600 }}>Valider</button>
                      <button onClick={() => changerStatut(d.id_demande, 'refusée')} style={{ border: 'none', background: 'none', color: 'var(--sceau)', cursor: 'pointer', fontSize: 12, fontWeight: 600 }}>Rejeter</button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}