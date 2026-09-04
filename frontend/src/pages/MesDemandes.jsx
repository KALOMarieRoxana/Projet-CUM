import { useEffect, useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import {
  FileText, Clock, CheckCircle, XCircle, Plus,
  LogOut, Home, Zap, Shield, AlertCircle,
  ChevronRight, Filter, Search, RefreshCw
} from 'lucide-react';

const THEMES = {
  sombre: {
    bg: '#0F0F0F', sidebar: '#161616', carte: '#161616',
    border: '#262626', texte: '#F9FAFB', texteSec: '#6B7280',
    texteThree: '#9CA3AF', input: '#1F1F1F', hover: 'rgba(99,102,241,0.15)',
    separateur: '#1F1F1F', inputBg: '#1A1A1A',
  },
  clair: {
    bg: '#F3F4F6', sidebar: '#FFFFFF', carte: '#FFFFFF',
    border: '#E5E7EB', texte: '#111827', texteSec: '#6B7280',
    texteThree: '#4B5563', input: '#F9FAFB', hover: 'rgba(99,102,241,0.08)',
    separateur: '#F3F4F6', inputBg: '#F9FAFB',
  }
};

const COULEURS_STATUT = {
  'en attente': { bg: 'rgba(245,158,11,0.12)', texte: '#F59E0B', border: '#F59E0B22', icon: Clock },
  'acceptée':   { bg: 'rgba(16,185,129,0.12)',  texte: '#10B981', border: '#10B98122', icon: CheckCircle },
  'refusée':    { bg: 'rgba(239,68,68,0.12)',   texte: '#EF4444', border: '#EF444422', icon: XCircle },
};

const LABELS_TYPE = {
  naissance: 'Acte de naissance',
  mariage:   'Acte de mariage',
  deces:     'Acte de décès',
  divorce:   'Acte de divorce',
};

export default function MesDemandes() {
  const { utilisateur, deconnecter } = useAuth();
  const navigate = useNavigate();
  const [demandes, setDemandes] = useState([]);
  const [demandesFiltrees, setDemandesFiltrees] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [recherche, setRecherche] = useState('');
  const [filtreStatut, setFiltreStatut] = useState('tous');
  const [filtreService, setFiltreService] = useState('tous');
  const [modeTheme, setModeTheme] = useState(() => localStorage.getItem('theme') || 'sombre');
  const T = THEMES[modeTheme];

  useEffect(() => {
    if (!utilisateur) { navigate('/connexion'); return; }
    chargerDemandes();
  }, [utilisateur]);

  useEffect(() => {
    filtrer();
  }, [demandes, recherche, filtreStatut, filtreService]);

  const chargerDemandes = async () => {
    try {
      setChargement(true);
      setErreur('');
      const { data } = await api.get('/demandes/mes-demandes');
      setDemandes(data.demandes);
    } catch (err) {
      setErreur('Impossible de charger vos demandes.');
    } finally {
      setChargement(false);
    }
  };

  const filtrer = () => {
    let liste = [...demandes];
    if (filtreStatut !== 'tous') {
      liste = liste.filter(d => d.statut === filtreStatut);
    }
    if (filtreService !== 'tous') {
      liste = liste.filter(d => (d.service || 'standard') === filtreService);
    }
    if (recherche.trim()) {
      const q = recherche.toLowerCase();
      liste = liste.filter(d =>
        (d.type_acte?.nom || '').toLowerCase().includes(q) ||
        String(d.id_demande).includes(q)
      );
    }
    setDemandesFiltrees(liste);
  };

  const gererDeconnexion = () => { deconnecter(); navigate('/connexion'); };

  if (!utilisateur) return null;

  const total       = demandes.length;
  const enAttente   = demandes.filter(d => d.statut === 'en attente').length;
  const acceptees   = demandes.filter(d => d.statut === 'acceptée').length;
  const refusees    = demandes.filter(d => d.statut === 'refusée').length;

  return (
    <div style={{ display: 'flex', minHeight: '100vh', background: T.bg, color: T.texte, fontFamily: 'Inter, sans-serif', transition: 'all 0.3s ease' }}>

      {/* ===== SIDEBAR ===== */}
      <div style={{ width: 240, background: T.sidebar, borderRight: `1px solid ${T.border}`, display: 'flex', flexDirection: 'column', padding: '24px 0', position: 'fixed', height: '100vh', transition: 'all 0.3s ease' }}>
        <div style={{ padding: '0 20px 24px', borderBottom: `1px solid ${T.border}` }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <div style={{ width: 36, height: 36, borderRadius: 10, background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 14, fontWeight: 700, color: '#fff' }}>EC</div>
            <div>
              <div style={{ fontSize: 13, fontWeight: 600, color: T.texte }}>Portail Citoyen</div>
              <div style={{ fontSize: 11, color: T.texteSec }}>État Civil</div>
            </div>
          </div>
        </div>

        <nav style={{ flex: 1, padding: '16px 12px' }}>
          {[
            { icon: Home,     label: 'Tableau de bord',  actif: false, lien: '/tableau-de-bord' },
            { icon: FileText, label: 'Mes demandes',      actif: true,  lien: '/mes-demandes' },
            { icon: Plus,     label: 'Nouvelle demande',  actif: false, lien: '/nouvelle-demande' },
          ].map(({ icon: Icon, label, actif, lien }) => (
            <Link key={label} to={lien} style={{ textDecoration: 'none' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, marginBottom: 4, background: actif ? T.hover : 'transparent', color: actif ? '#818CF8' : T.texteSec, cursor: 'pointer', transition: 'all 0.2s' }}>
                <Icon size={16} />
                <span style={{ fontSize: 13, fontWeight: actif ? 600 : 400 }}>{label}</span>
              </div>
            </Link>
          ))}
        </nav>

        <div style={{ padding: '16px 12px', borderTop: `1px solid ${T.border}` }}>
          <button onClick={gererDeconnexion} style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#EF4444', cursor: 'pointer', fontSize: 13 }}>
            <LogOut size={16} /> Se déconnecter
          </button>
        </div>
      </div>

      {/* ===== CONTENU PRINCIPAL ===== */}
      <div style={{ marginLeft: 240, flex: 1, padding: '32px' }}>

        {/* Header */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 }}>
          <div>
            <div style={{ fontSize: 12, color: T.texteSec, marginBottom: 4 }}>Espace citoyen</div>
            <h1 style={{ margin: 0, fontSize: 22, fontWeight: 700, color: T.texte }}>Mes demandes</h1>
          </div>
          <div style={{ display: 'flex', gap: 10 }}>
            <button onClick={chargerDemandes} style={{ width: 38, height: 38, borderRadius: 10, border: `1px solid ${T.border}`, background: T.sidebar, color: T.texteSec, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }} title="Actualiser">
              <RefreshCw size={15} />
            </button>
            <Link to="/nouvelle-demande" style={{ textDecoration: 'none' }}>
              <button style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '10px 18px', borderRadius: 10, border: 'none', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', color: '#fff', cursor: 'pointer', fontSize: 13, fontWeight: 600 }}>
                <Plus size={15} /> Nouvelle demande
              </button>
            </Link>
          </div>
        </div>

        {erreur && (
          <div style={{ padding: '12px 16px', borderRadius: 10, background: 'rgba(239,68,68,0.1)', border: '1px solid #EF4444', color: '#EF4444', fontSize: 13, marginBottom: 20, display: 'flex', alignItems: 'center', gap: 8 }}>
            <AlertCircle size={15} /> {erreur}
          </div>
        )}

        {/* ===== STATISTIQUES ===== */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 14, marginBottom: 24 }}>
          {[
            { label: 'Total',      valeur: total,      couleur: '#6366F1', bg: 'rgba(99,102,241,0.12)',  icon: FileText     },
            { label: 'En attente', valeur: enAttente,  couleur: '#F59E0B', bg: 'rgba(245,158,11,0.12)', icon: Clock        },
            { label: 'Acceptées',  valeur: acceptees,  couleur: '#10B981', bg: 'rgba(16,185,129,0.12)', icon: CheckCircle  },
            { label: 'Refusées',   valeur: refusees,   couleur: '#EF4444', bg: 'rgba(239,68,68,0.12)',  icon: XCircle      },
          ].map(({ label, valeur, couleur, bg, icon: Icon }) => (
            <div key={label} style={{ background: T.carte, borderRadius: 12, padding: '18px 20px', border: `1px solid ${T.border}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center', transition: 'all 0.3s ease' }}>
              <div>
                <div style={{ fontSize: 11, color: T.texteSec, marginBottom: 6 }}>{label}</div>
                <div style={{ fontSize: 26, fontWeight: 700, color: T.texte }}>{valeur}</div>
              </div>
              <div style={{ width: 38, height: 38, borderRadius: 10, background: bg, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <Icon size={17} color={couleur} />
              </div>
            </div>
          ))}
        </div>

        {/* ===== FILTRES ===== */}
        <div style={{ background: T.carte, borderRadius: 12, border: `1px solid ${T.border}`, padding: '16px 20px', marginBottom: 16, display: 'flex', gap: 12, alignItems: 'center', flexWrap: 'wrap', transition: 'all 0.3s ease' }}>
          <Filter size={14} color={T.texteSec} />

          {/* Recherche */}
          <div style={{ position: 'relative', flex: 1, minWidth: 180 }}>
            <Search size={13} color={T.texteSec} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)' }} />
            <input
              type="text"
              placeholder="Rechercher par type ou numéro…"
              value={recherche}
              onChange={e => setRecherche(e.target.value)}
              style={{ width: '100%', padding: '8px 12px 8px 32px', borderRadius: 8, border: `1px solid ${T.border}`, background: T.inputBg, color: T.texte, fontSize: 13, outline: 'none', boxSizing: 'border-box' }}
            />
          </div>

          {/* Filtre statut */}
          <select value={filtreStatut} onChange={e => setFiltreStatut(e.target.value)} style={{ padding: '8px 12px', borderRadius: 8, border: `1px solid ${T.border}`, background: T.inputBg, color: T.texte, fontSize: 13, cursor: 'pointer', outline: 'none' }}>
            <option value="tous">Tous les statuts</option>
            <option value="en attente">En attente</option>
            <option value="acceptée">Acceptées</option>
            <option value="refusée">Refusées</option>
          </select>

          {/* Filtre service */}
          <select value={filtreService} onChange={e => setFiltreService(e.target.value)} style={{ padding: '8px 12px', borderRadius: 8, border: `1px solid ${T.border}`, background: T.inputBg, color: T.texte, fontSize: 13, cursor: 'pointer', outline: 'none' }}>
            <option value="tous">Tous les services</option>
            <option value="standard">Standard</option>
            <option value="express">Express</option>
          </select>

          {(filtreStatut !== 'tous' || filtreService !== 'tous' || recherche) && (
            <button onClick={() => { setFiltreStatut('tous'); setFiltreService('tous'); setRecherche(''); }} style={{ padding: '8px 12px', borderRadius: 8, border: `1px solid ${T.border}`, background: 'transparent', color: '#EF4444', fontSize: 12, cursor: 'pointer' }}>
              Réinitialiser
            </button>
          )}
        </div>

        {/* ===== LISTE DES DEMANDES ===== */}
        <div style={{ background: T.carte, borderRadius: 14, border: `1px solid ${T.border}`, overflow: 'hidden', transition: 'all 0.3s ease' }}>
          <div style={{ padding: '16px 22px', borderBottom: `1px solid ${T.border}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <span style={{ fontSize: 14, fontWeight: 600, color: T.texte }}>
              {demandesFiltrees.length} demande{demandesFiltrees.length !== 1 ? 's' : ''}
              {(filtreStatut !== 'tous' || filtreService !== 'tous' || recherche) && ' (filtrées)'}
            </span>
          </div>

          {chargement ? (
            <div style={{ padding: 40, textAlign: 'center', color: T.texteSec, fontSize: 13 }}>Chargement…</div>
          ) : demandesFiltrees.length === 0 ? (
            <div style={{ padding: 48, textAlign: 'center' }}>
              <FileText size={36} color={T.border} style={{ marginBottom: 14 }} />
              <div style={{ fontSize: 14, fontWeight: 500, color: T.texte, marginBottom: 6 }}>
                {demandes.length === 0 ? 'Aucune demande pour le moment' : 'Aucun résultat'}
              </div>
              <div style={{ fontSize: 13, color: T.texteSec, marginBottom: 16 }}>
                {demandes.length === 0 ? 'Soumettez votre première demande d\'état civil.' : 'Essayez de modifier vos filtres.'}
              </div>
              {demandes.length === 0 && (
                <Link to="/nouvelle-demande" style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '9px 16px', borderRadius: 9, background: 'rgba(99,102,241,0.12)', color: '#818CF8', textDecoration: 'none', fontSize: 13, fontWeight: 500 }}>
                  <Plus size={14} /> Créer une demande
                </Link>
              )}
            </div>
          ) : (
            <div>
              {/* En-tête tableau */}
              <div style={{ display: 'grid', gridTemplateColumns: '60px 1fr 140px 120px 110px 110px', gap: 12, padding: '10px 22px', borderBottom: `1px solid ${T.border}` }}>
                {['N°', 'Type d\'acte', 'Service', 'Prix', 'Statut', 'Date'].map(col => (
                  <div key={col} style={{ fontSize: 11, fontWeight: 600, color: T.texteSec, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{col}</div>
                ))}
              </div>

              {/* Lignes */}
              {demandesFiltrees.map((d, i) => {
                const config = COULEURS_STATUT[d.statut] || COULEURS_STATUT['en attente'];
                const IconStatut = config.icon;
                const service = d.service || 'standard';
                const nomType = d.type_acte?.nom || LABELS_TYPE[d.type_acte?.type_acte] || 'Demande';
                const date = d.created_at ? new Date(d.created_at).toLocaleDateString('fr-FR') : '—';

                return (
                  <div key={d.id_demande} style={{ display: 'grid', gridTemplateColumns: '60px 1fr 140px 120px 110px 110px', gap: 12, padding: '14px 22px', borderBottom: i < demandesFiltrees.length - 1 ? `1px solid ${T.separateur}` : 'none', alignItems: 'center', transition: 'background 0.15s ease' }}
                    onMouseEnter={e => e.currentTarget.style.background = T.hover}
                    onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                  >
                    {/* N° */}
                    <div style={{ fontSize: 13, fontWeight: 600, color: '#818CF8' }}>#{d.id_demande}</div>

                    {/* Type */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                      <div style={{ width: 34, height: 34, borderRadius: 8, background: config.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                        <IconStatut size={15} color={config.texte} />
                      </div>
                      <span style={{ fontSize: 13, fontWeight: 500, color: T.texte }}>{nomType}</span>
                    </div>

                    {/* Service */}
                    <div>
                      {service === 'express' ? (
                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 11, padding: '4px 10px', borderRadius: 20, background: 'rgba(245,158,11,0.12)', color: '#F59E0B', fontWeight: 600 }}>
                          <Zap size={10} /> Express
                        </span>
                      ) : (
                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 11, padding: '4px 10px', borderRadius: 20, background: 'rgba(99,102,241,0.1)', color: '#818CF8', fontWeight: 600 }}>
                          <Shield size={10} /> Standard
                        </span>
                      )}
                    </div>

                    {/* Prix */}
                    <div style={{ fontSize: 13, fontWeight: 600, color: T.texte }}>
                      {Number(d.prix || 0).toLocaleString('fr-FR')} Ar
                    </div>

                    {/* Statut */}
                    <div>
                      <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 11, padding: '4px 10px', borderRadius: 20, background: config.bg, color: config.texte, fontWeight: 600, border: `1px solid ${config.border}` }}>
                        <IconStatut size={10} /> {d.statut}
                      </span>
                    </div>

                    {/* Date */}
                    <div style={{ fontSize: 12, color: T.texteSec }}>{date}</div>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {/* ===== CASES PAR STATUT (en bas) ===== */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16, marginTop: 24 }}>
          {[
            { statut: 'en attente', label: 'En attente',  icon: Clock },
            { statut: 'acceptée',   label: 'Acceptées',   icon: CheckCircle },
            { statut: 'refusée',    label: 'Refusées',    icon: XCircle },
          ].map(({ statut, label, icon: Icon }) => {
            const config = COULEURS_STATUT[statut];
            const filtrees = demandes.filter(d => d.statut === statut);
            return (
              <div key={statut} style={{ background: T.carte, borderRadius: 14, border: `1px solid ${T.border}`, overflow: 'hidden', transition: 'all 0.3s ease' }}>
                <div style={{ padding: '14px 18px', borderBottom: `1px solid ${T.border}`, display: 'flex', alignItems: 'center', gap: 8 }}>
                  <Icon size={14} color={config.texte} />
                  <span style={{ fontSize: 13, fontWeight: 600, color: config.texte }}>{label}</span>
                  <span style={{ marginLeft: 'auto', fontSize: 11, padding: '2px 8px', borderRadius: 20, background: config.bg, color: config.texte, fontWeight: 700 }}>{filtrees.length}</span>
                </div>
                <div style={{ maxHeight: 220, overflowY: 'auto' }}>
                  {filtrees.length === 0 ? (
                    <div style={{ padding: '20px', fontSize: 12, color: T.texteSec, textAlign: 'center' }}>Aucune demande</div>
                  ) : filtrees.map((d) => {
                    const service = d.service || 'standard';
                    return (
                      <div key={d.id_demande} style={{ padding: '10px 18px', borderBottom: `1px solid ${T.separateur}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div>
                          <div style={{ fontSize: 12, fontWeight: 500, color: T.texte }}>
                            {d.type_acte?.nom || LABELS_TYPE[d.type_acte?.type_acte] || 'Demande'}
                          </div>
                          <div style={{ fontSize: 11, color: T.texteSec, display: 'flex', gap: 8, marginTop: 2 }}>
                            <span>#{d.id_demande}</span>
                            {service === 'express' ? (
                              <span style={{ color: '#F59E0B', display: 'flex', alignItems: 'center', gap: 2 }}><Zap size={9} />Express</span>
                            ) : (
                              <span style={{ color: '#818CF8', display: 'flex', alignItems: 'center', gap: 2 }}><Shield size={9} />Standard</span>
                            )}
                          </div>
                        </div>
                        <span style={{ fontSize: 11, color: T.texteThree }}>{Number(d.prix || 0).toLocaleString('fr-FR')} Ar</span>
                      </div>
                    );
                  })}
                </div>
              </div>
            );
          })}
        </div>

      </div>
    </div>
  );
}
