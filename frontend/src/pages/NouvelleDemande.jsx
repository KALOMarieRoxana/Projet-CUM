import { useState, useEffect, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import {
  FileText, Clock, CheckCircle, XCircle, LogOut, Plus,
  User, Phone, MapPin, Mail, Bell, Settings, ChevronRight,
  Zap, Shield, TrendingUp, AlertCircle, Home, ChevronDown,
  UserCircle, Edit, HelpCircle, Award, Key, X, Eye, EyeOff,
  ArrowLeft, Send, Save, Calendar, Trash2, ShoppingCart,
  Heart, Users, HeartPulse, Scale
} from 'lucide-react';
import logo from '../assets/image/logo.png';

const LABELS_TYPE = {
  naissance: 'Acte de naissance',
  mariage: 'Acte de mariage',
  deces: 'Acte de décès',
  divorce: 'Acte de divorce',
};

const OPTIONS_RELATION = [
  { value: 'moi_meme', label: 'Moi-même' },
  { value: 'parent', label: 'Parent (père/mère)' },
  { value: 'frere', label: 'Frère' },
  { value: 'soeur', label: 'Sœur' },
  { value: 'cousin', label: 'Cousin' },
  { value: 'cousine', label: 'Cousine' },
  { value: 'tuteur', label: 'Tuteur / Tutrice' },
  { value: 'epoux', label: 'Époux / Épouse' },
  { value: 'enfant', label: 'Enfant' },
  { value: 'autre', label: 'Autre' },
];

// --- CHAMPS SPÉCIFIQUES POUR CHAQUE TYPE D'ACTE ---
const CHAMPS_SPECIFIQUES = {
  naissance: [
    { name: 'personne_sexe', label: 'Sexe', type: 'select', options: [{ value: 'M', label: 'Masculin' }, { value: 'F', label: 'Féminin' }], required: true },
    { name: 'pere_nom', label: 'Nom du père', type: 'text', required: true, placeholder: 'Ex: KOUADIO' },
    { name: 'pere_prenom', label: 'Prénom du père', type: 'text', required: true, placeholder: 'Ex: Pierre' },
    { name: 'mere_nom', label: 'Nom de la mère', type: 'text', required: true, placeholder: 'Ex: KOUADIO' },
    { name: 'mere_prenom', label: 'Prénom de la mère', type: 'text', required: true, placeholder: 'Ex: Marie' },
  ],
  mariage: [
    { name: 'epoux_nom', label: 'Nom de l\'époux', type: 'text', required: true, placeholder: 'Ex: KOUADIO' },
    { name: 'epoux_prenom', label: 'Prénom de l\'époux', type: 'text', required: true, placeholder: 'Ex: Jean' },
    { name: 'epoux_lieu_naissance', label: 'Lieu naissance époux', type: 'text', required: true, placeholder: 'Ex: Abidjan' },
    { name: 'epoux_date_naissance', label: 'Date naissance époux', type: 'date', required: true },
    { name: 'epouse_nom', label: 'Nom de l\'épouse', type: 'text', required: true, placeholder: 'Ex: KOUADIO' },
    { name: 'epouse_prenom', label: 'Prénom de l\'épouse', type: 'text', required: true, placeholder: 'Ex: Sophie' },
    { name: 'epouse_lieu_naissance', label: 'Lieu naissance épouse', type: 'text', required: true, placeholder: 'Ex: Bouaké' },
    { name: 'epouse_date_naissance', label: 'Date naissance épouse', type: 'date', required: true },
    { name: 'date_mariage', label: 'Date du mariage', type: 'date', required: true },
    { name: 'lieu_mariage', label: 'Lieu du mariage', type: 'text', required: true, placeholder: 'Ex: Mairie de Cocody' },
  ],
  deces: [
    { name: 'defunt_nom', label: 'Nom du défunt', type: 'text', required: true, placeholder: 'Ex: KOUADIO' },
    { name: 'defunt_prenom', label: 'Prénom du défunt', type: 'text', required: true, placeholder: 'Ex: Jean' },
    { name: 'defunt_lieu_naissance', label: 'Lieu naissance défunt', type: 'text', required: true, placeholder: 'Ex: Abidjan' },
    { name: 'defunt_date_naissance', label: 'Date naissance défunt', type: 'date', required: true },
    { name: 'date_deces', label: 'Date du décès', type: 'date', required: true },
    { name: 'lieu_deces', label: 'Lieu du décès', type: 'text', required: true, placeholder: 'Ex: CHU de Treichville' },
    { name: 'cause_deces', label: 'Cause du décès', type: 'text', required: false, placeholder: 'Ex: Accident de la route' },
  ],
  divorce: [
    { name: 'conjoint_nom', label: 'Nom du conjoint', type: 'text', required: true, placeholder: 'Ex: KOUADIO' },
    { name: 'conjoint_prenom', label: 'Prénom du conjoint', type: 'text', required: true, placeholder: 'Ex: Jean' },
    { name: 'conjointe_nom', label: 'Nom de la conjointe', type: 'text', required: true, placeholder: 'Ex: KOUADIO' },
    { name: 'conjointe_prenom', label: 'Prénom de la conjointe', type: 'text', required: true, placeholder: 'Ex: Marie' },
    { name: 'date_mariage', label: 'Date du mariage', type: 'date', required: true },
    { name: 'date_demande_divorce', label: 'Date demande divorce', type: 'date', required: true },
    { name: 'motif', label: 'Motif du divorce', type: 'text', required: false, placeholder: 'Ex: Incompatibilité d\'humeur' },
  ],
};

// --- PRIX PAR TYPE ET SERVICE ---
const PRIX = {
  naissance: { standard: 5000, express: 10000 },
  mariage: { standard: 7000, express: 12000 },
  deces: { standard: 5000, express: 10000 },
  divorce: { standard: 8000, express: 15000 },
};

// --- ICÔNES POUR CHAQUE TYPE ---
const ICONES_TYPE = {
  naissance: User,
  mariage: Heart,
  deces: HeartPulse,
  divorce: Scale,
};

export default function NouvelleDemande() {
  const { utilisateur, deconnecter } = useAuth();
  const navigate = useNavigate();
  const [profilDetaille, setProfilDetaille] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [soumission, setSoumission] = useState(false);
  const [erreur, setErreur] = useState('');
  const [succes, setSucces] = useState('');
  const [progression, setProgression] = useState('');
  const [typesActes, setTypesActes] = useState([]);

  // Menu profil
  const [menuProfilOuvert, setMenuProfilOuvert] = useState(false);
  const menuRef = useRef(null);

  // ---- État du formulaire (informations communes) ----
  const [form, setForm] = useState({
    demandeur_nom: '',
    demandeur_prenom: '',
    demandeur_adresse: '',
    demandeur_relation: 'moi_meme',
    demandeur_contact: '',
    personne_nom: '',
    personne_prenom: '',
    personne_numero_acte: '',
    personne_lieu_naissance: '',
    personne_date_naissance: '',
    service: 'standard',
  });

  // ---- État pour les champs spécifiques de l'acte en cours d'ajout ----
  const [detailsActe, setDetailsActe] = useState({});

  // ---- Sélecteur d'ajout d'acte ----
  const [selectionActe, setSelectionActe] = useState({
    type_acte: 'naissance',
    quantite: 1,
  });

  // ---- Liste des actes ajoutés (panier) ----
  const [actesAjoutes, setActesAjoutes] = useState([]);

  const estMoiMeme = form.demandeur_relation === 'moi_meme';

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

  // ---- Chargement des types d'actes ----
  useEffect(() => {
    const chargerTypesActes = async () => {
      try {
        const res = await api.get('/types-actes');
        setTypesActes(res.data.types || []);
      } catch (err) {
        console.error('Erreur chargement types actes:', err);
        setTypesActes([]);
      }
    };
    chargerTypesActes();
  }, []);

  // ---- Réinitialiser les détails quand le type d'acte change ----
  useEffect(() => {
    const champs = CHAMPS_SPECIFIQUES[selectionActe.type_acte] || [];
    const initialDetails = {};
    champs.forEach(champ => {
      initialDetails[champ.name] = '';
    });
    setDetailsActe(initialDetails);
  }, [selectionActe.type_acte]);

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
        demandeur_contact: user.contact || '',
        personne_nom: user.nom || '',
        personne_prenom: user.prenom || '',
      }));
    } catch (err) {
      setErreur('Impossible de charger votre profil.');
    } finally {
      setChargement(false);
    }
  };

  // ---- Quand la relation change, gérer le cas "Moi-même" ----
  useEffect(() => {
    if (estMoiMeme) {
      setForm(prev => ({
        ...prev,
        personne_nom: prev.demandeur_nom,
        personne_prenom: prev.demandeur_prenom,
      }));
    }
  }, [form.demandeur_relation, form.demandeur_nom, form.demandeur_prenom]);

  // ---- Gestion des champs communs ----
  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  };

  // ---- Gestion des champs spécifiques ----
  const handleDetailsChange = (e) => {
    const { name, value } = e.target;
    setDetailsActe(prev => ({ ...prev, [name]: value }));
  };

  // ---- Gestion du sélecteur d'ajout d'acte ----
  const handleSelectionActeChange = (e) => {
    const { name, value } = e.target;
    setSelectionActe(prev => ({
      ...prev,
      [name]: name === 'quantite' ? Math.max(1, parseInt(value) || 1) : value,
    }));
  };

  // ---- Ajouter un acte au panier ----
  const ajouterActe = () => {
    // Vérifier que tous les champs obligatoires sont remplis
    const champs = CHAMPS_SPECIFIQUES[selectionActe.type_acte] || [];
    const champsObligatoires = champs.filter(c => c.required);
    const champsManquants = champsObligatoires.filter(champ => !detailsActe[champ.name]?.trim());

    if (champsManquants.length > 0) {
      setErreur(`Veuillez remplir tous les champs obligatoires pour ${LABELS_TYPE[selectionActe.type_acte]}: ${champsManquants.map(c => c.label).join(', ')}`);
      return;
    }

    setActesAjoutes(prev => {
      const existant = prev.find(a => a.type_acte === selectionActe.type_acte);
      if (existant) {
        return prev.map(a =>
          a.type_acte === selectionActe.type_acte
            ? { ...a, quantite: a.quantite + selectionActe.quantite }
            : a
        );
      }
      return [...prev, { ...selectionActe, details: { ...detailsActe } }];
    });

    // Réinitialiser les détails après ajout
    const champsReset = CHAMPS_SPECIFIQUES[selectionActe.type_acte] || [];
    const initialDetails = {};
    champsReset.forEach(champ => {
      initialDetails[champ.name] = '';
    });
    setDetailsActe(initialDetails);
    setErreur('');
  };

  // ---- Retirer un acte du panier ----
  const retirerActe = (type_acte) => {
    setActesAjoutes(prev => prev.filter(a => a.type_acte !== type_acte));
  };

  // ---- Modifier la quantité d'un acte déjà ajouté ----
  const modifierQuantite = (type_acte, quantite) => {
    const q = Math.max(1, parseInt(quantite) || 1);
    setActesAjoutes(prev => prev.map(a =>
      a.type_acte === type_acte ? { ...a, quantite: q } : a
    ));
  };

  // ---- Calcul du prix total ----
  const totalActes = actesAjoutes.reduce((sum, a) => sum + a.quantite, 0);
  const prixTotal = actesAjoutes.reduce((sum, a) => {
    const prix = PRIX[a.type_acte]?.[form.service] || 0;
    return sum + (prix * a.quantite);
  }, 0);

  // ---- Vérification de l'authentification ----
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

  // ---- Soumission avec vérification ----
  const handleSubmit = async (e) => {
    e.preventDefault();
    setErreur('');
    setSucces('');
    setSoumission(true);

    // 1️⃣ Vérifier l'authentification
    const estAuthentifie = await verifierAuthentification();
    if (!estAuthentifie) {
      setSoumission(false);
      return;
    }

    // 2️⃣ Vérifier qu'au moins un acte a été ajouté
    if (actesAjoutes.length === 0) {
      setErreur('Veuillez ajouter au moins un acte à votre demande.');
      setSoumission(false);
      return;
    }

    // 3️⃣ Vérifier les champs obligatoires communs
    const requisDemandeur = ['demandeur_nom', 'demandeur_prenom', 'demandeur_adresse', 'demandeur_contact'];
    const requisPersonne = ['personne_nom', 'personne_prenom', 'personne_lieu_naissance', 'personne_date_naissance'];
    const requis = [...requisDemandeur, ...requisPersonne];

    const manquant = requis.filter(field => !form[field]?.trim());
    if (manquant.length) {
      setErreur(
        estMoiMeme
          ? 'Veuillez compléter les informations manquantes (lieu et date de naissance notamment).'
          : 'Veuillez remplir tous les champs obligatoires.'
      );
      setSoumission(false);
      return;
    }

    // 4️⃣ Construire le payload
    const payload = {
      demandeur_nom: form.demandeur_nom,
      demandeur_prenom: form.demandeur_prenom,
      demandeur_adresse: form.demandeur_adresse,
      demandeur_relation: OPTIONS_RELATION.find(o => o.value === form.demandeur_relation)?.label || form.demandeur_relation,
      demandeur_contact: form.demandeur_contact,
      service: form.service,
      demandes: actesAjoutes.map(acte => ({
        type_acte_id: typesActes.find(t => t.type_acte === acte.type_acte)?.id,
        quantite: acte.quantite,
        details: acte.details,
      }))
    };

    // Vérifier que tous les type_acte_id sont résolus
    const invalides = payload.demandes.filter(d => !d.type_acte_id);
    if (invalides.length > 0) {
      setErreur('Les types d\'actes ne sont pas encore chargés. Veuillez réessayer dans un instant.');
      setSoumission(false);
      return;
    }

    try {
      setProgression('📤 Envoi de la demande groupée...');

      const response = await api.post('/demandes/groupe', payload);
      console.log('✅ Demande groupée envoyée:', response.data);

      setProgression('');
      setSucces(
        `✅ Demande groupée envoyée avec succès !\n\n` +
        `📌 Référence : ${response.data.reference}\n` +
        `💰 Prix total : ${new Intl.NumberFormat('fr-FR').format(response.data.prix_total)} FCFA\n` +
        `📋 ${actesAjoutes.reduce((sum, a) => sum + a.quantite, 0)} acte(s) demandé(s)\n\n` +
        `Votre demande a bien été transmise à l'administration.\n` +
        `Vous recevrez une réponse dans quelques minutes.`
      );

      setTimeout(() => {
        navigate('/tableau-de-bord');
      }, 5000);

    } catch (err) {
      console.error('❌ Erreur:', err);

      if (err.response?.status === 401) {
        setErreur('Session expirée. Veuillez vous reconnecter.');
        localStorage.removeItem('token');
        setTimeout(() => navigate('/connexion'), 2000);
      } else if (err.response?.status === 422) {
        const errors = err.response.data.errors || {};
        const messages = Object.values(errors).flat().join(' ');
        setErreur(`Erreur de validation: ${messages}`);
      } else {
        setErreur(err.response?.data?.message || 'Erreur lors de l\'envoi de la demande groupée.');
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

  // Récupérer les champs spécifiques pour le type d'acte sélectionné
  const champsSpecifiques = CHAMPS_SPECIFIQUES[selectionActe.type_acte] || [];
  const IconeType = ICONES_TYPE[selectionActe.type_acte] || FileText;

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
          <div style={{ padding: '16px 20px', borderRadius: 12, background: '#D1FAE5', border: '1px solid #10B981', color: '#065F46', fontSize: 13, marginBottom: 24, display: 'flex', alignItems: 'flex-start', gap: 12 }}>
            <CheckCircle size={20} style={{ flexShrink: 0, marginTop: 2 }} />
            <div style={{ whiteSpace: 'pre-line' }}>
              <div style={{ fontWeight: 700, marginBottom: 4 }}>🎉 Demande envoyée !</div>
              <div>{succes}</div>
            </div>
          </div>
        )}
        {progression && (
          <div style={{ padding: '12px 16px', borderRadius: 10, background: '#EEF2FF', border: '1px solid #6366F1', color: '#4338CA', fontSize: 13, marginBottom: 24, display: 'flex', alignItems: 'center', gap: 8 }}>
            <Clock size={15} /> {progression}
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
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Relation avec la personne concernée *</label>
                <select
                  name="demandeur_relation"
                  value={form.demandeur_relation}
                  onChange={handleChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', cursor: 'pointer' }}
                >
                  {OPTIONS_RELATION.map(opt => (
                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                  ))}
                </select>
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
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 8px 0', display: 'flex', alignItems: 'center', gap: 8 }}>
              <User size={18} color="#8B5CF6" />
              Personne concernée par l'acte
            </h3>
            {estMoiMeme && (
              <p style={{ fontSize: 12, color: '#6B7280', margin: '0 0 16px 0', fontStyle: 'italic' }}>
                Vous avez choisi "Moi-même" : votre nom et prénom sont déjà renseignés. Complétez uniquement les informations manquantes ci-dessous.
              </p>
            )}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Nom *</label>
                <input
                  type="text"
                  name="personne_nom"
                  value={form.personne_nom}
                  onChange={handleChange}
                  readOnly={estMoiMeme}
                  style={{
                    width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB',
                    fontSize: 13, color: '#111827', background: estMoiMeme ? '#F3F4F6' : '#F9FAFB',
                    outline: 'none', transition: 'border-color 0.2s',
                    cursor: estMoiMeme ? 'not-allowed' : 'text'
                  }}
                  onFocus={(e) => !estMoiMeme && (e.currentTarget.style.borderColor = '#6366F1')}
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
                  readOnly={estMoiMeme}
                  style={{
                    width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB',
                    fontSize: 13, color: '#111827', background: estMoiMeme ? '#F3F4F6' : '#F9FAFB',
                    outline: 'none', transition: 'border-color 0.2s',
                    cursor: estMoiMeme ? 'not-allowed' : 'text'
                  }}
                  onFocus={(e) => !estMoiMeme && (e.currentTarget.style.borderColor = '#6366F1')}
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

          {/* ============================================================ */}
          {/* Section : Ajout d'actes avec champs spécifiques (PANIER) */}
          {/* ============================================================ */}
          <div style={{ marginBottom: 24 }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 16px 0', display: 'flex', alignItems: 'center', gap: 8 }}>
              <ShoppingCart size={18} color="#4F46E5" />
              Actes demandés *
            </h3>

            {/* Sélecteur d'ajout */}
            <div style={{ display: 'flex', gap: 12, alignItems: 'flex-end', marginBottom: 16, flexWrap: 'wrap' }}>
              <div style={{ flex: 2, minWidth: 200 }}>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Type d'acte</label>
                <select
                  name="type_acte"
                  value={selectionActe.type_acte}
                  onChange={handleSelectionActeChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none', cursor: 'pointer' }}
                >
                  {Object.entries(LABELS_TYPE).map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
              </div>
              <div style={{ width: 100 }}>
                <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>Quantité</label>
                <input
                  type="number"
                  name="quantite"
                  min="1"
                  value={selectionActe.quantite}
                  onChange={handleSelectionActeChange}
                  style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#F9FAFB', outline: 'none' }}
                />
              </div>
              <button
                type="button"
                onClick={ajouterActe}
                style={{
                  padding: '10px 20px', borderRadius: 8, border: 'none',
                  background: '#4F46E5', color: '#fff', cursor: 'pointer',
                  fontSize: 13, fontWeight: 600, display: 'flex', alignItems: 'center', gap: 6,
                  height: 38
                }}
              >
                <Plus size={15} /> Ajouter
              </button>
            </div>

            {/* 🔥 CHAMPS SPÉCIFIQUES DYNAMIQUES */}
            {champsSpecifiques.length > 0 && (
              <div style={{
                marginBottom: 16,
                padding: '16px 20px',
                background: '#F8FAFC',
                borderRadius: 10,
                border: '2px solid #E2E8F0'
              }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12 }}>
                  <IconeType size={18} color="#4F46E5" />
                  <h4 style={{ margin: 0, fontSize: 14, fontWeight: 600, color: '#111827' }}>
                    Détails pour {LABELS_TYPE[selectionActe.type_acte]}
                  </h4>
                </div>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                  {champsSpecifiques.map(champ => (
                    <div key={champ.name} style={{ gridColumn: champ.type === 'textarea' ? 'span 2' : 'span 1' }}>
                      <label style={{ fontSize: 12, fontWeight: 500, color: '#374151', display: 'block', marginBottom: 4 }}>
                        {champ.label} {champ.required && <span style={{ color: '#DC2626' }}>*</span>}
                      </label>
                      {champ.type === 'select' ? (
                        <select
                          name={champ.name}
                          value={detailsActe[champ.name] || ''}
                          onChange={handleDetailsChange}
                          style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#FFFFFF', outline: 'none', cursor: 'pointer' }}
                        >
                          <option value="">Sélectionnez...</option>
                          {champ.options.map(opt => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                          ))}
                        </select>
                      ) : (
                        <input
                          type={champ.type}
                          name={champ.name}
                          value={detailsActe[champ.name] || ''}
                          onChange={handleDetailsChange}
                          placeholder={champ.placeholder}
                          style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, color: '#111827', background: '#FFFFFF', outline: 'none', transition: 'border-color 0.2s' }}
                          onFocus={(e) => e.currentTarget.style.borderColor = '#6366F1'}
                          onBlur={(e) => e.currentTarget.style.borderColor = '#E5E7EB'}
                        />
                      )}
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Liste des actes ajoutés */}
            {actesAjoutes.length === 0 ? (
              <div style={{ padding: '16px', borderRadius: 8, border: '1px dashed #E5E7EB', textAlign: 'center', color: '#9CA3AF', fontSize: 13 }}>
                Aucun acte ajouté pour le moment. Remplissez les champs ci-dessus et cliquez sur "Ajouter".
              </div>
            ) : (
              <div style={{ border: '1px solid #E5E7EB', borderRadius: 8, overflow: 'hidden' }}>
                {actesAjoutes.map((acte, idx) => (
                  <div
                    key={idx}
                    style={{
                      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                      padding: '10px 14px',
                      borderBottom: idx < actesAjoutes.length - 1 ? '1px solid #F3F4F6' : 'none',
                      background: '#FAFAFA'
                    }}
                  >
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                      <FileText size={15} color="#4F46E5" />
                      <span style={{ fontSize: 13, color: '#111827', fontWeight: 500 }}>{LABELS_TYPE[acte.type_acte]}</span>
                      <span style={{ fontSize: 11, color: '#6B7280' }}>
                        {new Intl.NumberFormat('fr-FR').format(PRIX[acte.type_acte]?.[form.service] || 0)} FCFA
                      </span>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                      <label style={{ fontSize: 12, color: '#6B7280' }}>Qté :</label>
                      <input
                        type="number"
                        min="1"
                        value={acte.quantite}
                        onChange={(e) => modifierQuantite(acte.type_acte, e.target.value)}
                        style={{ width: 56, padding: '4px 8px', borderRadius: 6, border: '1px solid #E5E7EB', fontSize: 13, textAlign: 'center' }}
                      />
                      <button
                        type="button"
                        onClick={() => retirerActe(acte.type_acte)}
                        style={{ border: 'none', background: 'transparent', color: '#DC2626', cursor: 'pointer', display: 'flex', alignItems: 'center' }}
                        aria-label="Retirer cet acte"
                      >
                        <Trash2 size={15} />
                      </button>
                    </div>
                  </div>
                ))}
                <div style={{ padding: '10px 14px', background: '#EEF2FF', fontSize: 13, fontWeight: 600, color: '#4338CA', display: 'flex', justifyContent: 'space-between' }}>
                  <span>Total : {totalActes} exemplaire(s)</span>
                  <span>Prix total : {new Intl.NumberFormat('fr-FR').format(prixTotal)} FCFA</span>
                </div>
              </div>
            )}
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
              {soumission ? 'Envoi en cours...' : `Envoyer (${totalActes} acte${totalActes > 1 ? 's' : ''})`}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}