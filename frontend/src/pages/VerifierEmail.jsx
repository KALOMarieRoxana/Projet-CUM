import { useEffect, useState } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import api from '../api/axiosConfig';
import { CheckCircle, XCircle, Loader } from 'lucide-react';

export default function VerifierEmail() {
  const [searchParams] = useSearchParams();
  const [statut, setStatut] = useState('chargement'); // chargement | succes | erreur
  const [message, setMessage] = useState('');

  useEffect(() => {
    const token = searchParams.get('token');
    if (!token) {
      setStatut('erreur');
      setMessage('Lien de vérification invalide.');
      return;
    }

    api.post('/auth/verifier-email', { token })
      .then((res) => {
        setStatut('succes');
        setMessage(res.data.message);
      })
      .catch((err) => {
        setStatut('erreur');
        setMessage(err.response?.data?.message || 'Erreur lors de la vérification.');
      });
  }, [searchParams]);

  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: '100vh', background: '#F3F4F6' }}>
      <div style={{ background: '#fff', borderRadius: 16, padding: 40, maxWidth: 420, textAlign: 'center', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }}>
        {statut === 'chargement' && (
          <>
            <Loader size={40} color="#6366F1" style={{ marginBottom: 16 }} />
            <p style={{ color: '#6B7280', fontSize: 14 }}>Vérification en cours...</p>
          </>
        )}
        {statut === 'succes' && (
          <>
            <CheckCircle size={48} color="#10B981" style={{ marginBottom: 16 }} />
            <h2 style={{ margin: '0 0 8px', color: '#111827' }}>Email vérifié !</h2>
            <p style={{ color: '#6B7280', fontSize: 14, marginBottom: 20 }}>{message}</p>
            <Link to="/connexion" style={{ display: 'inline-block', padding: '10px 24px', borderRadius: 8, background: '#4F46E5', color: '#fff', textDecoration: 'none', fontSize: 13, fontWeight: 600 }}>
              Se connecter
            </Link>
          </>
        )}
        {statut === 'erreur' && (
          <>
            <XCircle size={48} color="#EF4444" style={{ marginBottom: 16 }} />
            <h2 style={{ margin: '0 0 8px', color: '#111827' }}>Échec de la vérification</h2>
            <p style={{ color: '#6B7280', fontSize: 14 }}>{message}</p>
          </>
        )}
      </div>
    </div>
  );
}