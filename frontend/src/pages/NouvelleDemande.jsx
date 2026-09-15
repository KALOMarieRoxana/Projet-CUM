import { useState, useEffect, useRef, useMemo } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import ModalEstimation from '../components/ModalEstimation';
import {
  FileText, Clock, CheckCircle, LogOut, Plus,
  User, Bell, ChevronDown, UserCircle, Key, ChevronRight,
  Zap, AlertCircle, Home, ArrowLeft, Send, Trash2, ShoppingCart,
  Heart, Users, HeartPulse, Scale, Globe
} from 'lucide-react';
import logo from '../assets/image/logo.png';

const LABELS_TYPE = {
  naissance: 'Acte de naissance',
  mariage: 'Acte de mariage',
  deces: 'Acte de décès',
  divorces: 'Acte de divorce',
};

const OPTIONS_LANGUE = [
  { value: 'mg', label: 'Malgache' },
  { value: 'fr', label: 'Français' },
];

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

const CHAMPS_SPECIFIQUES = {
  naissance: [
    { name: 'personne_sexe', label: 'Sexe', type: 'select', options: [{ value: 'M', label: 'Masculin' }, { value: 'F', label: 'Féminin' }], required: true },
    { name: 'pere_nom', label: 'Nom du père', type: 'text', required: true, placeholder: 'Ex: RAKOTO' },
    { name: 'pere_prenom', label: 'Prénom du père', type: 'text', required: true, placeholder: 'Ex: Jean' },
    { name: 'mere_nom', label: 'Nom de la mère', type: 'text', required: true, placeholder: 'Ex: RASOA' },
    { name: 'mere_prenom', label: 'Prénom de la mère', type: 'text', required: true, placeholder: 'Ex: Marie' },
  ],
  mariage: [
    { name: 'epoux_nom', label: "Nom de l'époux", type: 'text', required: true, placeholder: 'Ex: RAKOTO' },
    { name: 'epoux_prenom', label: "Prénom de l'époux", type: 'text', required: true, placeholder: 'Ex: Jean' },
    { name: 'epoux_lieu_naissance', label: 'Lieu naissance époux', type: 'text', required: true, placeholder: 'Ex: Antananarivo' },
    { name: 'epoux_date_naissance', label: 'Date naissance époux', type: 'date', required: true },
    { name: 'epouse_nom', label: "Nom de l'épouse", type: 'text', required: true, placeholder: 'Ex: RASOA' },
    { name: 'epouse_prenom', label: "Prénom de l'épouse", type: 'text', required: true, placeholder: 'Ex: Jeanne' },
    { name: 'epouse_lieu_naissance', label: 'Lieu naissance épouse', type: 'text', required: true, placeholder: 'Ex: Antsirabe' },
    { name: 'epouse_date_naissance', label: 'Date naissance épouse', type: 'date', required: true },
    { name: 'date_mariage', label: 'Date du mariage', type: 'date', required: true },
    { name: 'lieu_mariage', label: 'Lieu du mariage', type: 'text', required: true, placeholder: 'Ex: Mairie Antananarivo' },
  ],
  deces: [
    { name: 'defunt_nom', label: 'Nom du défunt', type: 'text', required: true, placeholder: 'Ex: RAKOTO' },
    { name: 'defunt_prenom', label: 'Prénom du défunt', type: 'text', required: true, placeholder: 'Ex: Jean' },
    { name: 'defunt_lieu_naissance', label: 'Lieu naissance défunt', type: 'text', required: true, placeholder: 'Ex: Mahajanga' },
    { name: 'defunt_date_naissance', label: 'Date naissance défunt', type: 'date', required: true },
    { name: 'date_deces', label: 'Date du décès', type: 'date', required: true },
    { name: 'lieu_deces', label: 'Lieu du décès', type: 'text', required: true, placeholder: 'Ex: CHU Antananarivo' },
    { name: 'cause_deces', label: 'Cause du décès', type: 'text', required: false },
  ],
  divorces: [
    { name: 'conjoint_nom', label: 'Nom du conjoint', type: 'text', required: true, placeholder: 'Ex: RAKOTO' },
    { name: 'conjoint_prenom', label: 'Prénom du conjoint', type: 'text', required: true, placeholder: 'Ex: Jean' },
    { name: 'conjointe_nom', label: 'Nom de la conjointe', type: 'text', required: true, placeholder: 'Ex: RASOA' },
    { name: 'conjointe_prenom', label: 'Prénom de la conjointe', type: 'text', required: true, placeholder: 'Ex: Marie' },
    { name: 'date_mariage', label: 'Date du mariage', type: 'date', required: true },
    { name: 'date_demande_divorce', label: 'Date demande divorce', type: 'date', required: true },
    { name: 'motif', label: 'Motif du divorce', type: 'text', required: false },
  ],
};

const ICONES_TYPE = {
  naissance: User,
  mariage: Heart,
  deces: HeartPulse,
  divorces: Scale,
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

  // ✅ States pour le modal d'estimation
  const [modalEstimation, setModalEstimation] = useState(false);
  const [demandeEstimee, setDemandeEstimee] = useState(null);

  const [menuProfilOuvert, setMenuProfilOuvert] = useState(false);
  const menuRef = useRef(null);

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

  const [detailsActe, setDetailsActe] = useState({});

  const [selectionActe, setSelectionActe] = useState({
    type_acte: 'naissance',
    langue: 'mg',
    quantite: 1,
    supplement_id: null,
    quantite_supplement: 1,
  });

  const [actesAjoutes, setActesAjoutes] = useState([]);

  const estMoiMeme = form.demandeur_relation === 'moi_meme';

  const supplementsDisponibles = useMemo(() => {
    const typeObj = typesActes.find(t => t.type_acte === selectionActe.type_acte);
    return typeObj?.supplements || [];
  }, [typesActes, selectionActe.type_acte]);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuProfilOuvert(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  useEffect(() => {
    if (!utilisateur) {
      navigate('/connexion');
      return;
    }
    chargerProfil();
  }, [utilisateur, navigate]);

  useEffect(() => {
    const chargerTypesActes = async () => {
      try {
        const res = await api.get('/types-actes');
        const data = res.data.types || res.data || [];
        setTypesActes(data);
      } catch (err) {
        console.error('Erreur chargement types actes:', err);
        setTypesActes([]);
      }
    };
    chargerTypesActes();
  }, []);

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

  useEffect(() => {
    if (estMoiMeme) {
      setForm(prev => ({
        ...prev,
        personne_nom: prev.demandeur_nom,
        personne_prenom: prev.demandeur_prenom,
      }));
    }
  }, [form.demandeur_relation, form.demandeur_nom, form.demandeur_prenom, estMoiMeme]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  };

  const handleDetailsChange = (e) => {
    const { name, value } = e.target;
    setDetailsActe(prev => ({ ...prev, [name]: value }));
  };

  const handleSelectionActeChange = (e) => {
    const { name, value } = e.target;

    setSelectionActe(prev => {
      if (name === 'type_acte') {
        return { ...prev, type_acte: value, supplement_id: null, quantite_supplement: 1 };
      }
      if (name === 'quantite') {
        return { ...prev, quantite: Math.max(1, parseInt(value, 10) || 1) };
      }
      if (name === 'quantite_supplement') {
        return { ...prev, quantite_supplement: Math.max(1, parseInt(value, 10) || 1) };
      }
      if (name === 'supplement_id') {
        return { ...prev, supplement_id: value ? parseInt(value) : null };
      }
      return { ...prev, [name]: value };
    });
  };

  const getPrixActe = (typeKey, langue, modeService) => {
    const typeObj = typesActes.find(t => t.type_acte === typeKey);
    if (!typeObj) return 0;
    const champ = `montant${modeService.charAt(0).toUpperCase() + modeService.slice(1)}${langue.toUpperCase()}`;
    return parseFloat(typeObj[champ]) || 0;
  };

  const getPrixSupplement = (typeKey, supplementId, langue, modeService) => {
    if (!supplementId) return 0;
    const typeObj = typesActes.find(t => t.type_acte === typeKey);
    if (!typeObj?.supplements) return 0;
    const supp = typeObj.supplements.find(s => s.id === supplementId);
    if (!supp) return 0;
    const champ = `prix_${modeService}_${langue}`;
    return parseFloat(supp[champ]) || 0;
  };

  const getNomSupplement = (typeKey, supplementId) => {
    if (!supplementId) return null;
    const typeObj = typesActes.find(t => t.type_acte === typeKey);
    return typeObj?.supplements?.find(s => s.id === supplementId)?.nom || null;
  };

  const ajouterActe = () => {
    const champs = CHAMPS_SPECIFIQUES[selectionActe.type_acte] || [];
    const champsObligatoires = champs.filter(c => c.required);
    const champsManquants = champsObligatoires.filter(champ => !detailsActe[champ.name]?.trim());

    if (champsManquants.length > 0) {
      setErreur(`Veuillez remplir tous les champs obligatoires : ${champsManquants.map(c => c.label).join(', ')}`);
      return;
    }

    if (supplementsDisponibles.length > 0 && !selectionActe.supplement_id) {
      setErreur('Veuillez sélectionner un type de document.');
      return;
    }

    setActesAjoutes(prev => {
      const indexExistant = prev.findIndex(
        a => a.type_acte === selectionActe.type_acte
          && a.langue === selectionActe.langue
          && a.supplement_id === selectionActe.supplement_id
      );
      if (indexExistant > -1) {
        const copy = [...prev];
        copy[indexExistant].quantite += selectionActe.quantite;
        if (selectionActe.supplement_id) {
          copy[indexExistant].quantite_supplement += selectionActe.quantite_supplement;
        }
        return copy;
      }
      return [...prev, { ...selectionActe, details: { ...detailsActe } }];
    });

    const champsReset = CHAMPS_SPECIFIQUES[selectionActe.type_acte] || [];
    const initialDetails = {};
    champsReset.forEach(champ => {
      initialDetails[champ.name] = '';
    });
    setDetailsActe(initialDetails);
    setErreur('');
  };

  const retirerActe = (index) => {
    setActesAjoutes(prev => prev.filter((_, i) => i !== index));
  };

  const modifierQuantite = (index, quantite) => {
    const q = Math.max(1, parseInt(quantite, 10) || 1);
    setActesAjoutes(prev => prev.map((item, i) => i === index ? { ...item, quantite: q } : item));
  };

  const modifierQuantiteSupp = (index, quantite) => {
    const q = Math.max(1, parseInt(quantite, 10) || 1);
    setActesAjoutes(prev => prev.map((item, i) => i === index ? { ...item, quantite_supplement: q } : item));
  };

  const prixTotal = actesAjoutes.reduce((sum, a) => {
    const prixActe = getPrixActe(a.type_acte, a.langue, form.service);
    const prixSupp = getPrixSupplement(a.type_acte, a.supplement_id, a.langue, form.service);
    const sousTotalActe = prixActe * a.quantite;
    const sousTotalSupp = a.supplement_id ? prixSupp * a.quantite_supplement : 0;
    return sum + sousTotalActe + sousTotalSupp;
  }, 0);

  const totalActes = actesAjoutes.reduce((sum, a) => sum + a.quantite, 0);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErreur('');
    setSucces('');
    setSoumission(true);

    if (actesAjoutes.length === 0) {
      setErreur('Veuillez ajouter au moins un acte à votre demande.');
      setSoumission(false);
      return;
    }

    const requisDemandeur = ['demandeur_nom', 'demandeur_prenom', 'demandeur_adresse', 'demandeur_contact'];
    const requisPersonne = ['personne_nom', 'personne_prenom', 'personne_lieu_naissance', 'personne_date_naissance'];
    const requis = [...requisDemandeur, ...requisPersonne];

    const manquant = requis.filter(field => !form[field]?.trim());
    if (manquant.length) {
      setErreur('Veuillez remplir tous les champs obligatoires du formulaire.');
      setSoumission(false);
      return;
    }

    const payload = {
      demandeur_nom: form.demandeur_nom,
      demandeur_prenom: form.demandeur_prenom,
      demandeur_adresse: form.demandeur_adresse,
      demandeur_relation: OPTIONS_RELATION.find(o => o.value === form.demandeur_relation)?.label || form.demandeur_relation,
      demandeur_contact: form.demandeur_contact,
      service: form.service,
      personne_nom: form.personne_nom,
      personne_prenom: form.personne_prenom,
      personne_numero_acte: form.personne_numero_acte,
      personne_lieu_naissance: form.personne_lieu_naissance,
      personne_date_naissance: form.personne_date_naissance,
      demandes: actesAjoutes.map(acte => {
        const prixActe = getPrixActe(acte.type_acte, acte.langue, form.service);
        const prixSupp = getPrixSupplement(acte.type_acte, acte.supplement_id, acte.langue, form.service);
        const quantiteSupp = acte.supplement_id ? acte.quantite_supplement : 0;

        return {
          type_acte_id: typesActes.find(t => t.type_acte === acte.type_acte)?.id,
          supplement_id: acte.supplement_id,
          langue: acte.langue,
          quantite: acte.quantite,
          quantite_supplement: quantiteSupp,
          prix_acte: prixActe,
          prix_supplement: prixSupp,
          prix_unitaire: prixActe + prixSupp,
          sous_total: (prixActe * acte.quantite) + (prixSupp * quantiteSupp),
          details: {
            ...acte.details,
            personne_nom: form.personne_nom,
            personne_prenom: form.personne_prenom,
            personne_lieu_naissance: form.personne_lieu_naissance,
            personne_date_naissance: form.personne_date_naissance,
            type_acte: acte.type_acte
          }
        };
      })
    };

    console.log('📤 PAYLOAD COMPLET:', JSON.stringify(payload, null, 2));

    try {
      setProgression('📤 Envoi de la demande...');
      const response = await api.post('/demandes/groupe', payload);

      setProgression('');
      setSucces(`Demande envoyée avec succès ! Référence : ${response.data.reference}`);
      
      // ✅ Calculer la date d'estimation selon le service
      const delaiHeures = form.service === 'express' ? 24 : 72;
      const now = new Date();
      const dateEstimation = new Date(now.getTime() + delaiHeures * 60 * 60 * 1000);
      
      // ✅ Préparer les données pour le modal
      setDemandeEstimee({
        reference: response.data.reference,
        service: form.service,
        delai_heures: delaiHeures,
        created_at: now.toISOString(),
        date_estimation: dateEstimation.toISOString(),
        prix_total: response.data.prix_total,
      });
      
      // ✅ Afficher le modal d'estimation
      setModalEstimation(true);
      
    } catch (err) {
      console.error('Erreur complète:', err);
      console.error('Payload envoyé:', payload);
      setErreur(err.response?.data?.message || 'Erreur lors de l\'envoi de la demande.');
    } finally {
      setSoumission(false);
    }
  };

  const gererDeconnexion = () => {
    deconnecter();
    navigate('/connexion');
  };

  if (!utilisateur || chargement) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#F3F4F6' }}>
        <div style={{ fontSize: 16, color: '#6B7280' }}>Chargement...</div>
      </div>
    );
  }

  const champsSpecifiques = CHAMPS_SPECIFIQUES[selectionActe.type_acte] || [];
  const prixActeApercu = getPrixActe(selectionActe.type_acte, selectionActe.langue, form.service);
  const prixSuppApercu = getPrixSupplement(selectionActe.type_acte, selectionActe.supplement_id, selectionActe.langue, form.service);
  const sousTotalActe = prixActeApercu * selectionActe.quantite;
  const sousTotalSupp = selectionActe.supplement_id ? prixSuppApercu * selectionActe.quantite_supplement : 0;
  const prixTotalApercu = sousTotalActe + sousTotalSupp;

  return (
    <div style={{ display: 'flex', minHeight: '100vh', background: '#F3F4F6', color: '#1F2937', fontFamily: 'Inter, sans-serif' }}>

      {/* ===== SIDEBAR ===== */}
      <div style={{ width: 240, background: '#FFFFFF', borderRight: '1px solid #E5E7EB', display: 'flex', flexDirection: 'column', padding: '24px 0', position: 'fixed', height: '100vh' }}>
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
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, marginBottom: 4, background: actif ? 'rgba(99,102,241,0.08)' : 'transparent', color: actif ? '#4F46E5' : '#6B7280', fontWeight: actif ? 600 : 400 }}>
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

        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 32 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <Link to="/tableau-de-bord" style={{ color: '#4F46E5', textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
              <ArrowLeft size={18} />
              <span style={{ fontSize: 13 }}>Retour</span>
            </Link>
            <h1 style={{ margin: 0, fontSize: 24, fontWeight: 700, color: '#111827' }}>Nouvelle demande d'acte</h1>
          </div>
        </div>

        {erreur && <div style={{ padding: '12px 16px', borderRadius: 10, background: '#FEE2E2', color: '#991B1B', marginBottom: 24, fontSize: 13 }}>{erreur}</div>}
        {succes && <div style={{ padding: '16px 20px', borderRadius: 12, background: '#D1FAE5', color: '#065F46', marginBottom: 24, fontSize: 13 }}>{succes}</div>}
        {progression && <div style={{ padding: '12px 16px', borderRadius: 10, background: '#EEF2FF', color: '#4338CA', marginBottom: 24, fontSize: 13 }}>{progression}</div>}

        <form onSubmit={handleSubmit} style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', padding: 28 }}>

          {/* Section : Demandeur */}
          <div style={{ marginBottom: 24 }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 16px 0', display: 'flex', alignItems: 'center', gap: 8 }}>
              <User size={18} color="#4F46E5" /> Informations du demandeur
            </h3>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Nom *</label>
                <input type="text" name="demandeur_nom" value={form.demandeur_nom} onChange={handleChange} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }} />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Prénom *</label>
                <input type="text" name="demandeur_prenom" value={form.demandeur_prenom} onChange={handleChange} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }} />
              </div>
              <div style={{ gridColumn: 'span 2' }}>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Adresse *</label>
                <input type="text" name="demandeur_adresse" value={form.demandeur_adresse} onChange={handleChange} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }} />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Relation *</label>
                <select name="demandeur_relation" value={form.demandeur_relation} onChange={handleChange} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }}>
                  {OPTIONS_RELATION.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
                </select>
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Contact *</label>
                <input type="tel" name="demandeur_contact" value={form.demandeur_contact} onChange={handleChange} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }} />
              </div>
            </div>
          </div>

          {/* Section : Personne concernée */}
          <div style={{ marginBottom: 24, paddingTop: 20, borderTop: '1px solid #E5E7EB' }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, color: '#111827', margin: '0 0 16px 0', display: 'flex', alignItems: 'center', gap: 8 }}>
              <Users size={18} color="#4F46E5" /> Informations sur la personne concernée
            </h3>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Nom *</label>
                <input type="text" name="personne_nom" value={form.personne_nom} onChange={handleChange} disabled={estMoiMeme} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, background: estMoiMeme ? '#F3F4F6' : '#FFF' }} />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Prénom *</label>
                <input type="text" name="personne_prenom" value={form.personne_prenom} onChange={handleChange} disabled={estMoiMeme} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, background: estMoiMeme ? '#F3F4F6' : '#FFF' }} />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Lieu de naissance *</label>
                <input type="text" name="personne_lieu_naissance" value={form.personne_lieu_naissance} onChange={handleChange} placeholder="Ex: Antananarivo" style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }} />
              </div>
              <div>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Date de naissance *</label>
                <input type="date" name="personne_date_naissance" value={form.personne_date_naissance} onChange={handleChange} style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }} />
              </div>
              <div style={{ gridColumn: 'span 2' }}>
                <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Numéro d'acte de naissance (Optionnel)</label>
                <input type="text" name="personne_numero_acte" value={form.personne_numero_acte} onChange={handleChange} placeholder="Ex: 123/2020" style={{ width: '100%', padding: '10px 12px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }} />
              </div>
            </div>
          </div>

          {/* Mode de traitement */}
          <div style={{ marginBottom: 24, paddingTop: 20, borderTop: '1px solid #E5E7EB' }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, marginBottom: 16, display: 'flex', alignItems: 'center', gap: 8 }}>
              <Zap size={18} color="#4F46E5" /> Mode de traitement
            </h3>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
              <label style={{ padding: 16, borderRadius: 10, border: form.service === 'standard' ? '2px solid #6366F1' : '1px solid #E5E7EB', background: form.service === 'standard' ? '#EEF2FF' : '#FFF', cursor: 'pointer' }}>
                <input type="radio" name="service" value="standard" checked={form.service === 'standard'} onChange={handleChange} style={{ accentColor: '#6366F1' }} />
                <span style={{ marginLeft: 8, fontWeight: 600 }}>Service Standard (72h)</span>
              </label>
              <label style={{ padding: 16, borderRadius: 10, border: form.service === 'express' ? '2px solid #6366F1' : '1px solid #E5E7EB', background: form.service === 'express' ? '#EEF2FF' : '#FFF', cursor: 'pointer' }}>
                <input type="radio" name="service" value="express" checked={form.service === 'express'} onChange={handleChange} style={{ accentColor: '#6366F1' }} />
                <span style={{ marginLeft: 8, fontWeight: 600 }}>Service Express ⚡ (24h)</span>
              </label>
            </div>
          </div>

          {/* SÉLECTION DES ACTES */}
          <div style={{ marginBottom: 24, paddingTop: 20, borderTop: '1px solid #E5E7EB' }}>
            <h3 style={{ fontSize: 15, fontWeight: 600, marginBottom: 16, display: 'flex', alignItems: 'center', gap: 8 }}>
              <ShoppingCart size={18} color="#4F46E5" /> Sélection des actes
            </h3>

            <div style={{ background: '#F9FAFB', padding: 20, borderRadius: 12, border: '1px solid #E5E7EB', marginBottom: 20 }}>

              {/* SECTION 1 : TYPE D'ACTE */}
              <div style={{ marginBottom: 20 }}>
                <div style={{ fontSize: 13, fontWeight: 700, color: '#4F46E5', marginBottom: 12, display: 'flex', alignItems: 'center', gap: 6 }}>
                  <FileText size={16} /> TYPE D'ACTE
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 16, marginBottom: 12 }}>
                  <div>
                    <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Acte</label>
                    <select name="type_acte" value={selectionActe.type_acte} onChange={handleSelectionActeChange} style={{ width: '100%', padding: '10px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }}>
                      {Object.entries(LABELS_TYPE).map(([key, label]) => (
                        <option key={key} value={key}>{label}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>Langue</label>
                    <select name="langue" value={selectionActe.langue} onChange={handleSelectionActeChange} style={{ width: '100%', padding: '10px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13 }}>
                      {OPTIONS_LANGUE.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
                    </select>
                  </div>
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12, background: '#FFF', padding: 12, borderRadius: 8, border: '1px solid #E5E7EB' }}>
                  <div>
                    <div style={{ fontSize: 11, color: '#6B7280', marginBottom: 4 }}>Prix unitaire</div>
                    <div style={{ fontSize: 14, fontWeight: 600, color: '#111827' }}>
                      {new Intl.NumberFormat('fr-FR').format(prixActeApercu)} Ar
                    </div>
                  </div>
                  <div>
                    <div style={{ fontSize: 11, color: '#6B7280', marginBottom: 4 }}>Quantité</div>
                    <input
                      type="number"
                      name="quantite"
                      min="1"
                      value={selectionActe.quantite}
                      onChange={handleSelectionActeChange}
                      style={{ width: '100%', padding: '8px', borderRadius: 6, border: '1px solid #E5E7EB', fontSize: 13, textAlign: 'center' }}
                    />
                  </div>
                  <div>
                    <div style={{ fontSize: 11, color: '#6B7280', marginBottom: 4 }}>Sous-total</div>
                    <div style={{ fontSize: 14, fontWeight: 700, color: '#4F46E5' }}>
                      {new Intl.NumberFormat('fr-FR').format(sousTotalActe)} Ar
                    </div>
                  </div>
                </div>
              </div>

              {/* SECTION 2 : TYPE DE DOCUMENT */}
              {supplementsDisponibles.length > 0 && (
                <div style={{ marginBottom: 20, paddingTop: 16, borderTop: '2px dashed #D1D5DB' }}>
                  <div style={{ fontSize: 13, fontWeight: 700, color: '#059669', marginBottom: 12, display: 'flex', alignItems: 'center', gap: 6 }}>
                    <FileText size={16} /> TYPE DE DOCUMENT
                  </div>

                  <div style={{ marginBottom: 12 }}>
                    <label style={{ fontSize: 12, fontWeight: 500, display: 'block', marginBottom: 4 }}>
                      Document à demander *
                    </label>
                    <select
                      name="supplement_id"
                      value={selectionActe.supplement_id || ''}
                      onChange={handleSelectionActeChange}
                      style={{ width: '100%', padding: '10px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, background: '#FFF' }}
                    >
                      <option value="">-- Sélectionnez un document --</option>
                      {supplementsDisponibles.map(supp => {
                        const prixSupp = parseFloat(supp[`prix_${form.service}_${selectionActe.langue}`]) || 0;
                        return (
                          <option key={supp.id} value={supp.id}>
                            {supp.nom} — {new Intl.NumberFormat('fr-FR').format(prixSupp)} Ar
                          </option>
                        );
                      })}
                    </select>
                    {getNomSupplement(selectionActe.type_acte, selectionActe.supplement_id) && (
                      <div style={{ fontSize: 11, color: '#6B7280', marginTop: 4, fontStyle: 'italic' }}>
                        {supplementsDisponibles.find(s => s.id === selectionActe.supplement_id)?.description}
                      </div>
                    )}
                  </div>

                  {selectionActe.supplement_id && (
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12, background: '#FFF', padding: 12, borderRadius: 8, border: '1px solid #E5E7EB' }}>
                      <div>
                        <div style={{ fontSize: 11, color: '#6B7280', marginBottom: 4 }}>Prix unitaire</div>
                        <div style={{ fontSize: 14, fontWeight: 600, color: '#111827' }}>
                          {new Intl.NumberFormat('fr-FR').format(prixSuppApercu)} Ar
                        </div>
                      </div>
                      <div>
                        <div style={{ fontSize: 11, color: '#6B7280', marginBottom: 4 }}>Quantité</div>
                        <input
                          type="number"
                          name="quantite_supplement"
                          min="1"
                          value={selectionActe.quantite_supplement}
                          onChange={handleSelectionActeChange}
                          style={{ width: '100%', padding: '8px', borderRadius: 6, border: '1px solid #E5E7EB', fontSize: 13, textAlign: 'center' }}
                        />
                      </div>
                      <div>
                        <div style={{ fontSize: 11, color: '#6B7280', marginBottom: 4 }}>Sous-total</div>
                        <div style={{ fontSize: 14, fontWeight: 700, color: '#059669' }}>
                          {new Intl.NumberFormat('fr-FR').format(sousTotalSupp)} Ar
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* SECTION 3 : PRIX TOTAL */}
              <div style={{ paddingTop: 16, borderTop: '2px dashed #D1D5DB', marginBottom: 16 }}>
                <div style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  padding: 14,
                  background: 'linear-gradient(135deg, #EEF2FF, #DBEAFE)',
                  borderRadius: 10,
                  border: '1px solid #4F46E5'
                }}>
                  <div style={{ fontSize: 14, fontWeight: 700, color: '#4F46E5' }}>
                    💰 Prix total (Acte + Document)
                  </div>
                  <div style={{ fontSize: 20, fontWeight: 800, color: '#4F46E5' }}>
                    {new Intl.NumberFormat('fr-FR').format(prixTotalApercu)} Ar
                  </div>
                </div>
              </div>

              {/* Champs spécifiques */}
              {champsSpecifiques.length > 0 && (
                <div style={{ marginTop: 16, paddingTop: 16, borderTop: '1px dashed #D1D5DB' }}>
                  <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 12 }}>Renseignements spécifiques :</div>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                    {champsSpecifiques.map(champ => (
                      <div key={champ.name}>
                        <label style={{ fontSize: 12, display: 'block', marginBottom: 4 }}>{champ.label} {champ.required && '*'}</label>
                        {champ.type === 'select' ? (
                          <select name={champ.name} value={detailsActe[champ.name] || ''} onChange={handleDetailsChange} style={{ width: '100%', padding: '8px', borderRadius: 6, border: '1px solid #D1D5DB', fontSize: 13 }}>
                            <option value="">Sélectionner...</option>
                            {champ.options.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
                          </select>
                        ) : (
                          <input type={champ.type} name={champ.name} value={detailsActe[champ.name] || ''} onChange={handleDetailsChange} placeholder={champ.placeholder || ''} style={{ width: '100%', padding: '8px', borderRadius: 6, border: '1px solid #D1D5DB', fontSize: 13 }} />
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}

              <button type="button" onClick={ajouterActe} style={{ marginTop: 16, padding: '10px 16px', borderRadius: 8, border: 'none', background: '#4F46E5', color: '#FFF', fontWeight: 600, cursor: 'pointer', fontSize: 13, display: 'flex', alignItems: 'center', gap: 6 }}>
                <Plus size={16} /> Ajouter cet acte
              </button>
            </div>

            {/* PANIER */}
            {actesAjoutes.length > 0 ? (
              <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                {actesAjoutes.map((item, index) => {
                  const Icone = ICONES_TYPE[item.type_acte] || FileText;
                  const prixActe = getPrixActe(item.type_acte, item.langue, form.service);
                  const prixSupp = getPrixSupplement(item.type_acte, item.supplement_id, item.langue, form.service);
                  const nomLangue = OPTIONS_LANGUE.find(l => l.value === item.langue)?.label;
                  const nomSupp = getNomSupplement(item.type_acte, item.supplement_id);
                  const sousTotalItem = (prixActe * item.quantite) + (item.supplement_id ? prixSupp * item.quantite_supplement : 0);

                  const detailsKeys = Object.keys(item.details || {});
                  const detailsAffiches = detailsKeys
                    .filter(key => item.details[key]?.trim() && key !== 'type_acte')
                    .map(key => {
                      const champ = CHAMPS_SPECIFIQUES[item.type_acte]?.find(c => c.name === key);
                      return champ ? `${champ.label}: ${item.details[key]}` : null;
                    })
                    .filter(Boolean);

                  return (
                    <div key={index} style={{ padding: '14px 16px', borderRadius: 8, border: '1px solid #E5E7EB', background: '#FFFFFF' }}>
                      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12, flex: 1, minWidth: 0 }}>
                          <div style={{ width: 40, height: 40, borderRadius: 8, background: '#EEF2FF', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#4F46E5', flexShrink: 0 }}>
                            <Icone size={20} />
                          </div>
                          <div style={{ flex: 1, minWidth: 0 }}>
                            <div style={{ fontSize: 14, fontWeight: 600, color: '#111827', marginBottom: 6 }}>
                              {LABELS_TYPE[item.type_acte]}
                              <span style={{ fontSize: 12, color: '#6B7280', fontWeight: 400 }}> ({nomLangue})</span>
                            </div>

                            <div style={{ fontSize: 12, color: '#374151', lineHeight: 1.7 }}>
                              <div style={{ display: 'flex', justifyContent: 'space-between', maxWidth: 400 }}>
                                <span>📄 {LABELS_TYPE[item.type_acte]}</span>
                                <span>{new Intl.NumberFormat('fr-FR').format(prixActe)} Ar × {item.quantite} = <strong>{new Intl.NumberFormat('fr-FR').format(prixActe * item.quantite)} Ar</strong></span>
                              </div>
                              {item.supplement_id && nomSupp && (
                                <div style={{ display: 'flex', justifyContent: 'space-between', maxWidth: 400, color: '#059669' }}>
                                  <span>📋 {nomSupp}</span>
                                  <span>{new Intl.NumberFormat('fr-FR').format(prixSupp)} Ar × {item.quantite_supplement} = <strong>{new Intl.NumberFormat('fr-FR').format(prixSupp * item.quantite_supplement)} Ar</strong></span>
                                </div>
                              )}
                            </div>
                          </div>
                        </div>

                        <div style={{ display: 'flex', alignItems: 'center', gap: 12, flexShrink: 0 }}>
                          <div style={{ textAlign: 'center' }}>
                            <div style={{ fontSize: 10, color: '#6B7280', marginBottom: 2 }}>Qté acte</div>
                            <input
                              type="number"
                              min="1"
                              value={item.quantite}
                              onChange={(e) => modifierQuantite(index, e.target.value)}
                              style={{ width: 55, padding: '4px 6px', borderRadius: 6, border: '1px solid #D1D5DB', fontSize: 12, textAlign: 'center' }}
                            />
                          </div>

                          {item.supplement_id && (
                            <div style={{ textAlign: 'center' }}>
                              <div style={{ fontSize: 10, color: '#6B7280', marginBottom: 2 }}>Qté doc</div>
                              <input
                                type="number"
                                min="1"
                                value={item.quantite_supplement}
                                onChange={(e) => modifierQuantiteSupp(index, e.target.value)}
                                style={{ width: 55, padding: '4px 6px', borderRadius: 6, border: '1px solid #D1D5DB', fontSize: 12, textAlign: 'center' }}
                              />
                            </div>
                          )}

                          <div style={{ textAlign: 'right', minWidth: 90 }}>
                            <div style={{ fontSize: 11, color: '#6B7280' }}>Total</div>
                            <div style={{ fontSize: 15, fontWeight: 700, color: '#4F46E5' }}>
                              {new Intl.NumberFormat('fr-FR').format(sousTotalItem)} Ar
                            </div>
                          </div>

                          <button type="button" onClick={() => retirerActe(index)} style={{ border: 'none', background: 'transparent', color: '#EF4444', cursor: 'pointer', padding: 4 }}>
                            <Trash2 size={18} />
                          </button>
                        </div>
                      </div>

                      {detailsAffiches.length > 0 && (
                        <div style={{
                          marginTop: 10,
                          paddingTop: 10,
                          borderTop: '1px dashed #E5E7EB',
                          display: 'flex',
                          flexWrap: 'wrap',
                          gap: '4px 12px',
                          fontSize: 11,
                          color: '#4B5563'
                        }}>
                          {detailsAffiches.map((detailTxt, i) => (
                            <span key={i} style={{ background: '#F3F4F6', padding: '2px 8px', borderRadius: 4 }}>
                              {detailTxt}
                            </span>
                          ))}
                        </div>
                      )}
                    </div>
                  );
                })}

                <div style={{ marginTop: 12, padding: 18, borderRadius: 10, background: 'linear-gradient(135deg, #F3F4F6, #E5E7EB)', border: '1px solid #D1D5DB' }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
                    <span style={{ fontSize: 15, fontWeight: 700, color: '#111827' }}>
                      💰 PRIX TOTAL ({totalActes} acte{totalActes > 1 ? 's' : ''})
                    </span>
                    <span style={{ fontSize: 22, fontWeight: 800, color: '#4F46E5' }}>
                      {new Intl.NumberFormat('fr-FR').format(prixTotal)} Ar
                    </span>
                  </div>

                  <div style={{ fontSize: 12, color: '#374151', borderTop: '1px solid #D1D5DB', paddingTop: 10 }}>
                    {actesAjoutes.map((item, idx) => {
                      const prixActe = getPrixActe(item.type_acte, item.langue, form.service);
                      const prixSupp = getPrixSupplement(item.type_acte, item.supplement_id, item.langue, form.service);
                      const nomSupp = getNomSupplement(item.type_acte, item.supplement_id);

                      return (
                        <div key={idx} style={{ marginBottom: 6 }}>
                          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                            <span>📄 {LABELS_TYPE[item.type_acte]} × {item.quantite}</span>
                            <span style={{ fontWeight: 600 }}>{new Intl.NumberFormat('fr-FR').format(prixActe * item.quantite)} Ar</span>
                          </div>
                          {item.supplement_id && nomSupp && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', paddingLeft: 20, color: '#059669' }}>
                              <span>📋 {nomSupp} × {item.quantite_supplement}</span>
                              <span style={{ fontWeight: 600 }}>{new Intl.NumberFormat('fr-FR').format(prixSupp * item.quantite_supplement)} Ar</span>
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                </div>
              </div>
            ) : (
              <div style={{ padding: 24, textAlign: 'center', color: '#9CA3AF', border: '2px dashed #E5E7EB', borderRadius: 8 }}>
                Aucun acte ajouté.
              </div>
            )}
          </div>

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 12, paddingTop: 20, borderTop: '1px solid #E5E7EB' }}>
            <button type="button" onClick={() => navigate('/tableau-de-bord')} disabled={soumission} style={{ padding: '10px 20px', borderRadius: 8, border: '1px solid #D1D5DB', background: '#FFF', color: '#374151', fontSize: 13, cursor: 'pointer' }}>
              Annuler
            </button>
            <button type="submit" disabled={soumission || actesAjoutes.length === 0} style={{ padding: '10px 24px', borderRadius: 8, border: 'none', background: soumission || actesAjoutes.length === 0 ? '#9CA3AF' : '#4F46E5', color: '#FFF', fontSize: 13, fontWeight: 600, cursor: soumission || actesAjoutes.length === 0 ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', gap: 8 }}>
              <Send size={16} /> {soumission ? 'Envoi en cours...' : 'Envoyer la demande'}
            </button>
          </div>

        </form>
      </div>

      {/* ===== MODAL ESTIMATION ===== */}
      {modalEstimation && demandeEstimee && (
        <ModalEstimation
          demande={demandeEstimee}
          onClose={() => {
            setModalEstimation(false);
            navigate('/tableau-de-bord');
          }}
          onStatutChange={(demandeMiseAJour) => {
            // ✅ Callback quand le statut change
            console.log('🎉 Statut mis à jour:', demandeMiseAJour.statut);

            // Afficher un message avant redirection
            const message = demandeMiseAJour.statut === 'acceptée'
              ? '✅ Votre demande a été acceptée !'
              : '❌ Votre demande a été refusée.';
             // Optionnel : afficher une alerte
            // alert(message);
          }}
        />
      )}

    </div>
  );
}