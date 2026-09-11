import { useState, useEffect, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../theme/ThemeContext';
import ThemeSwitcher from '../components/ThemeSwitcher';
import Statistiques from '../pages/Statistiques';
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
  divorces: Scale,
};

const STATUTS = {
  en_attente: { label: 'En attente', color: '#F59E0B', bg: '#FEF3C7' },
  acceptée: { label: 'Acceptée', color: '#10B981', bg: '#D1FAE5' },
  refusée: { label: 'Refusée', color: '#EF4444', bg: '#FEE2E2' },
  en_cours: { label: 'En cours', color: '#3B82F6', bg: '#DBEAFE' },
};

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
  divorces: [
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
  const { colors } = useTheme();
  const navigate = useNavigate();
  const [demandes, setDemandes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState('');
  const [filtreStatut, setFiltreStatut] = useState('tous');
  const [recherche, setRecherche] = useState('');
  const [menuProfilOuvert, setMenuProfilOuvert] = useState(false);
  const [demandeSelectionnee, setDemandeSelectionnee] = useState(null);
  const [modalOuverte, setModalOuverte] = useState(false);
  const [ongletActif, setOngletActif] = useState('demandes');
  const [periodeStats, setPeriodeStats] = useState('12mois');
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
    acceptees: demandes.filter(d => d.statut === 'acceptée').length,
    refusees: demandes.filter(d => d.statut === 'refusée').length,
  };

  const ouvrirModal = (demande) => {
    setDemandeSelectionnee(demande);
    setModalOuverte(true);
  };

  const fermerModal = () => {
    setModalOuverte(false);
    setDemandeSelectionnee(null);
  };

  const calculerTotalDemande = (demande) => {
    const actes = demande.demande_actes || demande.demandeActes || [];
    if (actes.length === 0) return 0;
    return actes.reduce((sum, item) => {
      return sum + (parseFloat(item.prix_unitaire || 0) * (item.quantite || 1));
    }, 0);
  };

  if (chargement) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: colors.bg }}>
        <div style={{ fontSize: 16, color: colors.textSecondary }}>Chargement de vos demandes...</div>
      </div>
    );
  }

  return (
    <div style={{ display: 'flex', minHeight: '100vh', background: colors.bg, color: colors.text, fontFamily: 'Inter, sans-serif' }}>

      {/* ===== SIDEBAR ===== */}
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
            { icon: FileText, label: 'Mes demandes', actif: true, lien: '/mes-demandes' },
            { icon: Plus, label: 'Nouvelle demande', actif: false, lien: '/nouvelle-demande' },
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

      {/* ===== CONTENU PRINCIPAL ===== */}
      <div style={{ marginLeft: 240, flex: 1, padding: '32px 32px' }}>

        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 32 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <Link to="/tableau-de-bord" style={{ color: colors.primary, textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
              <ArrowLeft size={18} />
              <span style={{ fontSize: 13 }}>Retour</span>
            </Link>
            <h1 style={{ margin: 0, fontSize: 24, fontWeight: 700, color: colors.text }}>Mes demandes</h1>
            <div style={{ display: 'flex', gap: 4, marginLeft: 24 }}>
              <button
                onClick={() => setOngletActif('demandes')}
                style={{
                  padding: '6px 16px',
                  borderRadius: 8,
                  border: 'none',
                  background: ongletActif === 'demandes' ? colors.primaryLight : 'transparent',
                  color: ongletActif === 'demandes' ? colors.primary : colors.textSecondary,
                  fontSize: 13,
                  fontWeight: ongletActif === 'demandes' ? 600 : 400,
                  cursor: 'pointer'
                }}
              >
                Mes demandes
              </button>
              <button
                onClick={() => setOngletActif('statistiques')}
                style={{
                  padding: '6px 16px',
                  borderRadius: 8,
                  border: 'none',
                  background: ongletActif === 'statistiques' ? colors.primaryLight : 'transparent',
                  color: ongletActif === 'statistiques' ? colors.primary : colors.textSecondary,
                  fontSize: 13,
                  fontWeight: ongletActif === 'statistiques' ? 600 : 400,
                  cursor: 'pointer'
                }}
              >
                Statistiques
              </button>
            </div>
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

        {erreur && (
          <div style={{ padding: '12px 16px', borderRadius: 10, background: '#FEE2E2', color: '#991B1B', marginBottom: 24, fontSize: 13 }}>
            {erreur}
          </div>
        )}

        {/* ===== STATISTIQUES HAUT ===== */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: 16, marginBottom: 24 }}>
          {[
            { label: 'Total demandes', value: stats.total, icon: FileText, color: colors.primary, bg: colors.primaryLight },
            { label: 'En attente', value: stats.en_attente, icon: Clock, color: '#F59E0B', bg: '#FEF3C7' },
            { label: 'Acceptées', value: stats.acceptees, icon: CheckCircle, color: '#10B981', bg: '#D1FAE5' },
            { label: 'Refusées', value: stats.refusees, icon: X, color: '#EF4444', bg: '#FEE2E2' },
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

        {/* ===== ONGLET DEMANDES ===== */}
        {ongletActif === 'demandes' && (
          <>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24, flexWrap: 'wrap', gap: 12 }}>
              <div style={{ display: 'flex', gap: 8 }}>
                {['tous', 'en_attente', 'en_cours', 'acceptée', 'refusée'].map(statut => (
                  <button
                    key={statut}
                    onClick={() => setFiltreStatut(statut)}
                    style={{
                      padding: '6px 14px',
                      borderRadius: 20,
                      border: filtreStatut === statut ? `2px solid ${colors.primary}` : `1px solid ${colors.cardBorder}`,
                      background: filtreStatut === statut ? colors.primaryLight : colors.card,
                      color: filtreStatut === statut ? colors.primary : colors.textSecondary,
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
                <Search size={16} style={{ position: 'absolute', left: 12, color: colors.textMuted }} />
                <input
                  type="text"
                  placeholder="Rechercher par référence, nom..."
                  value={recherche}
                  onChange={(e) => setRecherche(e.target.value)}
                  style={{ padding: '8px 12px 8px 36px', borderRadius: 8, border: `1px solid ${colors.inputBorder}`, fontSize: 13, width: 280, background: colors.input, color: colors.text }}
                />
              </div>
            </div>

            {demandesFiltrees.length === 0 ? (
              <div style={{ textAlign: 'center', padding: 40, background: colors.card, borderRadius: 12, border: `1px solid ${colors.cardBorder}` }}>
                <FileText size={48} style={{ color: colors.textMuted, marginBottom: 16 }} />
                <p style={{ color: colors.textSecondary, fontSize: 16 }}>Aucune demande trouvée</p>
                <Link to="/nouvelle-demande">
                  <button style={{ marginTop: 12, padding: '10px 20px', borderRadius: 8, border: 'none', background: colors.primary, color: '#FFF', fontSize: 13, fontWeight: 600, cursor: 'pointer' }}>
                    Créer ma première demande
                  </button>
                </Link>
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                {demandesFiltrees.map((demande) => {
                  const statutInfo = STATUTS[demande.statut] || { label: demande.statut, color: colors.textSecondary, bg: colors.input };
                  const totalDemande = calculerTotalDemande(demande);
                  const actes = demande.demande_actes || demande.demandeActes || [];
                  const nbActes = actes.reduce((sum, d) => sum + (d.quantite || 1), 0);

                  return (
                    <div key={demande.id_demande || demande.id} style={{ background: colors.card, borderRadius: 12, border: `1px solid ${colors.cardBorder}`, padding: 16 }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <div style={{ flex: 1 }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 8 }}>
                            <span style={{ fontSize: 14, fontWeight: 700, color: colors.text }}>
                              {demande.reference || `DEM-${demande.id_demande}`}
                            </span>
                            <span style={{ fontSize: 11, padding: '2px 10px', borderRadius: 12, background: statutInfo.bg, color: statutInfo.color, fontWeight: 500 }}>
                              {statutInfo.label}
                            </span>
                            <span style={{ fontSize: 11, color: colors.textMuted }}>
                              {new Date(demande.created_at).toLocaleDateString('fr-FR')}
                            </span>
                          </div>

                          <div style={{ display: 'flex', gap: 24, fontSize: 13, color: colors.textSecondary, flexWrap: 'wrap' }}>
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
                              <span style={{ color: colors.primary, fontWeight: 700 }}>
                                {new Intl.NumberFormat('fr-FR').format(totalDemande)} Ar
                              </span>
                            </div>
                          </div>
                        </div>

                        <button
                          onClick={() => ouvrirModal(demande)}
                          style={{ padding: '6px 12px', borderRadius: 6, border: `1px solid ${colors.cardBorder}`, background: colors.input, color: colors.primary, fontSize: 12, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 4 }}
                        >
                          <Eye size={14} /> Détails
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </>
        )}

        {/* ===== ONGLET STATISTIQUES ===== */}
        {ongletActif === 'statistiques' && (
          <Statistiques
            demandes={demandes}
            periodeStats={periodeStats}
            setPeriodeStats={setPeriodeStats}
          />
        )}
      </div>

      {/* ===== MODAL DÉTAILS ===== */}
      {modalOuverte && demandeSelectionnee && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000 }}>
          <div style={{ background: colors.card, borderRadius: 16, padding: 32, maxWidth: 800, width: '100%', maxHeight: '80vh', overflow: 'auto' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
              <h2 style={{ margin: 0, fontSize: 20, fontWeight: 700, color: colors.text }}>
                Détails de la demande
              </h2>
              <button onClick={fermerModal} style={{ border: 'none', background: 'transparent', color: colors.textSecondary, cursor: 'pointer' }}>
                <X size={24} />
              </button>
            </div>

            <div style={{ marginBottom: 24, padding: 16, background: colors.input, borderRadius: 8 }}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, fontSize: 13, color: colors.text }}>
                <div><span style={{ fontWeight: 600 }}>Référence :</span> {demandeSelectionnee.reference}</div>
                <div><span style={{ fontWeight: 600 }}>Statut :</span> {STATUTS[demandeSelectionnee.statut]?.label || demandeSelectionnee.statut}</div>
                <div><span style={{ fontWeight: 600 }}>Date :</span> {new Date(demandeSelectionnee.created_at).toLocaleDateString('fr-FR')}</div>
                <div><span style={{ fontWeight: 600 }}>Service :</span> {demandeSelectionnee.service === 'express' ? '⚡ Express' : 'Standard'}</div>
              </div>
            </div>

            <div style={{ marginBottom: 16 }}>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: colors.text }}>Informations demandeur</h3>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, fontSize: 13, padding: 12, background: colors.input, borderRadius: 8, color: colors.text }}>
                <div><span style={{ fontWeight: 600 }}>Nom :</span> {demandeSelectionnee.demandeur_nom}</div>
                <div><span style={{ fontWeight: 600 }}>Prénom :</span> {demandeSelectionnee.demandeur_prenom}</div>
                <div><span style={{ fontWeight: 600 }}>Adresse :</span> {demandeSelectionnee.demandeur_adresse}</div>
                <div><span style={{ fontWeight: 600 }}>Contact :</span> {demandeSelectionnee.demandeur_contact}</div>
                <div><span style={{ fontWeight: 600 }}>Relation :</span> {demandeSelectionnee.demandeur_relation}</div>
              </div>
            </div>

            <div style={{ marginBottom: 16 }}>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: colors.text }}>Personne concernée</h3>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, fontSize: 13, padding: 12, background: colors.input, borderRadius: 8, color: colors.text }}>
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
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: colors.text }}>Liste des actes demandés</h3>
              {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes || []).length > 0 ? (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes).map((item, index) => {
                    let typeActe = typeof item.type_acte === 'string' ? item.type_acte : null;
                    if (!typeActe) typeActe = item.typeActe?.type_acte || null;
                    if (!typeActe && item.typeActe && typeof item.typeActe === 'object') typeActe = item.typeActe.type_acte;

                    const Icone = ICONES_TYPE[typeActe] || FileText;
                    const nomActe = LABELS_TYPE[typeActe] || item.typeActe?.nom || typeActe;
                    const prixUnitaire = parseFloat(item.prix_unitaire || 0);
                    const quantite = item.quantite || 1;

                    const details = item.details || {};
                    const detailsAffiches = Object.keys(details)
                      .filter(key => details[key]?.trim())
                      .map(key => {
                        const champ = CHAMPS_SPECIFIQUES[typeActe]?.find(c => c.name === key);
                        return champ ? { label: champ.label, value: details[key] } : null;
                      })
                      .filter(Boolean);

                    return (
                      <div key={index} style={{ display: 'flex', flexDirection: 'column', padding: '10px 14px', background: colors.card, borderRadius: 6, border: `1px solid ${colors.cardBorder}` }}>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <div style={{ width: 32, height: 32, borderRadius: 6, background: colors.primaryLight, display: 'flex', alignItems: 'center', justifyContent: 'center', color: colors.primary }}>
                              <Icone size={16} />
                            </div>
                            <div>
                              <div style={{ fontSize: 13, fontWeight: 600, color: colors.text }}>{nomActe}</div>
                              <div style={{ fontSize: 11, color: colors.textSecondary }}>Quantité: {quantite}</div>
                            </div>
                          </div>
                          <div style={{ fontSize: 13, fontWeight: 600, color: colors.primary }}>
                            {new Intl.NumberFormat('fr-FR').format(prixUnitaire * quantite)} Ar
                          </div>
                        </div>
                        {detailsAffiches.length > 0 && (
                          <div style={{ marginTop: 6, paddingTop: 6, borderTop: `1px dashed ${colors.cardBorder}`, display: 'flex', flexWrap: 'wrap', gap: '4px 12px', fontSize: 11, color: colors.textSecondary }}>
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
                  <div style={{ marginTop: 8, padding: '12px 14px', background: colors.input, borderRadius: 6, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <span style={{ fontSize: 14, fontWeight: 600, color: colors.text }}>Total</span>
                    <span style={{ fontSize: 16, fontWeight: 700, color: colors.primary }}>
                      {new Intl.NumberFormat('fr-FR').format(calculerTotalDemande(demandeSelectionnee))} Ar
                    </span>
                  </div>
                </div>
              ) : (
                <div style={{ padding: 16, textAlign: 'center', color: colors.textMuted, background: colors.input, borderRadius: 8 }}>
                  Aucun acte associé à cette demande
                </div>
              )}
            </div>

            <div style={{ marginTop: 24, display: 'flex', justifyContent: 'flex-end' }}>
              <button onClick={fermerModal} style={{ padding: '8px 20px', borderRadius: 8, border: `1px solid ${colors.cardBorder}`, background: colors.card, color: colors.text, fontSize: 13, cursor: 'pointer' }}>
                Fermer
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}