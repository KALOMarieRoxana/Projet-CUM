import { useState, useEffect, useRef } from 'react';
import { Bell, CheckCircle, XCircle, AlertCircle, Download } from 'lucide-react';
import { useTheme } from '../theme/ThemeContext';
import api from '../api/axiosConfig';

export default function NotificationBell() {
  const { colors } = useTheme();
  const [notifications, setNotifications] = useState([]);
  const [nonLues, setNonLues] = useState(0);
  const [ouvert, setOuvert] = useState(false);
  const ref = useRef(null);

  // ✅ Charger le compteur toutes les 15 secondes
  useEffect(() => {
    chargerCompteur();
    const interval = setInterval(chargerCompteur, 15000);
    return () => clearInterval(interval);
  }, []);

  // ✅ Charger les notifications quand on ouvre
  useEffect(() => {
    if (ouvert) chargerNotifications();
  }, [ouvert]);

  // ✅ NOUVEAU : Recharger les notifications si le panneau est ouvert
  useEffect(() => {
    if (!ouvert) return;
  
    // Rafraîchir toutes les 10 secondes quand le panneau est ouvert
    const interval = setInterval(() => {
        chargerNotifications();
    }, 10000);
     return () => clearInterval(interval);
  }, [ouvert]);
  
  // Fermer au clic extérieur
  useEffect(() => {
    const handleClick = (e) => {
      if (ref.current && !ref.current.contains(e.target)) setOuvert(false);
    };
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

    const chargerNotifications = async () => {
        try {
            const res = await api.get('/notifications');
            setNotifications(res.data.notifications || []);
            setNonLues(res.data.non_lues || 0);
        } catch (err) {
            // ✅ Ne pas rediriger en cas d'erreur 401
            if (err.response?.status === 401) {
                console.warn('⚠️ Session expirée pour les notifications');
                setNotifications([]);
                setNonLues(0);
            } else {
                console.error('Erreur chargement notifications:', err);
            }
        }
    };

    const chargerCompteur = async () => {
        try {
            const res = await api.get('/notifications/compteur');
            setNonLues(res.data.count || 0);
        } catch (err) {
            // ✅ Silencieux, pas de redirection
            setNonLues(0);
        }
    };

  const marquerLue = async (id) => {
    try {
      await api.post(`/notifications/${id}/marquer-lue`);
      setNotifications(prev => prev.map(n => n.id === id ? { ...n, lue: true } : n));
      setNonLues(prev => Math.max(0, prev - 1));
    } catch (err) {}
  };

  const marquerToutesLues = async () => {
    try {
      await api.post('/notifications/marquer-toutes-lues');
      setNonLues(0);
      setNotifications(prev => prev.map(n => ({ ...n, lue: true })));
    } catch (err) {}
  };

  const telechargerPdf = async (id, reference, e) => {
    e.stopPropagation();
    try {
      const res = await api.get(`/notifications/${id}/pdf`, {
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([res.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `Demande-${reference}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (err) {
      alert('Erreur lors du téléchargement du PDF');
    }
  };

  const getIcon = (statut) => {
    if (statut === 'acceptée') return <CheckCircle size={18} color="#059669" />;
    if (statut === 'refusée') return <XCircle size={18} color="#DC2626" />;
    return <AlertCircle size={18} color="#D97706" />;
  };

  const getBgColor = (statut, lue) => {
    if (lue) return colors.card;
    if (statut === 'acceptée') return '#ECFDF5';
    if (statut === 'refusée') return '#FEF2F2';
    return '#FFFBEB';
  };

  return (
    <div ref={ref} style={{ position: 'relative' }}>
      {/* ✅ Bouton cloche avec compteur */}
      <button
        onClick={() => setOuvert(!ouvert)}
        style={{
          width: 38, height: 38, borderRadius: 10,
          border: `1px solid ${colors.cardBorder}`,
          background: colors.card,
          color: colors.textSecondary,
          cursor: 'pointer', position: 'relative',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}
      >
        <Bell size={16} />
        
        {/* ✅ BADGE COMPTEUR */}
        {nonLues > 0 && (
          <span style={{
            position: 'absolute', top: -6, right: -6,
            background: '#EF4444', color: '#FFF',
            borderRadius: '50%', minWidth: 20, height: 20,
            fontSize: 11, fontWeight: 700,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            padding: '0 5px',
            boxShadow: '0 2px 6px rgba(239,68,68,0.4)',
          }}>
            {nonLues > 99 ? '99+' : nonLues}
          </span>
        )}
      </button>

      {/* ✅ PANNEAU DÉROULANT */}
      {ouvert && (
        <div style={{
          position: 'absolute', top: 'calc(100% + 8px)', right: 0,
          width: 380, maxHeight: 480, overflowY: 'auto',
          background: colors.card, borderRadius: 14,
          border: `1px solid ${colors.cardBorder}`,
          boxShadow: '0 10px 40px rgba(0,0,0,0.15)',
          zIndex: 1000,
        }}>
          {/* Header */}
          <div style={{
            padding: '14px 18px', borderBottom: `1px solid ${colors.cardBorder}`,
            display: 'flex', justifyContent: 'space-between', alignItems: 'center',
            position: 'sticky', top: 0, background: colors.card, zIndex: 1,
          }}>
            <div>
              <strong style={{ fontSize: 14, color: colors.text }}>Notifications</strong>
              {nonLues > 0 && (
                <span style={{
                  marginLeft: 8, background: '#EF4444', color: '#FFF',
                  borderRadius: 10, padding: '2px 8px', fontSize: 11, fontWeight: 700,
                }}>
                  {nonLues}
                </span>
              )}
            </div>
            {nonLues > 0 && (
              <button
                onClick={marquerToutesLues}
                style={{
                  background: 'none', border: 'none',
                  color: colors.primary, fontSize: 12, cursor: 'pointer',
                  fontWeight: 500,
                }}
              >
                Tout marquer comme lu
              </button>
            )}
          </div>

          {/* Liste */}
          {notifications.length === 0 ? (
            <div style={{ padding: 40, textAlign: 'center', color: colors.textMuted, fontSize: 13 }}>
              <Bell size={32} color={colors.textMuted} style={{ marginBottom: 12 }} />
              <div>Aucune notification</div>
            </div>
          ) : (
            notifications.map((n) => (
              <div
                key={n.id}
                onClick={() => !n.lue && marquerLue(n.id)}
                style={{
                  padding: '14px 18px',
                  borderBottom: `1px solid ${colors.cardBorder}`,
                  background: getBgColor(n.statut, n.lue),
                  cursor: 'pointer',
                  transition: 'background 0.2s',
                }}
              >
                <div style={{ display: 'flex', gap: 12, alignItems: 'flex-start' }}>
                  <div style={{ marginTop: 2 }}>
                    {getIcon(n.statut)}
                  </div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{
                      fontSize: 13, color: colors.text,
                      fontWeight: n.lue ? 400 : 600,
                      lineHeight: 1.4,
                    }}>
                      {n.message}
                    </div>
                    
                    <div style={{
                      display: 'flex', gap: 12, marginTop: 6,
                      fontSize: 11, color: colors.textSecondary,
                    }}>
                      <span>Réf: {n.reference}</span>
                      {n.date && (
                        <span>
                          {new Date(n.date).toLocaleString('fr-FR', {
                            day: '2-digit', month: '2-digit',
                            hour: '2-digit', minute: '2-digit',
                          })}
                        </span>
                      )}
                    </div>

                    {/* ✅ Bouton télécharger PDF */}
                    {n.a_pdf && (
                      <button
                        onClick={(e) => telechargerPdf(n.id, n.reference, e)}
                        style={{
                          marginTop: 8,
                          display: 'inline-flex', alignItems: 'center', gap: 6,
                          padding: '6px 12px',
                          background: '#10B981', color: '#FFF',
                          border: 'none', borderRadius: 6,
                          fontSize: 11, fontWeight: 600,
                          cursor: 'pointer',
                        }}
                      >
                        <Download size={12} />
                        Télécharger le PDF
                      </button>
                    )}

                    {!n.lue && (
                      <span style={{
                        display: 'inline-block',
                        width: 8, height: 8, borderRadius: '50%',
                        background: '#EF4444',
                        marginLeft: 8,
                      }} />
                    )}
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      )}
    </div>
  );
}