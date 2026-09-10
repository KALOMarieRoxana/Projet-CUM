import { useState, useEffect, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axiosConfig';
import {
  FileText, Clock, CheckCircle, LogOut, Plus,
  User, ChevronDown, Home, ArrowLeft, Eye,
  Search, Filter, X, Calendar, UserCircle,
  Heart, HeartPulse, Scale, File, Download
} from 'lucide-react';
import logo from '../assets/image/logo.png';

const LABELS_TYPE = {
  naissance: 'Acte de naissance',
  mariage: 'Acte de mariage',
  deces: 'Acte de décès',
  divorces: 'Acte de divorce',
};

const ICONES_TYPE = {
  naissance: User,
  mariage: Heart,
  deces: HeartPulse,
  divorce: Scale,
};

const STATUTS = {
  en_attente: { label: 'En attente', color: '#F59E0B', bg: '#FEF3C7' },
  acceptee: { label: 'Acceptée', color: '#10B981', bg: '#D1FAE5' },
  refusee: { label: 'Refusée', color: '#EF4444', bg: '#FEE2E2' },
  en_cours: { label: 'En cours', color: '#3B82F6', bg: '#DBEAFE' },
};
// ✅ Déclarer CHAMPS_SPECIFIQUES ici, en dehors du composant
const CHAMPS_SPECIFIQUES = {
  naissance: [
    { name: 'personne_sexe', label: 'Sexe' },
    { name: 'pere_nom', label: 'Nom du père' },
    { name: 'pere_prenom', label: 'Prénom du père' },
    { name: 'mere_nom', label: 'Nom de la mère' },
    { name: 'mere_prenom', label: 'Prénom de la mère' },
  ],
  mariage: [
     { name: 'epoux_nom', label: "Nom de l'époux" },
    { name: 'epoux_prenom', label: "Prénom de l'époux" },
    { name: 'epoux_lieu_naissance', label: 'Lieu naissance époux' },
    { name: 'epoux_date_naissance', label: 'Date naissance époux' },
    { name: 'epouse_nom', label: "Nom de l'épouse" },
    { name: 'epouse_prenom', label: "Prénom de l'épouse" },
    { name: 'epouse_lieu_naissance', label: 'Lieu naissance épouse' },
    { name: 'epouse_date_naissance', label: 'Date naissance épouse' },
    { name: 'date_mariage', label: 'Date du mariage' },
    { name: 'lieu_mariage', label: 'Lieu du mariage' },
  ],
  deces: [
    { name: 'defunt_nom', label: 'Nom du défunt' },
    { name: 'defunt_prenom', label: 'Prénom du défunt' },
    { name: 'defunt_lieu_naissance', label: 'Lieu naissance défunt' },
    { name: 'defunt_date_naissance', label: 'Date naissance défunt' },
    { name: 'date_deces', label: 'Date du décès' },
    { name: 'lieu_deces', label: 'Lieu du décès' },
    { name: 'cause_deces', label: 'Cause du décès' },
  ],
  divorce: [
    { name: 'conjoint_nom', label: 'Nom du conjoint' },
    { name: 'conjoint_prenom', label: 'Prénom du conjoint' },
    { name: 'conjointe_nom', label: 'Nom de la conjointe' },
    { name: 'conjointe_prenom', label: 'Prénom de la conjointe' },
    { name: 'date_mariage', label: 'Date du mariage' },
    { name: 'date_demande_divorce', label: 'Date demande divorce' },
    { name: 'motif', label: 'Motif du divorce' },
  ],
};

export default function MesDemandes() {
  const { utilisateur, deconnecter } = useAuth();
  const navigate = useNavigate();
  const [demandes, setDemandes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [filtreStatut, setFiltreStatut] = useState('tous');
  const [recherche, setRecherche] = useState('');
  const [menuProfilOuvert, setMenuProfilOuvert] = useState(false);
  const [demandeSelectionnee, setDemandeSelectionnee] = useState(null);
  const [modalOuverte, setModalOuverte] = useState(false);
  const menuRef = useRef(null);

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
    chargerDemandes();
  }, [utilisateur, navigate]);

  const chargerDemandes = async () => {
    try {
      setChargement(true);
      const res = await api.get('/demandes/mes-demandes');
      setDemandes(res.data.demandes || []);
    } catch (err) {
      console.error('Erreur chargement demandes:', err);
      setErreur('Impossible de charger vos demandes. Veuillez réessayer.');
    } finally {
      setChargement(false);
    }
  };

  const gererDeconnexion = () => {
    deconnecter();
    navigate('/connexion');
  };

  const demandesFiltrees = demandes.filter(demande => {
    const statutOk = filtreStatut === 'tous' || demande.statut === filtreStatut;
    const rechercheOk = recherche === '' || 
      demande.reference?.toLowerCase().includes(recherche.toLowerCase()) ||
      demande.demandeur_nom?.toLowerCase().includes(recherche.toLowerCase()) ||
      demande.demandeur_prenom?.toLowerCase().includes(recherche.toLowerCase()) ||
      demande.personne_nom?.toLowerCase().includes(recherche.toLowerCase());
    return statutOk && rechercheOk;
  });

  const stats = {
    total: demandes.length,
    en_attente: demandes.filter(d => d.statut === 'en_attente').length,
    acceptees: demandes.filter(d => d.statut === 'acceptee').length,
    refusees: demandes.filter(d => d.statut === 'refusee').length,
  };

  const ouvrirModal = (demande) => {
    setDemandeSelectionnee(demande);
    setModalOuverte(true);
  };

  const fermerModal = () => {
    setModalOuverte(false);
    setDemandeSelectionnee(null);
  };

  // Calcul du prix total pour une demande
  const calculerTotalDemande = (demande) => {
    const actes = demande.demande_actes || demande.demandeActes || [];
    if (actes.length === 0) return 0;
    return actes.reduce((sum, item) => {
      return sum + (parseFloat(item.prix_unitaire || 0) * (item.quantite || 1));
    }, 0);
  };

  if (chargement) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#F3F4F6' }}>
        <div style={{ fontSize: 16, color: '#6B7280' }}>Chargement de vos demandes...</div>
      </div>
    );
  }

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
            { icon: Home, label: 'Tableau de bord', actif: true, lien: '/tableau-de-bord' },
            { icon: FileText, label: 'Mes demandes', actif: false, lien: '/tableau-de-bord' },
            { icon: Plus, label: 'Nouvelle demande', actif: false, lien: '/nouvelle-demande' },
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
            <h1 style={{ margin: 0, fontSize: 24, fontWeight: 700, color: '#111827' }}>Mes demandes</h1>
          </div>
          <Link to="/nouvelle-demande">
            <button style={{ padding: '10px 20px', borderRadius: 8, border: 'none', background: '#4F46E5', color: '#FFF', fontSize: 13, fontWeight: 600, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 8 }}>
              <Plus size={16} /> Nouvelle demande
            </button>
          </Link>
        </div>

        {erreur && (
          <div style={{ padding: '12px 16px', borderRadius: 10, background: '#FEE2E2', color: '#991B1B', marginBottom: 24, fontSize: 13 }}>
            {erreur}
          </div>
        )}

        {/* ===== STATISTIQUES ===== */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: 16, marginBottom: 24 }}>
          {[
            { label: 'Total demandes', value: stats.total, icon: FileText, color: '#6366F1', bg: '#EEF2FF' },
            { label: 'En attente', value: stats.en_attente, icon: Clock, color: '#F59E0B', bg: '#FEF3C7' },
            { label: 'Acceptées', value: stats.acceptees, icon: CheckCircle, color: '#10B981', bg: '#D1FAE5' },
            { label: 'Refusées', value: stats.refusees, icon: X, color: '#EF4444', bg: '#FEE2E2' },
          ].map((stat, index) => (
            <div key={index} style={{ background: '#FFFFFF', padding: '16px 20px', borderRadius: 12, border: '1px solid #E5E7EB' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <div style={{ padding: 8, borderRadius: 8, background: stat.bg, color: stat.color }}>
                  <stat.icon size={18} />
                </div>
                <div>
                  <div style={{ fontSize: 22, fontWeight: 700, color: '#111827' }}>{stat.value}</div>
                  <div style={{ fontSize: 12, color: '#6B7280' }}>{stat.label}</div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* ===== FILTRES ET RECHERCHE ===== */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24, flexWrap: 'wrap', gap: 12 }}>
          <div style={{ display: 'flex', gap: 8 }}>
            {['tous', 'en_attente', 'en_cours', 'acceptee', 'refusee'].map(statut => (
              <button
                key={statut}
                onClick={() => setFiltreStatut(statut)}
                style={{
                  padding: '6px 14px',
                  borderRadius: 20,
                  border: filtreStatut === statut ? '2px solid #4F46E5' : '1px solid #E5E7EB',
                  background: filtreStatut === statut ? '#EEF2FF' : '#FFF',
                  color: filtreStatut === statut ? '#4F46E5' : '#6B7280',
                  fontSize: 12,
                  fontWeight: filtreStatut === statut ? 600 : 400,
                  cursor: 'pointer'
                }}
              >
                {statut === 'tous' ? 'Tous' : STATUTS[statut]?.label || statut}
              </button>
            ))}
          </div>

          <div style={{ display: 'flex', alignItems: 'center', gap: 8, position: 'relative' }}>
            <Search size={16} style={{ position: 'absolute', left: 12, color: '#9CA3AF' }} />
            <input
              type="text"
              placeholder="Rechercher par référence, nom..."
              value={recherche}
              onChange={(e) => setRecherche(e.target.value)}
              style={{ padding: '8px 12px 8px 36px', borderRadius: 8, border: '1px solid #E5E7EB', fontSize: 13, width: 280 }}
            />
          </div>
        </div>

        {/* ===== LISTE DES DEMANDES ===== */}
        {demandesFiltrees.length === 0 ? (
          <div style={{ textAlign: 'center', padding: 40, background: '#FFFFFF', borderRadius: 12, border: '1px solid #E5E7EB' }}>
            <FileText size={48} style={{ color: '#D1D5DB', marginBottom: 16 }} />
            <p style={{ color: '#6B7280', fontSize: 16 }}>Aucune demande trouvée</p>
            <Link to="/nouvelle-demande">
              <button style={{ marginTop: 12, padding: '10px 20px', borderRadius: 8, border: 'none', background: '#4F46E5', color: '#FFF', fontSize: 13, fontWeight: 600, cursor: 'pointer' }}>
                Créer ma première demande
              </button>
            </Link>
          </div>
        ) : (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
            {demandesFiltrees.map((demande) => {
              const statutInfo = STATUTS[demande.statut] || { label: demande.statut, color: '#6B7280', bg: '#F3F4F6' };
              const totalDemande = calculerTotalDemande(demande);
              const actes = demande.demande_actes || demande.demandeActes || [];
              const nbActes = actes.reduce((sum, d) => sum + (d.quantite || 1), 0);
              
              return (
                <div key={demande.id_demande || demande.id} style={{ background: '#FFFFFF', borderRadius: 12, border: '1px solid #E5E7EB', padding: 16 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                    <div style={{ flex: 1 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 8 }}>
                        <span style={{ fontSize: 14, fontWeight: 700, color: '#111827' }}>
                          {demande.reference || `DEM-${demande.id_demande}`}
                        </span>
                        <span style={{ 
                          fontSize: 11, 
                          padding: '2px 10px', 
                          borderRadius: 12, 
                          background: statutInfo.bg, 
                          color: statutInfo.color,
                          fontWeight: 500
                        }}>
                          {statutInfo.label}
                        </span>
                        <span style={{ fontSize: 11, color: '#9CA3AF' }}>
                          {new Date(demande.created_at).toLocaleDateString('fr-FR')}
                        </span>
                      </div>

                      <div style={{ display: 'flex', gap: 24, fontSize: 13, color: '#6B7280', flexWrap: 'wrap' }}>
                        <div>
                          <span style={{ fontWeight: 500 }}>Demandeur :</span> {demande.demandeur_prenom} {demande.demandeur_nom}
                        </div>
                        <div>
                          <span style={{ fontWeight: 500 }}>Concerné :</span> {demande.personne_prenom} {demande.personne_nom}
                        </div>
                        <div>
                          <span style={{ fontWeight: 500 }}>Actes :</span> {nbActes}
                        </div>
                        <div>
                          <span style={{ fontWeight: 500 }}>Total :</span> 
                          <span style={{ color: '#4F46E5', fontWeight: 700 }}>
                            {new Intl.NumberFormat('fr-FR').format(totalDemande)} Ar
                          </span>
                        </div>
                      </div>
                    </div>

                    <button
                      onClick={() => ouvrirModal(demande)}
                      style={{ padding: '6px 12px', borderRadius: 6, border: '1px solid #E5E7EB', background: '#F9FAFB', color: '#4F46E5', fontSize: 12, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 4 }}
                    >
                      <Eye size={14} /> Détails
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* ===== MODAL DÉTAILS ===== */}
      {modalOuverte && demandeSelectionnee && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000 }}>
          <div style={{ background: '#FFFFFF', borderRadius: 16, padding: 32, maxWidth: 800, width: '100%', maxHeight: '80vh', overflow: 'auto' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
              <h2 style={{ margin: 0, fontSize: 20, fontWeight: 700, color: '#111827' }}>
                Détails de la demande
              </h2>
              <button onClick={fermerModal} style={{ border: 'none', background: 'transparent', color: '#6B7280', cursor: 'pointer' }}>
                <X size={24} />
              </button>
            </div>

            <div style={{ marginBottom: 24, padding: 16, background: '#F9FAFB', borderRadius: 8 }}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, fontSize: 13 }}>
                <div><span style={{ fontWeight: 600 }}>Référence :</span> {demandeSelectionnee.reference}</div>
                <div><span style={{ fontWeight: 600 }}>Statut :</span> {STATUTS[demandeSelectionnee.statut]?.label || demandeSelectionnee.statut}</div>
                <div><span style={{ fontWeight: 600 }}>Date :</span> {new Date(demandeSelectionnee.created_at).toLocaleDateString('fr-FR')}</div>
                <div><span style={{ fontWeight: 600 }}>Service :</span> {demandeSelectionnee.service === 'express' ? '⚡ Express' : 'Standard'}</div>
              </div>
            </div>

            <div style={{ marginBottom: 16 }}>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: '#111827' }}>Informations demandeur</h3>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, fontSize: 13, padding: 12, background: '#F9FAFB', borderRadius: 8 }}>
                <div><span style={{ fontWeight: 600 }}>Nom :</span> {demandeSelectionnee.demandeur_nom}</div>
                <div><span style={{ fontWeight: 600 }}>Prénom :</span> {demandeSelectionnee.demandeur_prenom}</div>
                <div><span style={{ fontWeight: 600 }}>Adresse :</span> {demandeSelectionnee.demandeur_adresse}</div>
                <div><span style={{ fontWeight: 600 }}>Contact :</span> {demandeSelectionnee.demandeur_contact}</div>
                <div><span style={{ fontWeight: 600 }}>Relation :</span> {demandeSelectionnee.demandeur_relation}</div>
              </div>
            </div>

            <div style={{ marginBottom: 16 }}>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: '#111827' }}>Personne concernée</h3>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, fontSize: 13, padding: 12, background: '#F9FAFB', borderRadius: 8 }}>
                <div><span style={{ fontWeight: 600 }}>Nom :</span> {demandeSelectionnee.personne_nom}</div>
                <div><span style={{ fontWeight: 600 }}>Prénom :</span> {demandeSelectionnee.personne_prenom}</div>
                <div><span style={{ fontWeight: 600 }}>Lieu naissance :</span> {demandeSelectionnee.personne_lieu_naissance}</div>
                <div><span style={{ fontWeight: 600 }}>Date naissance :</span> {demandeSelectionnee.personne_date_naissance}</div>
                {demandeSelectionnee.personne_numero_acte && (
                  <div><span style={{ fontWeight: 600 }}>N° acte :</span> {demandeSelectionnee.personne_numero_acte}</div>
                )}
              </div>
            </div>

            <div>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: '#111827' }}>Liste des actes demandés</h3>
              {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes || []).length > 0 ? (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes).map((item, index) => {
                    // Récupérer le slug (chaîne) du type d'acte
                    let typeActe = typeof item.type_acte === 'string' ? item.type_acte : null;
                    if (!typeActe) {
                      typeActe = item.typeActe?.type_acte || null;
                    }
                    // Si toujours null, fallback sur la clé de l'objet si présent
                    if (!typeActe && item.typeActe && typeof item.typeActe === 'object') {
                      typeActe = item.typeActe.type_acte;
                    }
                    
                    const Icone = ICONES_TYPE[typeActe] || FileText;
                    const nomActe = LABELS_TYPE[typeActe] || item.typeActe?.nom || typeActe;
                    const prixUnitaire = parseFloat(item.prix_unitaire || 0);
                    const quantite = item.quantite || 1;
                    
                     // Récupérer les détails spécifiques
                    const details = item.details || {};
                    const detailsAffiches = Object.keys(details)
                      .filter(key => details[key]?.trim())
                      .map(key => {
                        const champ = CHAMPS_SPECIFIQUES[typeActe]?.find(c => c.name === key);
                    return champ ? { label: champ.label, value: details[key] } : null;
                    })
                    .filter(Boolean);
                    return (
                      <div key={index} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 14px', background: '#FFFFFF', borderRadius: 6, border: '1px solid #E5E7EB' }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <div style={{ width: 32, height: 32, borderRadius: 6, background: '#EEF2FF', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#4F46E5' }}>
                              <Icone size={16} />
                            </div>
                            <div>
                              <div style={{ fontSize: 13, fontWeight: 600, color: '#111827' }}>{nomActe}</div>
                              <div style={{ fontSize: 11, color: '#6B7280' }}>Quantité: {quantite}</div>
                            </div>
                          </div>
                          <div style={{ fontSize: 13, fontWeight: 600, color: '#4F46E5' }}>
                            {new Intl.NumberFormat('fr-FR').format(prixUnitaire * quantite)} Ar
                          </div>
                        </div>
                        {/* Afficher les détails spécifiques */}
                        {detailsAffiches.length > 0 && (
                          <div style={{ 
                            marginTop: 6,
                            paddingTop: 6,
                            borderTop: '1px dashed #E5E7EB',
                            display: 'flex',
                            flexWrap: 'wrap',
                            gap: '4px 12px',
                            fontSize: 11,
                            color: '#6B7280'
                          }}>
                            {detailsAffiches.map((detail, i) => (
                              <span key={i}>
                                <strong>{detail.label}:</strong> {detail.value}
                              </span>
                            ))}
                          </div>
                        )}
                      </div>
                    );
                  })}
                  <div style={{ marginTop: 8, padding: '12px 14px', background: '#F3F4F6', borderRadius: 6, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <span style={{ fontSize: 14, fontWeight: 600 }}>Total</span>
                    <span style={{ fontSize: 16, fontWeight: 700, color: '#4F46E5' }}>
                      {new Intl.NumberFormat('fr-FR').format(calculerTotalDemande(demandeSelectionnee))} Ar
                    </span>
                  </div>
                </div>
              ) : (
                <div style={{ padding: 16, textAlign: 'center', color: '#9CA3AF', background: '#F9FAFB', borderRadius: 8 }}>
                  Aucun acte associé à cette demande
                </div>
              )}
            </div>

            <div style={{ marginTop: 24, display: 'flex', justifyContent: 'flex-end' }}>
              <button onClick={fermerModal} style={{ padding: '8px 20px', borderRadius: 8, border: '1px solid #D1D5DB', background: '#FFF', color: '#374151', fontSize: 13, cursor: 'pointer' }}>
                Fermer
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}