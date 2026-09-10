import { useEffect, useState, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import {
  FileText, Clock, CheckCircle, XCircle, LogOut, Plus,
  User, Phone, MapPin, Mail, Bell, ChevronRight,
  Zap, Shield, AlertCircle, Home, ChevronDown,
  UserCircle, Award, Key, X, Eye, EyeOff
} from 'lucide-react';
import logo from '../assets/image/logo.png';

const COULEURS_STATUT = {
  'en attente': { bg: '#FEF3C7', texte: '#92400E', border: '#F59E0B', icon: Clock },
  'acceptée':   { bg: '#D1FAE5', texte: '#065F46', border: '#10B981', icon: CheckCircle },
  'refusée':    { bg: '#FEE2E2', texte: '#991B1B', border: '#EF4444', icon: XCircle },
};

const LABELS_TYPE = {
  naissance: 'Acte de naissance',
  mariage: 'Acte de mariage',
  deces: 'Acte de décès',
  divorce: 'Acte de divorce',
};

export default function Dashboard() {
  const { utilisateur, deconnecter } = useAuth();
  const navigate = useNavigate();
  const [profilDetaille, setProfilDetaille] = useState(null);
  const [mesDemandes, setMesDemandes] = useState([]);
  const [erreur, setErreur] = useState('');
  const [chargement, setChargement] = useState(true);
  const [menuProfilOuvert, setMenuProfilOuvert] = useState(false);
  const [modalCompteOuvert, setModalCompteOuvert] = useState(false);
  const [modalChangerMdpOuvert, setModalChangerMdpOuvert] = useState(false);
  const [ancienMotDePasse, setAncienMotDePasse] = useState('');
  const [nouveauMotDePasse, setNouveauMotDePasse] = useState('');
  const [confirmerMotDePasse, setConfirmerMotDePasse] = useState('');
  const [afficherAncienMdp, setAfficherAncienMdp] = useState(false);
  const [afficherNouveauMdp, setAfficherNouveauMdp] = useState(false);
  const [afficherConfirmerMdp, setAfficherConfirmerMdp] = useState(false);
  const [erreurMdp, setErreurMdp] = useState('');
  const [succesMdp, setSuccesMdp] = useState('');
  const [chargementMdp, setChargementMdp] = useState(false);
  const menuRef = useRef(null);
  const modalRef = useRef(null);

  useEffect(() => {
    if (!utilisateur) { navigate('/connexion'); return; }
    chargerDonnees();
  }, [utilisateur, navigate]);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuProfilOuvert(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const chargerDonnees = async () => {
    try {
      setChargement(true);
      const [resProfil, resDemandes] = await Promise.all([
        api.get('/auth/profil'),
        api.get('/demandes/mes-demandes')
      ]);
      setProfilDetaille(resProfil.data.utilisateur);
      setMesDemandes(resDemandes.data.demandes);
    } catch (err) {
      setErreur('Impossible de charger vos données.');
    } finally {
      setChargement(false);
    }
  };

  const gererDeconnexion = () => {
    deconnecter();
    navigate('/connexion');
    setMenuProfilOuvert(false);
  };

  const toggleMenuProfil = () => setMenuProfilOuvert(!menuProfilOuvert);

  const ouvrirModalCompte = () => {
    setModalCompteOuvert(true);
    setMenuProfilOuvert(false);
  };

  const fermerModalCompte = () => setModalCompteOuvert(false);

  const ouvrirModalChangerMdp = () => {
    setModalChangerMdpOuvert(true);
    setModalCompteOuvert(false);
    setAncienMotDePasse('');
    setNouveauMotDePasse('');
    setConfirmerMotDePasse('');
    setErreurMdp('');
    setSuccesMdp('');
  };

  const fermerModalChangerMdp = () => {
    setModalChangerMdpOuvert(false);
    setAncienMotDePasse('');
    setNouveauMotDePasse('');
    setConfirmerMotDePasse('');
    setErreurMdp('');
    setSuccesMdp('');
  };

  const handleChangerMotDePasse = async (e) => {
    e.preventDefault();
    setErreurMdp('');
    setSuccesMdp('');

    if (!ancienMotDePasse || !nouveauMotDePasse || !confirmerMotDePasse) {
      setErreurMdp('Tous les champs sont obligatoires.');
      return;
    }
    if (nouveauMotDePasse.length < 6) {
      setErreurMdp('Le nouveau mot de passe doit contenir au moins 6 caractères.');
      return;
    }
    if (nouveauMotDePasse !== confirmerMotDePasse) {
      setErreurMdp('Les mots de passe ne correspondent pas.');
      return;
    }

    try {
      setChargementMdp(true);
      await api.put('/auth/changer-mot-de-passe', { ancienMotDePasse, nouveauMotDePasse });
      setSuccesMdp('Mot de passe changé avec succès !');
      setAncienMotDePasse('');
      setNouveauMotDePasse('');
      setConfirmerMotDePasse('');
      setTimeout(() => fermerModalChangerMdp(), 2000);
    } catch (err) {
      setErreurMdp(err.response?.data?.message || 'Erreur lors du changement de mot de passe.');
    } finally {
      setChargementMdp(false);
    }
  };

  if (!utilisateur) return null;

  const totalDemandes = mesDemandes.length;
  const demandesAcceptees = mesDemandes.filter(d => d.statut === 'acceptée').length;
  const demandesEnAttente = mesDemandes.filter(d => d.statut === 'en attente').length;
  const demandesRefusees = mesDemandes.filter(d => d.statut === 'refusée').length;

  return (
    <div style={{ display: 'flex', minHeight: '100vh', background: '#F3F4F6', color: '#1F2937', fontFamily: 'Inter, sans-serif' }}>

      {/* ===== SIDEBAR ===== */}
      <div style={{ width: 240, background: '#FFFFFF', borderRight: '1px solid #E5E7EB', display: 'flex', flexDirection: 'column', padding: '24px 0', position: 'fixed', height: '100vh', boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
        <div style={{ padding: '0 20px 24px', borderBottom: '1px solid #E5E7EB' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <img src={logo} alt="Logo" style={{ width: 40, height: 40, objectFit: 'contain' }} />
            <div>
              <div style={{ fontSize: 13, fontWeight: 600, color: '#111827' }}>Portail Citoyen</div>
              <div style={{ fontSize: 11, color: '#6B7280' }}>État Civil</div>
            </div>
          </div>
        </div>

        <nav style={{ flex: 1, padding: '16px 12px' }}>
          {[
            { icon: Home, label: 'Tableau de bord', actif: true, lien: '/tableau-de-bord' },
            { icon: FileText, label: 'Mes demandes', actif: false, lien: '/mes-demandes' },
            { icon: Plus, label: 'Nouvelle demande', actif: false, lien: '/nouvelle-demande' },
          ].map(({ icon: Icon, label, actif, lien }) => (
            <Link key={label} to={lien} style={{ textDecoration: 'none' }}>
              <div style={{
                display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px',
                borderRadius: 8, marginBottom: 4,
                background: actif ? 'rgba(99,102,241,0.08)' : 'transparent',
                color: actif ? '#4F46E5' : '#6B7280',
                cursor: 'pointer', transition: 'all 0.2s',
                fontWeight: actif ? 600 : 400
              }}>
                <Icon size={16} />
                <span style={{ fontSize: 13 }}>{label}</span>
              </div>
            </Link>
          ))}
        </nav>

        <div style={{ padding: '16px 12px', borderTop: '1px solid #E5E7EB' }}>
          <button onClick={gererDeconnexion} style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#DC2626', cursor: 'pointer', fontSize: 13 }}>
            <LogOut size={16} />
            Se déconnecter
          </button>
        </div>
      </div>

      {/* ===== CONTENU PRINCIPAL ===== */}
      <div style={{ marginLeft: 240, flex: 1, padding: '32px 32px' }}>

        {/* Header */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 32 }}>
          <div>
            <div style={{ fontSize: 12, color: '#6B7280', marginBottom: 4 }}>Bienvenue,</div>
            <h1 style={{ margin: 0, fontSize: 24, fontWeight: 700, color: '#111827' }}>
              {profilDetaille?.prenom} {profilDetaille?.nom}
            </h1>
          </div>
          <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
            <button style={{ width: 38, height: 38, borderRadius: 10, border: '1px solid #E5E7EB', background: '#FFFFFF', color: '#6B7280', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Bell size={16} />
            </button>
            <Link to="/nouvelle-demande" style={{ textDecoration: 'none' }}>
              <button style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '10px 18px', borderRadius: 10, border: 'none', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', color: '#fff', cursor: 'pointer', fontSize: 13, fontWeight: 600 }}>
                <Plus size={15} />
                Nouvelle demande
              </button>
            </Link>

            {/* Profil avec menu */}
            <div ref={menuRef} style={{ position: 'relative' }}>
              <div onClick={toggleMenuProfil} style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '6px 12px 6px 6px', borderRadius: 50, border: '1px solid #E5E7EB', background: '#FFFFFF', cursor: 'pointer', boxShadow: menuProfilOuvert ? '0 4px 6px rgba(0,0,0,0.1)' : 'none' }}>
                <div style={{ width: 32, height: 32, borderRadius: '50%', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12, fontWeight: 700, color: '#fff' }}>
                  {profilDetaille?.nom?.charAt(0)}{profilDetaille?.prenom?.charAt(0)}
                </div>
                <div style={{ fontSize: 12, fontWeight: 500, color: '#111827' }}>
                  {profilDetaille?.prenom} {profilDetaille?.nom}
                </div>
                <ChevronDown size={14} color="#6B7280" style={{ transform: menuProfilOuvert ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }} />
              </div>

              {menuProfilOuvert && (
                <div style={{ position: 'absolute', top: 'calc(100% + 8px)', right: 0, width: 280, background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', boxShadow: '0 10px 30px rgba(0,0,0,0.15)', overflow: 'hidden', zIndex: 1000 }}>
                  <div style={{ padding: '16px 20px', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', display: 'flex', alignItems: 'center', gap: 12 }}>
                    <div style={{ width: 48, height: 48, borderRadius: '50%', background: 'rgba(255,255,255,0.2)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 16, fontWeight: 700, color: '#fff', border: '2px solid rgba(255,255,255,0.3)' }}>
                      {profilDetaille?.nom?.charAt(0)}{profilDetaille?.prenom?.charAt(0)}
                    </div>
                    <div style={{ color: '#fff', flex: 1 }}>
                      <div style={{ fontSize: 14, fontWeight: 700 }}>{profilDetaille?.prenom} {profilDetaille?.nom}</div>
                      <div style={{ fontSize: 11, opacity: 0.9 }}>{profilDetaille?.email}</div>
                    </div>
                  </div>

                  <div style={{ padding: '8px 12px' }}>
                    <button onClick={ouvrirModalCompte} style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#111827', cursor: 'pointer', fontSize: 13 }} onMouseEnter={(e) => e.currentTarget.style.background = '#F3F4F6'} onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}>
                      <UserCircle size={16} color="#4F46E5" />
                      <span style={{ flex: 1, textAlign: 'left' }}>Mon compte</span>
                      <ChevronRight size={14} color="#9CA3AF" />
                    </button>

                    <button onClick={ouvrirModalChangerMdp} style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#111827', cursor: 'pointer', fontSize: 13 }} onMouseEnter={(e) => e.currentTarget.style.background = '#F3F4F6'} onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}>
                      <Key size={16} color="#D97706" />
                      <span style={{ flex: 1, textAlign: 'left' }}>Changer mot de passe</span>
                      <ChevronRight size={14} color="#9CA3AF" />
                    </button>

                    <div style={{ borderTop: '1px solid #F3F4F6', marginTop: 4, paddingTop: 4 }}>
                      <button onClick={gererDeconnexion} style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#DC2626', cursor: 'pointer', fontSize: 13 }} onMouseEnter={(e) => e.currentTarget.style.background = '#FEE2E2'} onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}>
                        <LogOut size={16} />
                        <span style={{ flex: 1, textAlign: 'left', fontWeight: 600 }}>Se déconnecter</span>
                      </button>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {erreur && (
          <div style={{ padding: '12px 16px', borderRadius: 10, background: '#FEE2E2', border: '1px solid #DC2626', color: '#991B1B', fontSize: 13, marginBottom: 24, display: 'flex', alignItems: 'center', gap: 8 }}>
            <AlertCircle size={15} /> {erreur}
          </div>
        )}

        {/* ===== CARTES STATISTIQUES ===== */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 32 }}>
          {[
            { label: 'Total demandes', valeur: totalDemandes, icon: FileText, couleur: '#4F46E5', bg: 'rgba(99,102,241,0.1)' },
            { label: 'En attente', valeur: demandesEnAttente, icon: Clock, couleur: '#D97706', bg: 'rgba(245,158,11,0.1)' },
            { label: 'Acceptées', valeur: demandesAcceptees, icon: CheckCircle, couleur: '#059669', bg: 'rgba(16,185,129,0.1)' },
            { label: 'Refusées', valeur: demandesRefusees, icon: XCircle, couleur: '#DC2626', bg: 'rgba(239,68,68,0.1)' },
          ].map(({ label, valeur, icon: Icon, couleur, bg }) => (
            <div key={label} style={{ background: '#FFFFFF', borderRadius: 14, padding: '20px 22px', border: '1px solid #E5E7EB', boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <div>
                  <div style={{ fontSize: 12, color: '#6B7280', marginBottom: 8 }}>{label}</div>
                  <div style={{ fontSize: 28, fontWeight: 700, color: '#111827' }}>{valeur}</div>
                </div>
                <div style={{ width: 40, height: 40, borderRadius: 10, background: bg, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Icon size={18} color={couleur} />
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* ===== MES DEMANDES (pleine largeur) ===== */}
        <div style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', overflow: 'hidden', boxShadow: '0 1px 3px rgba(0,0,0,0.06)', marginBottom: 24 }}>
          <div style={{ padding: '20px 24px', borderBottom: '1px solid #E5E7EB', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h3 style={{ margin: 0, fontSize: 15, fontWeight: 600, color: '#111827' }}>Mes demandes</h3>
            <Link to="/nouvelle-demande" style={{ fontSize: 12, color: '#4F46E5', textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
              Nouvelle <ChevronRight size={13} />
            </Link>
          </div>

          {chargement ? (
            <div style={{ padding: 24, textAlign: 'center', color: '#6B7280', fontSize: 13 }}>Chargement…</div>
          ) : mesDemandes.length === 0 ? (
            <div style={{ padding: 40, textAlign: 'center' }}>
              <FileText size={32} color="#9CA3AF" style={{ marginBottom: 12 }} />
              <div style={{ color: '#6B7280', fontSize: 13 }}>Aucune demande pour le moment</div>
              <Link to="/nouvelle-demande" style={{ display: 'inline-block', marginTop: 12, padding: '8px 16px', borderRadius: 8, background: 'rgba(99,102,241,0.1)', color: '#4F46E5', textDecoration: 'none', fontSize: 13 }}>
                Créer une demande
              </Link>
            </div>
          ) : (
            <div>
              {mesDemandes.map((d, index) => {
                const config = COULEURS_STATUT[d.statut] || COULEURS_STATUT['en attente'];
                const IconStatut = config.icon;
                const actes = d.demande_actes || d.demandeActes || [];
                const nbActes = actes.reduce((sum, a) => sum + (a.quantite || 1), 0);
                const totalPrix = actes.reduce((sum, a) => sum + (parseFloat(a.prix_unitaire || 0) * (a.quantite || 1)), 0);
                
                return (
                  <div key={d.id_demande} style={{ padding: '16px 24px', borderBottom: index < mesDemandes.length - 1 ? '1px solid #F3F4F6' : 'none', display: 'flex', alignItems: 'center', gap: 16 }}>
                    <div style={{ width: 40, height: 40, borderRadius: 10, background: config.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                      <IconStatut size={18} color={config.texte} />
                    </div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      {/* Ligne 1: Reference + Status + Date */}
                      <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                        <span style={{ fontSize: 13, fontWeight: 700, color: '#111827' }}>
                          {d.reference || `DEM-${d.id_demande}`}
                        </span>
                        <span style={{ fontSize: 11, color: '#9CA3AF' }}>
                          {new Date(d.created_at).toLocaleDateString('fr-FR')}
                        </span>
                      </div>

                      {/* Ligne 2 : Demandeur / Concerné / Nombre d'actes / Type d'acte */}
                      <div style={{ display: 'flex', flexWrap: 'wrap', gap: 16, fontSize: 12, color: '#6B7280', marginBottom: 4 }}>
                        <div>
                          <span style={{ fontWeight: 500 }}>Demandeur :</span> {d.demandeur_prenom} {d.demandeur_nom}
                        </div>
                        <div>
                          <span style={{ fontWeight: 500 }}>Concerné :</span> {d.concerne_prenom} {d.concerne_nom}
                        </div>
                        <div>
                          <span style={{ fontWeight: 500 }}>Actes :</span> {nbActes}
                        </div>
                        <div>
                          <span style={{ fontWeight: 500 }}>Type :</span> {actes.map(a => LABELS_TYPE[a.type_acte?.type_acte] || a.type_acte).join(', ')}
                        </div>
                      </div>

                      {/* Ligne 3: Prix total + service */}
                      <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginTop: 6 }}>
                        <span style={{ fontSize: 13, fontWeight: 700, color: '#4F46E5' }}>
                          {new Intl.NumberFormat('fr-FR').format(totalPrix || d.prix_total || d.prix || 0)} Ar
                        </span>
                        {d.service === 'express' && (
                          <span style={{ fontSize: 10, padding: '2px 7px', borderRadius: 20, background: 'rgba(245,158,11,0.15)', color: '#D97706', fontWeight: 600, display: 'flex', alignItems: 'center', gap: 3 }}>
                            <Zap size={10} /> Express
                          </span>
                        )}
                        {(!d.service || d.service === 'standard') && (
                          <span style={{ fontSize: 10, padding: '2px 7px', borderRadius: 20, background: 'rgba(99,102,241,0.1)', color: '#4F46E5', fontWeight: 600, display: 'flex', alignItems: 'center', gap: 3 }}>
                            <Shield size={10} /> Standard
                          </span>
                        )}
                      </div>
                    </div>
                    <span style={{ fontSize: 11, padding: '4px 10px', borderRadius: 20, background: config.bg, color: config.texte, fontWeight: 600, border: `1px solid ${config.border}33`, flexShrink: 0 }}>
                      {d.statut}
                    </span>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {/* ===== SERVICES DISPONIBLES (sous Mes demandes) ===== */}
        <div style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', padding: 20, boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
          <h4 style={{ margin: '0 0 14px', fontSize: 13, fontWeight: 600, color: '#111827' }}>Services disponibles</h4>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            {[
              { icon: Shield, label: 'Standard', desc: 'Délai normal', couleur: '#4F46E5', bg: 'rgba(99,102,241,0.08)' },
              { icon: Zap, label: 'Express', desc: 'Traitement rapide', couleur: '#D97706', bg: 'rgba(245,158,11,0.08)' },
            ].map(({ icon: Icon, label, desc, couleur, bg }) => (
              <div key={label} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 16px', borderRadius: 10, background: bg }}>
                <Icon size={18} color={couleur} />
                <div>
                  <div style={{ fontSize: 13, fontWeight: 600, color: '#111827' }}>{label}</div>
                  <div style={{ fontSize: 11, color: '#6B7280' }}>{desc}</div>
                </div>
              </div>
            ))}
          </div>
        </div>

      </div>
      {/* ===== FIN CONTENU PRINCIPAL ===== */}

      {/* ===== MODALE MON COMPTE ===== */}
      {modalCompteOuvert && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 2000, backdropFilter: 'blur(4px)' }}>
          <div ref={modalRef} style={{ background: '#FFFFFF', borderRadius: 16, width: '100%', maxWidth: 480, maxHeight: '90vh', overflow: 'auto', boxShadow: '0 20px 60px rgba(0,0,0,0.3)' }}>
            <div style={{ padding: '20px 24px', borderBottom: '1px solid #E5E7EB', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', color: '#fff', borderTopLeftRadius: 16, borderTopRightRadius: 16 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <UserCircle size={20} />
                <h3 style={{ margin: 0, fontSize: 16, fontWeight: 600 }}>Mon compte</h3>
              </div>
              <button onClick={fermerModalCompte} style={{ background: 'rgba(255,255,255,0.15)', border: 'none', color: '#fff', width: 32, height: 32, borderRadius: 8, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <X size={18} />
              </button>
            </div>

            <div style={{ padding: '24px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 20 }}>
                <div style={{ width: 64, height: 64, borderRadius: '50%', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, fontWeight: 700, color: '#fff' }}>
                  {profilDetaille?.nom?.charAt(0)}{profilDetaille?.prenom?.charAt(0)}
                </div>
                <div>
                  <div style={{ fontSize: 16, fontWeight: 600, color: '#111827' }}>{profilDetaille?.prenom} {profilDetaille?.nom}</div>
                  <div style={{ fontSize: 12, color: '#6B7280' }}>
                    <Award size={12} style={{ display: 'inline', marginRight: 4 }} />
                    Citoyen
                  </div>
                </div>
              </div>

              <div style={{ marginBottom: 20 }}>
                <div style={{ fontSize: 11, fontWeight: 600, color: '#6B7280', textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: 12 }}>
                  Informations personnelles
                </div>
                {[
                  { icon: Mail, label: 'Email', value: profilDetaille?.email },
                  { icon: Phone, label: 'Téléphone', value: profilDetaille?.contact || 'Non renseigné' },
                  { icon: MapPin, label: 'Adresse', value: profilDetaille?.adresse || 'Non renseignée' },
                ].map(({ icon: Icon, label, value }) => (
                  <div key={label} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', background: '#F9FAFB', borderRadius: 8, marginBottom: 8 }}>
                    <Icon size={16} color="#6B7280" />
                    <div style={{ flex: 1 }}>
                      <div style={{ fontSize: 11, color: '#6B7280' }}>{label}</div>
                      <div style={{ fontSize: 13, color: '#111827', fontWeight: 500 }}>{value}</div>
                    </div>
                  </div>
                ))}
              </div>

              <button onClick={ouvrirModalChangerMdp} style={{ width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, padding: '10px', borderRadius: 8, border: '1px solid #E5E7EB', background: '#FFFFFF', color: '#4F46E5', cursor: 'pointer', fontSize: 13, fontWeight: 500 }}>
                <Key size={16} />
                Changer mon mot de passe
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ===== MODALE CHANGER MOT DE PASSE ===== */}
      {modalChangerMdpOuvert && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 2000, backdropFilter: 'blur(4px)' }}>
          <div ref={modalRef} style={{ background: '#FFFFFF', borderRadius: 16, width: '100%', maxWidth: 440, boxShadow: '0 20px 60px rgba(0,0,0,0.3)' }}>
            <div style={{ padding: '20px 24px', borderBottom: '1px solid #E5E7EB', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', color: '#fff', borderTopLeftRadius: 16, borderTopRightRadius: 16 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <Key size={20} />
                <h3 style={{ margin: 0, fontSize: 16, fontWeight: 600 }}>Changer mot de passe</h3>
              </div>
              <button onClick={fermerModalChangerMdp} style={{ background: 'rgba(255,255,255,0.15)', border: 'none', color: '#fff', width: 32, height: 32, borderRadius: 8, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <X size={18} />
              </button>
            </div>

            <form onSubmit={handleChangerMotDePasse} style={{ padding: '24px' }}>
              {erreurMdp && (
                <div style={{ padding: '10px 12px', borderRadius: 8, background: '#FEE2E2', color: '#991B1B', fontSize: 12, marginBottom: 16, display: 'flex', alignItems: 'center', gap: 8 }}>
                  <AlertCircle size={14} />
                  {erreurMdp}
                </div>
              )}

              {succesMdp && (
                <div style={{ padding: '10px 12px', borderRadius: 8, background: '#D1FAE5', color: '#065F46', fontSize: 12, marginBottom: 16, display: 'flex', alignItems: 'center', gap: 8 }}>
                  <CheckCircle size={14} />
                  {succesMdp}
                </div>
              )}

              {[
                { label: 'Ancien mot de passe', value: ancienMotDePasse, setter: setAncienMotDePasse, visible: afficherAncienMdp, toggle: () => setAfficherAncienMdp(!afficherAncienMdp), placeholder: 'Entrez votre ancien mot de passe' },
                { label: 'Nouveau mot de passe', value: nouveauMotDePasse, setter: setNouveauMotDePasse, visible: afficherNouveauMdp, toggle: () => setAfficherNouveauMdp(!afficherNouveauMdp), placeholder: 'Entrez votre nouveau mot de passe', hint: 'Minimum 6 caractères' },
                { label: 'Confirmer le mot de passe', value: confirmerMotDePasse, setter: setConfirmerMotDePasse, visible: afficherConfirmerMdp, toggle: () => setAfficherConfirmerMdp(!afficherConfirmerMdp), placeholder: 'Confirmez votre nouveau mot de passe' },
              ].map(({ label, value, setter, visible, toggle, placeholder, hint }) => (
                <div key={label} style={{ marginBottom: 16 }}>
                  <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>{label}</label>
                  <div style={{ position: 'relative' }}>
                    <input
                      type={visible ? 'text' : 'password'}
                      value={value}
                      onChange={(e) => setter(e.target.value)}
                      placeholder={placeholder}
                      style={{ width: '100%', padding: '10px 40px 10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none' }}
                    />
                    <button type="button" onClick={toggle} style={{ position: 'absolute', right: 8, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', color: '#6B7280', cursor: 'pointer', padding: 4 }}>
                      {visible ? <EyeOff size={16} /> : <Eye size={16} />}
                    </button>
                  </div>
                  {hint && <div style={{ fontSize: 11, color: '#6B7280', marginTop: 4 }}>{hint}</div>}
                </div>
              ))}

              <div style={{ display: 'flex', gap: 10 }}>
                <button type="button" onClick={fermerModalChangerMdp} style={{ flex: 1, padding: '10px', borderRadius: 8, border: '1px solid #E5E7EB', background: '#FFFFFF', color: '#6B7280', cursor: 'pointer', fontSize: 13, fontWeight: 500 }}>
                  Annuler
                </button>
                <button type="submit" disabled={chargementMdp} style={{ flex: 2, padding: '10px', borderRadius: 8, border: 'none', background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', color: '#fff', cursor: chargementMdp ? 'not-allowed' : 'pointer', fontSize: 13, fontWeight: 600, opacity: chargementMdp ? 0.7 : 1 }}>
                  {chargementMdp ? 'Changement en cours...' : 'Changer le mot de passe'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}