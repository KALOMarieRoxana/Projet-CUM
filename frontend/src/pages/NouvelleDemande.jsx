import { useState, useEffect, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import {
  FileText, Clock, CheckCircle, XCircle, LogOut, Plus,
  User, Phone, MapPin, Mail, Bell, Settings, ChevronRight,
  Zap, Shield, TrendingUp, AlertCircle, Home, ChevronDown,
  UserCircle, Edit, HelpCircle, Award, Key, X, Eye, EyeOff,
  ArrowLeft, Send, Save, Calendar
} from 'lucide-react';
import logo from '../assets/image/logo.png';

const LABELS_TYPE = {
  naissance: 'Acte de naissance',
  mariage: 'Acte de mariage',
  deces: 'Acte de décès',
  divorce: 'Acte de divorce',
};

export default function NouvelleDemande() {
  const { utilisateur, deconnecter } = useAuth();
  const navigate = useNavigate();
  const [profilDetaille, setProfilDetaille] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [soumission, setSoumission] = useState(false);
  const [erreur, setErreur] = useState('');
  const [succes, setSucces] = useState('');

  // Menu profil
  const [menuProfilOuvert, setMenuProfilOuvert] = useState(false);
  const menuRef = useRef(null);

  // ---- État du formulaire ----
  const [form, setForm] = useState({
    demandeur_nom: '',
    demandeur_prenom: '',
    demandeur_adresse: '',
    demandeur_relation: '',
    demandeur_contact: '',
    personne_nom: '',
    personne_prenom: '',
    personne_numero_acte: '',
    personne_lieu_naissance: '',
    personne_date_naissance: '',
    type_acte: 'naissance',
    service: 'standard',
  });

  // ---- Gestion du clic hors menu ----
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuProfilOuvert(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // ---- Chargement du profil ----
  useEffect(() => {
    if (!utilisateur) {
      navigate('/connexion');
      return;
    }
    chargerProfil();
  }, [utilisateur, navigate]);

  const chargerProfil = async () => {
    try {
      setChargement(true);
      const res = await api.get('/auth/profil');
      const user = res.data.utilisateur;
      setProfilDetaille(user);
      setForm(prev => ({
        ...prev,
        demandeur_nom: user.nom || '',
        demandeur_prenom: user.prenom || '',
        demandeur_adresse: user.adresse || '',
        demandeur_relation: user.relation || '',
        demandeur_contact: user.contact || '',
      }));
    } catch (err) {
      setErreur('Impossible de charger votre profil.');
    } finally {
      setChargement(false);
    }
  };

  // ---- VÉRIFICATION DE L'AUTHENTIFICATION (NOUVEAU) ----
  const verifierAuthentification = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        setErreur('Vous devez être connecté pour faire une demande.');
        navigate('/connexion');
        return false;
      }

      const response = await api.get('/auth/verifier');
      console.log('✅ Utilisateur authentifié:', response.data);
      return true;
    } catch (error) {
      console.error('❌ Erreur d\'authentification:', error);
      if (error.response?.status === 401) {
        setErreur('Session expirée. Veuillez vous reconnecter.');
        localStorage.removeItem('token');
        navigate('/connexion');
      } else {
        setErreur('Erreur de vérification d\'authentification.');
      }
      return false;
    }
  };

  // ---- Gestion des champs ----
  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  };

  // ---- Soumission avec vérification ----
  const handleSubmit = async (e) => {
    e.preventDefault();
    setErreur('');
    setSucces('');
    setSoumission(true);

    // 1️⃣ Vérifier l'authentification avant tout
    const estAuthentifie = await verifierAuthentification();
    if (!estAuthentifie) {
      setSoumission(false);
      return;
    }

    // 2️⃣ Vérifier les champs obligatoires
    const required = [
      'demandeur_nom', 'demandeur_prenom', 'demandeur_adresse',
      'personne_nom', 'personne_prenom', 'personne_lieu_naissance',
      'personne_date_naissance', 'type_acte', 'service'
    ];
    const manquant = required.filter(field => !form[field]?.trim());
    if (manquant.length) {
      setErreur('Veuillez remplir tous les champs obligatoires.');
      setSoumission(false);
      return;
    }

    // 3️⃣ Vérifier le token
    const token = localStorage.getItem('token');
    if (!token) {
      setErreur('Token d\'authentification manquant. Veuillez vous reconnecter.');
      navigate('/connexion');
      setSoumission(false);
      return;
    }

    try {
      // 4️⃣ Préparer et envoyer la demande
      const payload = {
        demandeur_nom: form.demandeur_nom,
        demandeur_prenom: form.demandeur_prenom,
        demandeur_adresse: form.demandeur_adresse,
        demandeur_relation: form.demandeur_relation,
        demandeur_contact: form.demandeur_contact,
        personne_nom: form.personne_nom,
        personne_prenom: form.personne_prenom,
        personne_numero_acte: form.personne_numero_acte,
        personne_lieu_naissance: form.personne_lieu_naissance,
        personne_date_naissance: form.personne_date_naissance,
        type_acte: form.type_acte,
        service: form.service,
      };

      console.log('📤 Envoi de la demande:', payload);
      console.log('🔑 Token utilisé:', token);

      const response = await api.post('/demandes', payload);
      console.log('✅ Réponse:', response.data);
      
      setSucces('Demande envoyée avec succès !');
      setTimeout(() => {
        navigate('/tableau-de-bord');
      }, 2000);
    } catch (err) {
      console.error('❌ Erreur complète:', err);
      console.error('📦 Réponse erreur:', err.response);
      
      // Gérer les différentes erreurs
      if (err.response?.status === 401) {
        setErreur('Session expirée. Veuillez vous reconnecter.');
        localStorage.removeItem('token');
        setTimeout(() => navigate('/connexion'), 2000);
      } else if (err.response?.status === 422) {
        // Erreur de validation
        const errors = err.response.data.errors || {};
        const messages = Object.values(errors).flat().join(', ');
        setErreur(`Erreur de validation: ${messages}`);
      } else {
        const message = err.response?.data?.message || 'Erreur lors de l\'envoi de la demande.';
        setErreur(message);
      }
    } finally {
      setSoumission(false);
    }
  };

  // ---- Annuler ----
  const handleAnnuler = () => {
    navigate('/tableau-de-bord');
  };

  // ---- Gestion de la déconnexion ----
  const gererDeconnexion = () => {
    deconnecter();
    navigate('/connexion');
    setMenuProfilOuvert(false);
  };

  // ---- Toggle menu profil ----
  const toggleMenuProfil = () => {
    setMenuProfilOuvert(!menuProfilOuvert);
  };

  if (!utilisateur || chargement) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#F3F4F6' }}>
        <div style={{ fontSize: 16, color: '#6B7280' }}>Chargement...</div>
      </div>
    );
  }

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
            { icon: Home, label: 'Tableau de bord', actif: false, lien: '/tableau-de-bord' },
            { icon: FileText, label: 'Mes demandes', actif: false, lien: '/tableau-de-bord' },
            { icon: Plus, label: 'Nouvelle demande', actif: true, lien: '/nouvelle-demande' },
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
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <Link to="/tableau-de-bord" style={{ color: '#4F46E5', textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
              <ArrowLeft size={18} />
              <span style={{ fontSize: 13 }}>Retour</span>
            </Link>
            <h1 style={{ margin: 0, fontSize: 24, fontWeight: 700, color: '#111827' }}>Nouvelle demande</h1>
          </div>
          <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
            <button style={{ width: 38, height: 38, borderRadius: 10, border: '1px solid #E5E7EB', background: '#FFFFFF', color: '#6B7280', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Bell size={16} />
            </button>

            {/* Menu profil */}
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
                  <div style={{ padding: '8px 12px' }}>
                    <button
                      onClick={() => { setMenuProfilOuvert(false); navigate('/mon-compte'); }}
                      style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#111827', cursor: 'pointer', fontSize: 13, transition: 'background 0.2s' }}
                      onMouseEnter={(e) => e.currentTarget.style.background = '#F3F4F6'}
                      onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}
                    >
                      <UserCircle size={16} color="#4F46E5" />
                      <span style={{ flex: 1, textAlign: 'left' }}>Mon compte</span>
                      <ChevronRight size={14} color="#9CA3AF" />
                    </button>
                    <button
                      onClick={() => { setMenuProfilOuvert(false); navigate('/changer-mot-de-passe'); }}
                      style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#111827', cursor: 'pointer', fontSize: 13, transition: 'background 0.2s' }}
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
                        style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: '#DC2626', cursor: 'pointer', fontSize: 13, transition: 'background 0.2s' }}
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

        {/* Messages */}
        {erreur && (
          <div style={{ padding: '12px 16px', borderRadius: 10, background: '#FEE2E2', border: '1px solid #DC2626', color: '#991B1B', fontSize: 13, marginBottom: 24, display: 'flex', alignItems: 'center', gap: 8 }}>
            <AlertCircle size={15} /> {erreur}
          </div>
        )}
        {succes && (
          <div style={{ padding: '12px 16px', borderRadius: 10, background: '#D1FAE5', border: '1px solid #10B981', color: '#065F46', fontSize: 13, marginBottom: 24, display: 'flex', alignItems: 'center', gap: 8 }}>
            <CheckCircle size={15} /> {succes}
          </div>
        )}

        {/* ===== FORMULAIRE ===== */}
        <form onSubmit={handleSubmit} style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', padding: 28, boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>

          {/* Section : Demandeur */}
          <div style={{ marginBottom: 24 }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 16px 0', display: 'flex', alignItems: 'center', gap: 8 }}>
              <User size={18} color="#4F46E5" />
              Informations du demandeur
            </h3>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Nom *</label>
                <input
                  type="text"
                  name="demandeur_nom"
                  value={form.demandeur_nom}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Prénom *</label>
                <input
                  type="text"
                  name="demandeur_prenom"
                  value={form.demandeur_prenom}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div style={{ gridColumn: 'span 2' }}>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Adresse *</label>
                <input
                  type="text"
                  name="demandeur_adresse"
                  value={form.demandeur_adresse}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Relation</label>
                <input
                  type="text"
                  name="demandeur_relation"
                  value={form.demandeur_relation}
                  onChange={handleChange}
                  placeholder="Ex: père, mère, tuteur..."
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Contact *</label>
                <input
                  type="tel"
                  name="demandeur_contact"
                  value={form.demandeur_contact}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
            </div>
          </div>

          <hr style={{ border: 'none', borderTop: '1px solid #E5E7EB', margin: '24px 0' }} />

          {/* Section : Personne concernée */}
          <div style={{ marginBottom: 24 }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 16px 0', display: 'flex', alignItems: 'center', gap: 8 }}>
              <User size={18} color="#8B5CF6" />
              Personne concernée par l'acte
            </h3>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Nom *</label>
                <input
                  type="text"
                  name="personne_nom"
                  value={form.personne_nom}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Prénom *</label>
                <input
                  type="text"
                  name="personne_prenom"
                  value={form.personne_prenom}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Numéro d'acte (si connu)</label>
                <input
                  type="text"
                  name="personne_numero_acte"
                  value={form.personne_numero_acte}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Lieu de naissance *</label>
                <input
                  type="text"
                  name="personne_lieu_naissance"
                  value={form.personne_lieu_naissance}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
              <div style={{ gridColumn: 'span 2' }}>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Date de naissance *</label>
                <input
                  type="date"
                  name="personne_date_naissance"
                  value={form.personne_date_naissance}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', transition: 'border-color 0.2s' }}
                  onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                  onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                />
              </div>
            </div>
          </div>

          <hr style={{ border: 'none', borderTop: '1px solid #E5E7EB', margin: '24px 0' }} />

          {/* Section : Type d'acte */}
          <div style={{ marginBottom: 24 }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 16px 0' }}>Type d'acte demandé *</h3>
            <div style={{ display: 'flex', gap: 20, flexWrap: 'wrap' }}>
              {[
                { value: 'naissance', label: 'Acte de naissance' },
                { value: 'mariage', label: 'Acte de mariage' },
                { value: 'deces', label: 'Acte de décès' },
                { value: 'divorce', label: 'Acte de divorce' },
              ].map(({ value, label }) => (
                <label key={value} style={{ display: 'flex', alignItems: 'center', gap: 8, cursor: 'pointer', fontSize: 13, color: '#1F2937' }}>
                  <input
                    type="radio"
                    name="type_acte"
                    value={value}
                    checked={form.type_acte === value}
                    onChange={handleChange}
                    style={{ accentColor: '#4F46E5', width: 16, height: 16, cursor: 'pointer' }}
                  />
                  {label}
                </label>
              ))}
            </div>
          </div>

          <hr style={{ border: 'none', borderTop: '1px solid #E5E7EB', margin: '24px 0' }} />

          {/* Section : Service */}
          <div style={{ marginBottom: 28 }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 16px 0' }}>Service *</h3>
            <div style={{ display: 'flex', gap: 24, flexWrap: 'wrap' }}>
              {[
                { value: 'standard', label: 'Standard', desc: 'Délai normal', icon: Shield, color: '#4F46E5' },
                { value: 'express', label: 'Express', desc: 'Traitement prioritaire', icon: Zap, color: '#D97706' },
              ].map(({ value, label, desc, icon: Icon, color }) => (
                <label key={value} style={{ display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer', padding: '10px 16px', borderRadius: 10, border: `2px solid ${form.service === value ? color : '#E5E7EB'}`, background: form.service === value ? `${color}10` : 'transparent', transition: 'all 0.2s' }}>
                  <input
                    type="radio"
                    name="service"
                    value={value}
                    checked={form.service === value}
                    onChange={handleChange}
                    style={{ accentColor: color, width: 16, height: 16, cursor: 'pointer' }}
                  />
                  <Icon size={16} color={color} />
                  <div>
                    <div style={{ fontSize: 13, fontWeight: 500, color: '#111827' }}>{label}</div>
                    <div style={{ fontSize: 11, color: '#6B7280' }}>{desc}</div>
                  </div>
                </label>
              ))}
            </div>
          </div>

          {/* Boutons */}
          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', borderTop: '1px solid #E5E7EB', paddingTop: 20 }}>
            <button
              type="button"
              onClick={handleAnnuler}
              style={{
                padding: '10px 24px',
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
              disabled={soumission}
              style={{
                padding: '10px 28px',
                borderRadius: 8,
                border: 'none',
                background: 'linear-gradient(135deg,#6366F1,#8B5CF6)',
                color: '#fff',
                cursor: soumission ? 'not-allowed' : 'pointer',
                fontSize: 13,
                fontWeight: 600,
                opacity: soumission ? 0.7 : 1,
                display: 'flex',
                alignItems: 'center',
                gap: 8,
                transition: 'all 0.2s'
              }}
            >
              <Send size={16} />
              {soumission ? 'Envoi en cours...' : 'Envoyer la demande'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}