import { useState, useEffect, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../theme/ThemeContext';
import ThemeSwitcher from '../components/ThemeSwitcher';
import api from '../api/axiosConfig';
import {
  Clock, CheckCircle, XCircle, FileText, TrendingUp,
  LogOut, Plus, Home, BarChart3, Download, ArrowLeft,
  Search
} from 'lucide-react';
import logo from '../assets/image/logo.png';

const COULEURS_STATUT = {
  'en_attente': { couleur: '#F59E0B', trackColor: '#FEF3C7', label: 'En attente', icon: Clock },
  'acceptée':   { couleur: '#10B981', trackColor: '#D1FAE5', label: 'Acceptées', icon: CheckCircle },
  'refusée':    { couleur: '#EF4444', trackColor: '#FEE2E2', label: 'Refusées', icon: XCircle },
  'en cours':   { couleur: '#3B82F6', trackColor: '#DBEAFE', label: 'En cours', icon: TrendingUp },
};

export default function Statistiques() {
  const { utilisateur, deconnecter } = useAuth();
  const { colors } = useTheme();
  const navigate = useNavigate();

  // ✅ État local — plus de props !
  const [demandes, setDemandes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [periodeStats, setPeriodeStats] = useState('12mois');

  // ✅ Sécurisation : toujours un tableau
  const demandesSafe = Array.isArray(demandes) ? demandes : [];

  useEffect(() => {
    if (!utilisateur) {
      navigate('/connexion');
      return;
    }
    chargerDemandes();
  }, [utilisateur, navigate]);

  const chargerDemandes = async () => {
    try {
      setChargement(true);
      const res = await api.get('/demandes/mes-demandes');
      const data = res.data?.demandes || res.data || [];
      setDemandes(Array.isArray(data) ? data : []);
    } catch (err) {
      console.error('Erreur chargement demandes:', err);
      setDemandes([]);
    } finally {
      setChargement(false);
    }
  };

  const gererDeconnexion = () => {
    deconnecter();
    navigate('/connexion');
  };

  // ═══════════ CALCULS STATS ═══════════
  const total = demandesSafe.length || 1;

  const statuts = Object.keys(COULEURS_STATUT).map(key => {
    const count = demandesSafe.filter(d => d.statut === key).length;
    return {
      key,
      count,
      pourcentage: Math.round((count / total) * 100),
      ...COULEURS_STATUT[key],
    };
  }).filter(s => s.count > 0);

  const rayon = 60;
  const circonference = 2 * Math.PI * rayon;
  let offsetCumule = 0;

  const calculerAnalytique = (periode) => {
    const maintenant = new Date();
    let labels = [];
    let data = [];

    switch (periode) {
      case '24h': {
        for (let i = 23; i >= 0; i--) {
          const h = new Date(maintenant.getTime() - i * 60 * 60 * 1000);
          const label = h.getHours() + 'h';
          const count = demandesSafe.filter(d => {
            if (!d.created_at) return false;
            const dc = new Date(d.created_at);
            return dc.toDateString() === h.toDateString() && dc.getHours() === h.getHours();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
      case '7jours': {
        for (let i = 6; i >= 0; i--) {
          const j = new Date(maintenant.getTime() - i * 24 * 60 * 60 * 1000);
          const label = j.toLocaleDateString('fr-FR', { weekday: 'short' });
          const count = demandesSafe.filter(d => {
            if (!d.created_at) return false;
            return new Date(d.created_at).toDateString() === j.toDateString();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
      case '30jours': {
        for (let i = 29; i >= 0; i--) {
          const j = new Date(maintenant.getTime() - i * 24 * 60 * 60 * 1000);
          const label = j.getDate() + '/' + (j.getMonth() + 1);
          const count = demandesSafe.filter(d => {
            if (!d.created_at) return false;
            return new Date(d.created_at).toDateString() === j.toDateString();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
      case '12mois':
      default: {
        for (let i = 11; i >= 0; i--) {
          const m = new Date(maintenant.getFullYear(), maintenant.getMonth() - i, 1);
          const label = m.toLocaleDateString('fr-FR', { month: 'short' }) + ' ' + String(m.getFullYear()).slice(2);
          const count = demandesSafe.filter(d => {
            if (!d.created_at) return false;
            const dc = new Date(d.created_at);
            return dc.getFullYear() === m.getFullYear() && dc.getMonth() === m.getMonth();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
    }
    return { labels, data };
  };

  const { labels, data } = calculerAnalytique(periodeStats);
  const maxValue = Math.max(...data, 1);

  // ═══════════ COMPTEURS CARTES ═══════════
  const statsCartes = {
    total: demandesSafe.length,
    en_attente: demandesSafe.filter(d => d.statut === 'en_attente').length,
    acceptees: demandesSafe.filter(d => d.statut === 'acceptée').length,
    refusees: demandesSafe.filter(d => d.statut === 'refusée').length,
  };

  if (chargement) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: colors.bg }}>
        <div style={{ fontSize: 16, color: colors.textSecondary }}>Chargement...</div>
      </div>
    );
  }

  return (
    <div style={{ display: 'flex', minHeight: '100vh', background: colors.bg, color: colors.text, fontFamily: 'Inter, sans-serif' }}>

      {/* SIDEBAR */}
      <div style={{ width: 240, background: colors.sidebar, borderRight: `1px solid ${colors.sidebarBorder}`, display: 'flex', flexDirection: 'column', padding: '24px 0', position: 'fixed', height: '100vh' }}>
        <div style={{ padding: '0 20px 24px', borderBottom: `1px solid ${colors.sidebarBorder}` }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <img src={logo} alt="Logo" style={{ width: 40, height: 40, objectFit: 'contain' }} />
            <div>
              <div style={{ fontSize: 13, fontWeight: 600, color: colors.text }}>Portail Citoyen</div>
              <div style={{ fontSize: 11, color: colors.textSecondary }}>État Civil</div>
            </div>
          </div>
        </div>

        <nav style={{ flex: 1, padding: '16px 12px' }}>
          {[
            { icon: Home, label: 'Tableau de bord', actif: false, lien: '/tableau-de-bord' },
            { icon: FileText, label: 'Mes demandes', actif: false, lien: '/mes-demandes' },
            { icon: Plus, label: 'Nouvelle demande', actif: false, lien: '/nouvelle-demande' },
            { icon: BarChart3, label: 'Statistiques', actif: true, lien: '/statistiques' },
            { icon: Download, label: 'Mes téléchargements', actif: false, lien: '/mes-telechargements' },
          ].map(({ icon: Icon, label, actif, lien }) => (
            <Link key={label} to={lien} style={{ textDecoration: 'none' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, marginBottom: 4, background: actif ? colors.primaryLight : 'transparent', color: actif ? colors.primary : colors.textSecondary, fontWeight: actif ? 600 : 400 }}>
                <Icon size={16} />
                <span style={{ fontSize: 13 }}>{label}</span>
              </div>
            </Link>
          ))}
        </nav>

        <div style={{ padding: '16px 12px', borderTop: `1px solid ${colors.sidebarBorder}` }}>
          <button onClick={gererDeconnexion} style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: colors.danger, cursor: 'pointer', fontSize: 13 }}>
            <LogOut size={16} />
            Se déconnecter
          </button>
        </div>
      </div>

      {/* CONTENU */}
      <div style={{ marginLeft: 240, flex: 1, padding: '32px' }}>

        {/* HEADER */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 32 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <Link to="/tableau-de-bord" style={{ color: colors.primary, textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
              <ArrowLeft size={18} />
              <span style={{ fontSize: 13 }}>Retour</span>
            </Link>
            <h1 style={{ margin: 0, fontSize: 24, fontWeight: 700, color: colors.text }}>Statistiques</h1>
          </div>

          <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
            <ThemeSwitcher />
            <Link to="/nouvelle-demande">
              <button style={{ padding: '10px 20px', borderRadius: 8, border: 'none', background: colors.primaryGradient, color: '#FFF', fontSize: 13, fontWeight: 600, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 8 }}>
                <Plus size={16} /> Nouvelle demande
              </button>
            </Link>
          </div>
        </div>

        {/* 4 CARTES */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: 16, marginBottom: 24 }}>
          {[
            { label: 'Total demandes', value: statsCartes.total, icon: FileText, color: colors.primary, bg: colors.primaryLight },
            { label: 'En attente', value: statsCartes.en_attente, icon: Clock, color: '#F59E0B', bg: '#FEF3C7' },
            { label: 'Acceptées', value: statsCartes.acceptees, icon: CheckCircle, color: '#10B981', bg: '#D1FAE5' },
            { label: 'Refusées', value: statsCartes.refusees, icon: XCircle, color: '#EF4444', bg: '#FEE2E2' },
          ].map((stat, index) => (
            <div key={index} style={{ background: colors.card, padding: '16px 20px', borderRadius: 12, border: `1px solid ${colors.cardBorder}` }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <div style={{ padding: 8, borderRadius: 8, background: stat.bg, color: stat.color }}>
                  <stat.icon size={18} />
                </div>
                <div>
                  <div style={{ fontSize: 22, fontWeight: 700, color: colors.text }}>{stat.value}</div>
                  <div style={{ fontSize: 12, color: colors.textSecondary }}>{stat.label}</div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* DONUT */}
        <div style={{ background: colors.card, borderRadius: 14, border: `1px solid ${colors.cardBorder}`, padding: 24, marginBottom: 24 }}>
          <h3 style={{ margin: '0 0 4px 0', fontSize: 16, fontWeight: 700, color: colors.text }}>Classements par statut</h3>
          <p style={{ margin: '0 0 24px 0', fontSize: 12, color: colors.textSecondary }}>Répartition des statuts de demande</p>

          <div style={{ display: 'flex', alignItems: 'center', gap: 40, flexWrap: 'wrap' }}>
            <div style={{ position: 'relative', width: 180, height: 180, flexShrink: 0 }}>
              <svg width="180" height="180" viewBox="0 0 180 180">
                <circle cx="90" cy="90" r={rayon} fill="none" stroke="#F3F4F6" strokeWidth="22" />
                {statuts.map((s) => {
                  const dash = (s.pourcentage / 100) * circonference;
                  const offset = -offsetCumule;
                  offsetCumule += dash;
                  return (
                    <circle
                      key={s.key}
                      cx="90" cy="90" r={rayon}
                      fill="none"
                      stroke={s.couleur}
                      strokeWidth="22"
                      strokeDasharray={`${dash} ${circonference - dash}`}
                      strokeDashoffset={offset}
                      transform="rotate(-90 90 90)"
                    />
                  );
                })}
                <text x="90" y="86" textAnchor="middle" fontSize="26" fontWeight="700" fill="#111827">
                  {demandesSafe.length}
                </text>
                <text x="90" y="106" textAnchor="middle" fontSize="11" fill="#6B7280">
                  Demandes
                </text>
              </svg>
            </div>

            <div style={{ flex: 1, minWidth: 200 }}>
              {statuts.map((s) => (
                <div key={s.key} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 0', borderBottom: '1px solid #F3F4F6' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <span style={{ width: 12, height: 12, borderRadius: '50%', background: s.couleur }} />
                    <span style={{ fontSize: 13, color: colors.text }}>{s.label}</span>
                  </div>
                  <span style={{ fontSize: 14, fontWeight: 700, color: colors.text }}>{s.pourcentage} %</span>
                </div>
              ))}
              {statuts.length === 0 && (
                <div style={{ padding: 20, textAlign: 'center', color: colors.textMuted, fontSize: 13 }}>
                  Aucune donnée disponible
                </div>
              )}
            </div>
          </div>
        </div>

        {/* ANALYTIQUE */}
        <div style={{ background: colors.card, borderRadius: 14, border: `1px solid ${colors.cardBorder}`, padding: 24 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 24, flexWrap: 'wrap', gap: 12 }}>
            <div>
              <h3 style={{ margin: '0 0 4px 0', fontSize: 16, fontWeight: 700, color: colors.text }}>Analytique</h3>
              <p style={{ margin: 0, fontSize: 12, color: colors.textSecondary }}>
                Analyse des demandes sur les {periodeStats === '24h' ? '24 dernières heures' : periodeStats === '7jours' ? '7 derniers jours' : periodeStats === '30jours' ? '30 derniers jours' : '12 derniers mois'}
              </p>
            </div>

            <div style={{ display: 'flex', background: '#F3F4F6', borderRadius: 10, padding: 4 }}>
              {[
                { key: '12mois', label: '12 mois' },
                { key: '30jours', label: '30 jours' },
                { key: '7jours', label: '7 jours' },
                { key: '24h', label: '24 heures' },
              ].map(({ key, label }) => (
                <button
                  key={key}
                  onClick={() => setPeriodeStats(key)}
                  style={{
                    padding: '6px 14px', borderRadius: 8, border: 'none',
                    background: periodeStats === key ? '#FFFFFF' : 'transparent',
                    color: periodeStats === key ? '#111827' : '#6B7280',
                    fontSize: 12, fontWeight: periodeStats === key ? 600 : 400,
                    cursor: 'pointer',
                    boxShadow: periodeStats === key ? '0 1px 3px rgba(0,0,0,0.1)' : 'none'
                  }}
                >
                  {label}
                </button>
              ))}
            </div>
          </div>

          <div style={{ position: 'relative', height: 260, paddingLeft: 40, paddingBottom: 30 }}>
            {[0, 0.25, 0.5, 0.75, 1].map((ratio) => {
              const valeur = Math.round(maxValue * (1 - ratio));
              return (
                <div key={ratio} style={{ position: 'absolute', left: 0, right: 0, top: `${ratio * 100}%`, display: 'flex', alignItems: 'center' }}>
                  <span style={{ fontSize: 11, color: '#9CA3AF', width: 35, textAlign: 'right', paddingRight: 6 }}>{valeur}</span>
                  <div style={{ flex: 1, height: 1, background: '#F3F4F6' }} />
                </div>
              );
            })}

            <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: 6, height: 220, paddingLeft: 40 }}>
              {data.map((valeur, i) => {
                const hauteur = (valeur / maxValue) * 200;
                return (
                  <div key={i} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
                    <div style={{ fontSize: 10, fontWeight: 600, color: '#4F46E5', height: 14 }}>
                      {valeur > 0 ? valeur : ''}
                    </div>
                    <div
                      title={`${labels[i]} : ${valeur} demande${valeur > 1 ? 's' : ''}`}
                      style={{
                        width: '100%', maxWidth: 40,
                        height: Math.max(hauteur, 4),
                        background: valeur > 0 ? 'linear-gradient(180deg,#6366F1,#8B5CF6)' : '#E5E7EB',
                        borderRadius: 6, transition: 'all 0.4s ease',
                      }}
                    />
                  </div>
                );
              })}
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', paddingLeft: 40, marginTop: 6, gap: 6 }}>
              {labels.map((label, i) => (
                <div key={i} style={{ flex: 1, textAlign: 'center', fontSize: 10, color: '#6B7280', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                  {label}
                </div>
              ))}
            </div>
          </div>

          <div style={{ display: 'flex', justifyContent: 'space-around', marginTop: 20, paddingTop: 16, borderTop: '1px solid #F3F4F6' }}>
            <div style={{ textAlign: 'center' }}>
              <div style={{ fontSize: 20, fontWeight: 700, color: '#4F46E5' }}>{demandesSafe.length}</div>
              <div style={{ fontSize: 11, color: colors.textSecondary }}>Total</div>
            </div>
            <div style={{ textAlign: 'center' }}>
              <div style={{ fontSize: 20, fontWeight: 700, color: '#10B981' }}>{demandesSafe.filter(d => d.statut === 'acceptée').length}</div>
              <div style={{ fontSize: 11, color: colors.textSecondary }}>Acceptées</div>
            </div>
            <div style={{ textAlign: 'center' }}>
              <div style={{ fontSize: 20, fontWeight: 700, color: '#F59E0B' }}>{demandesSafe.filter(d => d.statut === 'en_attente').length}</div>
              <div style={{ fontSize: 11, color: colors.textSecondary }}>En attente</div>
            </div>
            <div style={{ textAlign: 'center' }}>
              <div style={{ fontSize: 20, fontWeight: 700, color: '#EF4444' }}>{demandesSafe.filter(d => d.statut === 'refusée').length}</div>
              <div style={{ fontSize: 11, color: colors.textSecondary }}>Refusées</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}