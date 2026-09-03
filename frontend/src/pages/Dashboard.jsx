import { useEffect, useState, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import {
  FileText, Clock, CheckCircle, XCircle, LogOut, Plus,
  User, Phone, MapPin, Mail, Bell, Settings, ChevronRight,
  Zap, Shield, TrendingUp, AlertCircle, Home, ChevronDown,
  UserCircle, Edit, HelpCircle, Award, Key, X, Eye, EyeOff
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
      if (modalRef.current && !modalRef.current.contains(event.target)) {
        // Ne ferme pas automatiquement les modales pour éviter une fermeture accidentelle
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

      console.log('Profil détaillé:', resProfil.data.utilisateur);
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

  const toggleMenuProfil = () => {
    setMenuProfilOuvert(!menuProfilOuvert);
  };

  const ouvrirModalCompte = () => {
    setModalCompteOuvert(true);
    setMenuProfilOuvert(false);
  };

  const fermerModalCompte = () => {
    setModalCompteOuvert(false);
  };

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

    // Validation
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
      const response = await api.put('/auth/changer-mot-de-passe', {
        ancienMotDePasse,
        nouveauMotDePasse
      });
      
      setSuccesMdp('Mot de passe changé avec succès !');
      setAncienMotDePasse('');
      setNouveauMotDePasse('');
      setConfirmerMotDePasse('');
      
      // Fermer automatiquement après 2 secondes
      setTimeout(() => {
        fermerModalChangerMdp();
      }, 2000);
      
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
            { icon: FileText, label: 'Mes demandes', actif: false, lien: '/tableau-de-bord' },
            { icon: Plus, label: 'Nouvelle demande', actif: false, lien: '/nouvelle-demande' },
          ].map(({ icon: Icon, label, actif, lien }) => (
            <Link key={label} to={lien} style={{ textDecoration: 'none' }}>
              <div style={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: 10, 
                padding: '10px 12px', 
                borderRadius: 8, 
                marginBottom: 4, 
                background: actif ? 'rgba(99,102,241,0.08)' : 'transparent', 
                color: actif ? '#4F46E5' : '#6B7280', 
                cursor: 'pointer', 
                transition: 'all 0.2s',
                fontWeight: actif ? 600 : 400
              }}>
                <Icon size={16} />
                <span style={{ fontSize: 13 }}>{label}</span>
              </div>
            </Link>
          ))}
        </nav>

        <div style={{ padding: '16px 12px', borderTop: '1px solid #E5E7EB' }}>
          <button onClick={gererDeconnexion} style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#DC2626', cursor: 'pointer', fontSize: 13, transition: 'background 0.2s' }}>
            <LogOut size={16} />
            Se déconnecter
          </button>
        </div>
      </div>

      {/* ===== CONTENU PRINCIPAL ===== */}
      <div style={{ marginLeft: 240, flex: 1, padding: '32px 32px' }}>

        {/* Header avec profil */}
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
            
            {/* ===== PROFIL AVEC MENU DÉROULANT ===== */}
            <div ref={menuRef} style={{ position: 'relative' }}>
              <div 
                onClick={toggleMenuProfil}
                style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  gap: 8, 
                  padding: '6px 12px 6px 6px', 
                  borderRadius: 50, 
                  border: '1px solid #E5E7EB', 
                  background: '#FFFFFF', 
                  cursor: 'pointer',
                  transition: 'all 0.2s',
                  boxShadow: menuProfilOuvert ? '0 4px 6px rgba(0,0,0,0.1)' : 'none'
                }}
              >
                <div style={{ 
                  width: 32, 
                  height: 32, 
                  borderRadius: '50%', 
                  background: 'linear-gradient(135deg,#6366F1,#8B5CF6)', 
                  display: 'flex', 
                  alignItems: 'center', 
                  justifyContent: 'center', 
                  fontSize: 12, 
                  fontWeight: 700, 
                  color: '#fff' 
                }}>
                  {profilDetaille?.nom?.charAt(0)}{profilDetaille?.prenom?.charAt(0)}
                </div>
                <div style={{ fontSize: 12, fontWeight: 500, color: '#111827' }}>
                  {profilDetaille?.prenom} {profilDetaille?.nom}
                </div>
                <ChevronDown size={14} color="#6B7280" style={{ 
                  transform: menuProfilOuvert ? 'rotate(180deg)' : 'rotate(0deg)',
                  transition: 'transform 0.2s'
                }} />
              </div>

              {/* ===== MENU DÉROULANT ===== */}
              {menuProfilOuvert && (
                <div style={{
                  position: 'absolute',
                  top: 'calc(100% + 8px)',
                  right: 0,
                  width: 280,
                  background: '#FFFFFF',
                  borderRadius: 14,
                  border: '1px solid #E5E7EB',
                  boxShadow: '0 10px 30px rgba(0,0,0,0.15)',
                  overflow: 'hidden',
                  zIndex: 1000
                }}>
                  {/* En-tête du profil */}
                  <div style={{ 
                    padding: '16px 20px', 
                    background: 'linear-gradient(135deg,#6366F1,#8B5CF6)',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 12
                  }}>
                    <div style={{ 
                      width: 48, 
                      height: 48, 
                      borderRadius: '50%', 
                      background: 'rgba(255,255,255,0.2)', 
                      display: 'flex', 
                      alignItems: 'center', 
                      justifyContent: 'center', 
                      fontSize: 16, 
                      fontWeight: 700, 
                      color: '#fff',
                      border: '2px solid rgba(255,255,255,0.3)'
                    }}>
                      {profilDetaille?.nom?.charAt(0)}{profilDetaille?.prenom?.charAt(0)}
                    </div>
                    <div style={{ color: '#fff', flex: 1 }}>
                      <div style={{ fontSize: 14, fontWeight: 700 }}>
                        {profilDetaille?.prenom} {profilDetaille?.nom}
                      </div>
                      <div style={{ fontSize: 11, opacity: 0.9 }}>
                        {profilDetaille?.email}
                      </div>
                    </div>
                  </div>

                  {/* Actions du menu */}
                  <div style={{ padding: '8px 12px' }}>
                    {/* Mon compte */}
                    <button
                      onClick={ouvrirModalCompte}
                      style={{
                        width: '100%',
                        display: 'flex',
                        alignItems: 'center',
                        gap: 12,
                        padding: '10px 12px',
                        borderRadius: 8,
                        border: 'none',
                        background: 'transparent',
                        color: '#111827',
                        cursor: 'pointer',
                        fontSize: 13,
                        transition: 'background 0.2s',
                      }}
                      onMouseEnter={(e) => e.currentTarget.style.background = '#F3F4F6'}
                      onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}
                    >
                      <UserCircle size={16} color="#4F46E5" />
                      <span style={{ flex: 1, textAlign: 'left' }}>Mon compte</span>
                      <ChevronRight size={14} color="#9CA3AF" />
                    </button>

                    {/* Changer mot de passe */}
                    <button
                      onClick={ouvrirModalChangerMdp}
                      style={{
                        width: '100%',
                        display: 'flex',
                        alignItems: 'center',
                        gap: 12,
                        padding: '10px 12px',
                        borderRadius: 8,
                        border: 'none',
                        background: 'transparent',
                        color: '#111827',
                        cursor: 'pointer',
                        fontSize: 13,
                        transition: 'background 0.2s',
                      }}
                      onMouseEnter={(e) => e.currentTarget.style.background = '#F3F4F6'}
                      onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}
                    >
                      <Key size={16} color="#D97706" />
                      <span style={{ flex: 1, textAlign: 'left' }}>Changer mot de passe</span>
                      <ChevronRight size={14} color="#9CA3AF" />
                    </button>

                    <div style={{ borderTop: '1px solid #F3F4F6', marginTop: 4, paddingTop: 4 }}>
                      <button
                        onClick={gererDeconnexion}
                        style={{
                          width: '100%',
                          display: 'flex',
                          alignItems: 'center',
                          gap: 12,
                          padding: '10px 12px',
                          borderRadius: 8,
                          border: 'none',
                          background: 'transparent',
                          color: '#DC2626',
                          cursor: 'pointer',
                          fontSize: 13,
                          transition: 'background 0.2s',
                        }}
                        onMouseEnter={(e) => e.currentTarget.style.background = '#FEE2E2'}
                        onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}
                      >
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

        {/* ===== GRILLE PRINCIPALE ===== */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 320px', gap: 20, marginBottom: 24 }}>
          {/* Demandes récentes */}
          <div style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', overflow: 'hidden', boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
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
                  return (
                    <div key={d.id_demande} style={{ padding: '16px 24px', borderBottom: index < mesDemandes.length - 1 ? '1px solid #F3F4F6' : 'none', display: 'flex', alignItems: 'center', gap: 16 }}>
                      <div style={{ width: 40, height: 40, borderRadius: 10, background: config.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                        <IconStatut size={18} color={config.texte} />
                      </div>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ fontSize: 13, fontWeight: 600, color: '#111827', marginBottom: 3 }}>
                          {d.type_acte?.nom || LABELS_TYPE[d.type_acte?.type_acte] || 'Demande N°' + d.id_demande}
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                          <span style={{ fontSize: 11, color: '#6B7280' }}>N° {d.id_demande}</span>
                          <span style={{ fontSize: 11, color: '#6B7280' }}>{Number(d.prix || 0).toLocaleString('fr-FR')} Ar</span>
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

          {/* Informations rapides */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
            <div style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', padding: 20, boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
              <h4 style={{ margin: '0 0 14px', fontSize: 13, fontWeight: 600, color: '#111827' }}>Services disponibles</h4>
              {[
                { icon: Shield, label: 'Standard', desc: 'Délai normal', couleur: '#4F46E5', bg: 'rgba(99,102,241,0.08)' },
                { icon: Zap, label: 'Express', desc: 'Traitement rapide', couleur: '#D97706', bg: 'rgba(245,158,11,0.08)' },
              ].map(({ icon: Icon, label, desc, couleur, bg }) => (
                <div key={label} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', borderRadius: 10, background: bg, marginBottom: 8 }}>
                  <Icon size={16} color={couleur} />
                  <div>
                    <div style={{ fontSize: 12, fontWeight: 600, color: '#111827' }}>{label}</div>
                    <div style={{ fontSize: 11, color: '#6B7280' }}>{desc}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* ===== CASES PAR STATUT ===== */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16 }}>
          {[
            { statut: 'en attente', label: 'En attente', icon: Clock },
            { statut: 'acceptée', label: 'Acceptées', icon: CheckCircle },
            { statut: 'refusée', label: 'Refusées', icon: XCircle },
          ].map(({ statut, label, icon: Icon }) => {
            const config = COULEURS_STATUT[statut];
            const filtrees = mesDemandes.filter(d => d.statut === statut);
            return (
              <div key={statut} style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', overflow: 'hidden', boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
                <div style={{ padding: '14px 18px', borderBottom: '1px solid #E5E7EB', display: 'flex', alignItems: 'center', gap: 8 }}>
                  <Icon size={15} color={config.texte} />
                  <span style={{ fontSize: 13, fontWeight: 600, color: config.texte }}>{label}</span>
                  <span style={{ marginLeft: 'auto', fontSize: 11, padding: '2px 8px', borderRadius: 20, background: config.bg, color: config.texte, fontWeight: 600 }}>{filtrees.length}</span>
                </div>
                <div style={{ padding: '8px 0', maxHeight: 200, overflowY: 'auto' }}>
                  {filtrees.length === 0 ? (
                    <div style={{ padding: '16px 18px', fontSize: 12, color: '#6B7280', textAlign: 'center' }}>Aucune demande</div>
                  ) : filtrees.map((d) => (
                    <div key={d.id_demande} style={{ padding: '10px 18px', borderBottom: '1px solid #F3F4F6', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <div>
                        <div style={{ fontSize: 12, fontWeight: 500, color: '#1F2937' }}>{d.type_acte?.nom || 'Demande'}</div>
                        <div style={{ fontSize: 11, color: '#6B7280', display: 'flex', gap: 8, marginTop: 2 }}>
                          <span>N°{d.id_demande}</span>
                          {d.service === 'express' ? (
                            <span style={{ color: '#D97706', display: 'flex', alignItems: 'center', gap: 2 }}><Zap size={9} />Express</span>
                          ) : (
                            <span style={{ color: '#4F46E5', display: 'flex', alignItems: 'center', gap: 2 }}><Shield size={9} />Standard</span>
                          )}
                        </div>
                      </div>
                      <span style={{ fontSize: 11, color: '#6B7280' }}>{Number(d.prix || 0).toLocaleString('fr-FR')} Ar</span>
                    </div>
                  ))}
                </div>
              </div>
            );
          })}
        </div>

      </div>

      {/* ===== MODALE MON COMPTE ===== */}
      {modalCompteOuvert && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: 'rgba(0,0,0,0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 2000,
          backdropFilter: 'blur(4px)',
          animation: 'fadeIn 0.2s ease-in-out'
        }}>
          <div ref={modalRef} style={{
            background: '#FFFFFF',
            borderRadius: 16,
            width: '100%',
            maxWidth: 480,
            maxHeight: '90vh',
            overflow: 'auto',
            boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
            animation: 'slideUp 0.3s ease-in-out'
          }}>
            {/* En-tête */}
            <div style={{
              padding: '20px 24px',
              borderBottom: '1px solid #E5E7EB',
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              background: 'linear-gradient(135deg,#6366F1,#8B5CF6)',
              color: '#fff',
              borderTopLeftRadius: 16,
              borderTopRightRadius: 16
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <UserCircle size={20} />
                <h3 style={{ margin: 0, fontSize: 16, fontWeight: 600 }}>Mon compte</h3>
              </div>
              <button
                onClick={fermerModalCompte}
                style={{
                  background: 'rgba(255,255,255,0.15)',
                  border: 'none',
                  color: '#fff',
                  width: 32,
                  height: 32,
                  borderRadius: 8,
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  transition: 'background 0.2s'
                }}
                onMouseEnter={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.25)'}
                onMouseLeave={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.15)'}
              >
                <X size={18} />
              </button>
            </div>

            {/* Contenu */}
            <div style={{ padding: '24px' }}>
              {/* Avatar et nom */}
              <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 20 }}>
                <div style={{
                  width: 64,
                  height: 64,
                  borderRadius: '50%',
                  background: 'linear-gradient(135deg,#6366F1,#8B5CF6)',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 20,
                  fontWeight: 700,
                  color: '#fff'
                }}>
                  {profilDetaille?.nom?.charAt(0)}{profilDetaille?.prenom?.charAt(0)}
                </div>
                <div>
                  <div style={{ fontSize: 16, fontWeight: 600, color: '#111827' }}>
                    {profilDetaille?.prenom} {profilDetaille?.nom}
                  </div>
                  <div style={{ fontSize: 12, color: '#6B7280' }}>
                    <Award size={12} style={{ display: 'inline', marginRight: 4 }} />
                    Citoyen
                  </div>
                </div>
              </div>

              {/* Informations */}
              <div style={{ marginBottom: 20 }}>
                <div style={{ fontSize: 11, fontWeight: 600, color: '#6B7280', textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: 12 }}>
                  Informations personnelles
                </div>
                {[
                  { icon: Mail, label: 'Email', value: profilDetaille?.email },
                  { icon: Phone, label: 'Téléphone', value: profilDetaille?.contact || 'Non renseigné' },
                  { icon: MapPin, label: 'Adresse', value: profilDetaille?.adresse || 'Non renseignée' },
                ].map(({ icon: Icon, label, value }) => (
                  <div key={label} style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 12,
                    padding: '10px 12px',
                    background: '#F9FAFB',
                    borderRadius: 8,
                    marginBottom: 8
                  }}>
                    <Icon size={16} color="#6B7280" />
                    <div style={{ flex: 1 }}>
                      <div style={{ fontSize: 11, color: '#6B7280' }}>{label}</div>
                      <div style={{ fontSize: 13, color: '#111827', fontWeight: 500 }}>{value}</div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Bouton changer mot de passe */}
              <button
                onClick={ouvrirModalChangerMdp}
                style={{
                  width: '100%',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: 8,
                  padding: '10px',
                  borderRadius: 8,
                  border: '1px solid #E5E7EB',
                  background: '#FFFFFF',
                  color: '#4F46E5',
                  cursor: 'pointer',
                  fontSize: 13,
                  fontWeight: 500,
                  transition: 'all 0.2s'
                }}
                onMouseEnter={(e) => { e.currentTarget.style.background = '#F3F4F6'; }}
                onMouseLeave={(e) => { e.currentTarget.style.background = '#FFFFFF'; }}
              >
                <Key size={16} />
                Changer mon mot de passe
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ===== MODALE CHANGER MOT DE PASSE ===== */}
      {modalChangerMdpOuvert && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: 'rgba(0,0,0,0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 2000,
          backdropFilter: 'blur(4px)'
        }}>
          <div ref={modalRef} style={{
            background: '#FFFFFF',
            borderRadius: 16,
            width: '100%',
            maxWidth: 440,
            boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
            animation: 'slideUp 0.3s ease-in-out'
          }}>
            {/* En-tête */}
            <div style={{
              padding: '20px 24px',
              borderBottom: '1px solid #E5E7EB',
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              background: 'linear-gradient(135deg,#6366F1,#8B5CF6)',
              color: '#fff',
              borderTopLeftRadius: 16,
              borderTopRightRadius: 16
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <Key size={20} />
                <h3 style={{ margin: 0, fontSize: 16, fontWeight: 600 }}>Changer mot de passe</h3>
              </div>
              <button
                onClick={fermerModalChangerMdp}
                style={{
                  background: 'rgba(255,255,255,0.15)',
                  border: 'none',
                  color: '#fff',
                  width: 32,
                  height: 32,
                  borderRadius: 8,
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  transition: 'background 0.2s'
                }}
                onMouseEnter={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.25)'}
                onMouseLeave={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.15)'}
              >
                <X size={18} />
              </button>
            </div>

            {/* Contenu */}
            <form onSubmit={handleChangerMotDePasse} style={{ padding: '24px' }}>
              {erreurMdp && (
                <div style={{
                  padding: '10px 12px',
                  borderRadius: 8,
                  background: '#FEE2E2',
                  color: '#991B1B',
                  fontSize: 12,
                  marginBottom: 16,
                  display: 'flex',
                  alignItems: 'center',
                  gap: 8
                }}>
                  <AlertCircle size={14} />
                  {erreurMdp}
                </div>
              )}

              {succesMdp && (
                <div style={{
                  padding: '10px 12px',
                  borderRadius: 8,
                  background: '#D1FAE5',
                  color: '#065F46',
                  fontSize: 12,
                  marginBottom: 16,
                  display: 'flex',
                  alignItems: 'center',
                  gap: 8
                }}>
                  <CheckCircle size={14} />
                  {succesMdp}
                </div>
              )}

              {/* Ancien mot de passe */}
              <div style={{ marginBottom: 16 }}>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>
                  Ancien mot de passe
                </label>
                <div style={{ position: 'relative' }}>
                  <input
                    type={afficherAncienMdp ? 'text' : 'password'}
                    value={ancienMotDePasse}
                    onChange={(e) => setAncienMotDePasse(e.target.value)}
                    placeholder="Entrez votre ancien mot de passe"
                    style={{
                      width: '100%',
                      padding: '10px 40px 10px 12px',
                      borderRadius: 8,
                      border: '1px solid #E5E7EB',
                      fontSize: 13,
                      color: '#111827',
                      background: '#F9FAFB',
                      outline: 'none',
                      transition: 'border-color 0.2s'
                    }}
                    onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                    onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                  />
                  <button
                    type="button"
                    onClick={() => setAfficherAncienMdp(!afficherAncienMdp)}
                    style={{
                      position: 'absolute',
                      right: 8,
                      top: '50%',
                      transform: 'translateY(-50%)',
                      background: 'none',
                      border: 'none',
                      color: '#6B7280',
                      cursor: 'pointer',
                      padding: 4
                    }}
                  >
                    {afficherAncienMdp ? <EyeOff size={16} /> : <Eye size={16} />}
                  </button>
                </div>
              </div>

              {/* Nouveau mot de passe */}
              <div style={{ marginBottom: 16 }}>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>
                  Nouveau mot de passe
                </label>
                <div style={{ position: 'relative' }}>
                  <input
                    type={afficherNouveauMdp ? 'text' : 'password'}
                    value={nouveauMotDePasse}
                    onChange={(e) => setNouveauMotDePasse(e.target.value)}
                    placeholder="Entrez votre nouveau mot de passe"
                    style={{
                      width: '100%',
                      padding: '10px 40px 10px 12px',
                      borderRadius: 8,
                      border: '1px solid #E5E7EB',
                      fontSize: 13,
                      color: '#111827',
                      background: '#F9FAFB',
                      outline: 'none',
                      transition: 'border-color 0.2s'
                    }}
                    onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                    onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                  />
                  <button
                    type="button"
                    onClick={() => setAfficherNouveauMdp(!afficherNouveauMdp)}
                    style={{
                      position: 'absolute',
                      right: 8,
                      top: '50%',
                      transform: 'translateY(-50%)',
                      background: 'none',
                      border: 'none',
                      color: '#6B7280',
                      cursor: 'pointer',
                      padding: 4
                    }}
                  >
                    {afficherNouveauMdp ? <EyeOff size={16} /> : <Eye size={16} />}
                  </button>
                </div>
                <div style={{ fontSize: 11, color: '#6B7280', marginTop: 4 }}>
                  Minimum 6 caractères
                </div>
              </div>

              {/* Confirmer mot de passe */}
              <div style={{ marginBottom: 20 }}>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>
                  Confirmer le mot de passe
                </label>
                <div style={{ position: 'relative' }}>
                  <input
                    type={afficherConfirmerMdp ? 'text' : 'password'}
                    value={confirmerMotDePasse}
                    onChange={(e) => setConfirmerMotDePasse(e.target.value)}
                    placeholder="Confirmez votre nouveau mot de passe"
                    style={{
                      width: '100%',
                      padding: '10px 40px 10px 12px',
                      borderRadius: 8,
                      border: '1px solid #E5E7EB',
                      fontSize: 13,
                      color: '#111827',
                      background: '#F9FAFB',
                      outline: 'none',
                      transition: 'border-color 0.2s'
                    }}
                    onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                    onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                  />
                  <button
                    type="button"
                    onClick={() => setAfficherConfirmerMdp(!afficherConfirmerMdp)}
                    style={{
                      position: 'absolute',
                      right: 8,
                      top: '50%',
                      transform: 'translateY(-50%)',
                      background: 'none',
                      border: 'none',
                      color: '#6B7280',
                      cursor: 'pointer',
                      padding: 4
                    }}
                  >
                    {afficherConfirmerMdp ? <EyeOff size={16} /> : <Eye size={16} />}
                  </button>
                </div>
              </div>

              {/* Boutons */}
              <div style={{ display: 'flex', gap: 10 }}>
                <button
                  type="button"
                  onClick={fermerModalChangerMdp}
                  style={{
                    flex: 1,
                    padding: '10px',
                    borderRadius: 8,
                    border: '1px solid #E5E7EB',
                    background: '#FFFFFF',
                    color: '#6B7280',
                    cursor: 'pointer',
                    fontSize: 13,
                    fontWeight: 500,
                    transition: 'all 0.2s'
                  }}
                  onMouseEnter={(e) => e.currentTarget.style.background = '#F3F4F6'}
                  onMouseLeave={(e) => e.currentTarget.style.background = '#FFFFFF'}
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  disabled={chargementMdp}
                  style={{
                    flex: 2,
                    padding: '10px',
                    borderRadius: 8,
                    border: 'none',
                    background: 'linear-gradient(135deg,#6366F1,#8B5CF6)',
                    color: '#fff',
                    cursor: chargementMdp ? 'not-allowed' : 'pointer',
                    fontSize: 13,
                    fontWeight: 600,
                    opacity: chargementMdp ? 0.7 : 1,
                    transition: 'all 0.2s'
                  }}
                >
                  {chargementMdp ? 'Changement en cours...' : 'Changer le mot de passe'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Styles CSS pour les animations */}
      <style>{`
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes slideUp {
          from { 
            opacity: 0;
            transform: translateY(20px) scale(0.95);
          }
          to { 
            opacity: 1;
            transform: translateY(0) scale(1);
          }
        }
      `}</style>
    </div>
  );
}