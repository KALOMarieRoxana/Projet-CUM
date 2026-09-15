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

  // ✅ CALCUL TOTAL avec suppléments
  const calculerTotalDemande = (demande) => {
    const actes = demande.demande_actes || demande.demandeActes || [];
    if (actes.length === 0) return 0;
    return actes.reduce((sum, item) => {
      const prixActe = parseFloat(item.prix_acte || item.prix_unitaire || 0);
      const prixSupp = parseFloat(item.prix_supplement || 0);
      const qteActe = parseInt(item.quantite || 1);
      const qteSupp = item.supplement_id ? parseInt(item.quantite_supplement || 0) : 0;

      const sousTotalActe = prixActe * qteActe;
      const sousTotalSupp = item.supplement_id ? prixSupp * qteSupp : 0;

      return sum + sousTotalActe + sousTotalSupp;
    }, 0);
  };

  // ✅ COMPTER actes + suppléments
  const compterTotalItems = (demande) => {
    const actes = demande.demande_actes || demande.demandeActes || [];
    return actes.reduce((sum, item) => {
      const qteActe = parseInt(item.quantite || 1);
      const qteSupp = item.supplement_id ? parseInt(item.quantite_supplement || 0) : 0;
      return sum + qteActe + qteSupp;
    }, 0);
  };

  // ✅ Helper : récupérer le slug du type d'acte
  const getSlugTypeActe = (item) => {
    if (typeof item.type_acte === 'string') return item.type_acte;
    if (item.type_acte?.type_acte) return item.type_acte.type_acte;
    if (item.typeActe?.type_acte) return item.typeActe.type_acte;
    return null;
  };

  // ✅ Helper : récupérer le nom de l'acte
  const getNomTypeActe = (item) => {
    const slug = getSlugTypeActe(item);
    return (typeof item.type_acte === 'object' && item.type_acte?.nom) ||
           (typeof item.typeActe === 'object' && item.typeActe?.nom) ||
           LABELS_TYPE[slug] ||
           slug ||
           'Acte inconnu';
  };

  // ✅ Helper : récupérer le nom du supplément
  const getNomSupplement = (item) => {
    if (!item) return null;
    if (item.supplement) {
      return item.supplement.nom || item.supplement.libelle || null;
    }
    return item.supplement_nom || null;
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
                  const nbActes = compterTotalItems(demande);

                  return (
                    <div key={demande.id_demande || demande.id} style={{ background: colors.card, borderRadius: 12, border: `1px solid ${colors.cardBorder}`, padding: 16 }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <div style={{ flex: 1 }}>
                          {/* Référence + Statut + Date */}
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

                          {/* Demandeur + Concerné + Total */}
                          <div style={{ display: 'flex', gap: 24, fontSize: 13, color: colors.textSecondary, flexWrap: 'wrap', marginBottom: 8 }}>
                            <div>
                              <span style={{ fontWeight: 500 }}>Demandeur :</span> {demande.demandeur_prenom} {demande.demandeur_nom}
                            </div>
                            <div>
                              <span style={{ fontWeight: 500 }}>Concerné :</span> {demande.personne_prenom} {demande.personne_nom}
                            </div>
                            <div>
                              <span style={{ fontWeight: 500 }}>Total actes + docs :</span> {nbActes}
                            </div>
                            <div>
                              <span style={{ fontWeight: 500 }}>Total :</span>
                              <span style={{ color: colors.primary, fontWeight: 700 }}>
                                {new Intl.NumberFormat('fr-FR').format(totalDemande)} Ar
                              </span>
                            </div>
                          </div>

                          {/* ✅ DÉTAIL DES ACTES (avec ou sans sous-type) */}
                          {actes.length > 0 && (
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 6, marginTop: 6 }}>
                              {actes.map((a, idx) => {
                                const nomType = getNomTypeActe(a);
                                const nomSupp = getNomSupplement(a);
                                const langue = (a.langue || 'FR').toUpperCase();
                                const qteActe = parseInt(a.quantite || 1);
                                const qteSupp = a.supplement_id ? parseInt(a.quantite_supplement || 0) : 0;
                                const prixActe = parseFloat(a.prix_acte || 0);
                                const prixSupp = parseFloat(a.prix_supplement || 0);

                                const sousTotalActe = prixActe * qteActe;
                                const sousTotalSupp = a.supplement_id ? prixSupp * qteSupp : 0;
                                const totalLigne = sousTotalActe + sousTotalSupp;
                                const aSupplement = a.supplement_id && nomSupp;

                                return (
                                  <div
                                    key={idx}
                                    style={{
                                      padding: '8px 12px',
                                      background: colors.input,
                                      borderRadius: 6,
                                      fontSize: 12
                                    }}
                                  >
                                    {/* 📄 LIGNE 1 : TYPE ACTE */}
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                      <span style={{ fontWeight: 600, color: colors.text }}>
                                        📄 {nomType}
                                      </span>
                                      <span style={{ color: colors.textMuted }}>
                                        ({langue})
                                      </span>
                                      <span style={{
                                        padding: '2px 7px',
                                        borderRadius: 10,
                                        background: '#FEF3C7',
                                        color: '#92400E',
                                        fontSize: 10,
                                        fontWeight: 600
                                      }}>
                                        × {qteActe}
                                      </span>
                                      <span style={{ marginLeft: 'auto', color: colors.textSecondary, fontWeight: 600 }}>
                                        {new Intl.NumberFormat('fr-FR').format(sousTotalActe)} Ar
                                      </span>
                                    </div>

                                    {/* 📋 LIGNE 2 : SOUS-TYPE (si présent) */}
                                    {aSupplement && (
                                      <>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, paddingLeft: 16, marginTop: 4 }}>
                                          <span style={{ fontWeight: 600, color: '#4F46E5' }}>
                                            📋 {nomSupp}
                                          </span>
                                          <span style={{
                                            padding: '2px 7px',
                                            borderRadius: 10,
                                            background: '#DBEAFE',
                                            color: '#1E40AF',
                                            fontSize: 10,
                                            fontWeight: 600
                                          }}>
                                            × {qteSupp}
                                          </span>
                                          <span style={{ marginLeft: 'auto', color: '#4F46E5', fontWeight: 600 }}>
                                            {new Intl.NumberFormat('fr-FR').format(sousTotalSupp)} Ar
                                          </span>
                                        </div>

                                        {/* 💰 LIGNE 3 : TOTAL LIGNE */}
                                        <div style={{
                                          display: 'flex',
                                          justifyContent: 'space-between',
                                          paddingTop: 6,
                                          marginTop: 6,
                                          borderTop: `1px dashed ${colors.cardBorder}`
                                        }}>
                                          <span style={{ fontSize: 11, color: colors.textSecondary, fontWeight: 500 }}>
                                            Total ligne
                                          </span>
                                          <span style={{ color: colors.primary, fontWeight: 700, fontSize: 13 }}>
                                            {new Intl.NumberFormat('fr-FR').format(totalLigne)} Ar
                                          </span>
                                        </div>
                                      </>
                                    )}
                                  </div>
                                );
                              })}
                            </div>
                          )}
                        </div>

                        <button
                          onClick={() => ouvrirModal(demande)}
                          style={{ padding: '6px 12px', borderRadius: 6, border: `1px solid ${colors.cardBorder}`, background: colors.input, color: colors.primary, fontSize: 12, cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 4, flexShrink: 0 }}
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
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, backdropFilter: 'blur(4px)' }}>
          <div style={{ background: colors.card, borderRadius: 16, padding: 32, maxWidth: 950, width: '100%', maxHeight: '85vh', overflow: 'auto' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
              <h2 style={{ margin: 0, fontSize: 20, fontWeight: 700, color: colors.text }}>
                Détails de la demande
              </h2>
              <button onClick={fermerModal} style={{ border: 'none', background: 'transparent', color: colors.textSecondary, cursor: 'pointer' }}>
                <X size={24} />
              </button>
            </div>

            {/* Infos générales */}
            <div style={{ marginBottom: 24, padding: 16, background: colors.input, borderRadius: 8 }}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, fontSize: 13, color: colors.text }}>
                <div><span style={{ fontWeight: 600 }}>Référence :</span> {demandeSelectionnee.reference}</div>
                <div>
                  <span style={{ fontWeight: 600 }}>Statut :</span>{' '}
                  <span style={{ padding: '2px 10px', borderRadius: 12, background: STATUTS[demandeSelectionnee.statut]?.bg, color: STATUTS[demandeSelectionnee.statut]?.color, fontWeight: 600 }}>
                    {STATUTS[demandeSelectionnee.statut]?.label || demandeSelectionnee.statut}
                  </span>
                </div>
                <div><span style={{ fontWeight: 600 }}>Date :</span> {new Date(demandeSelectionnee.created_at).toLocaleDateString('fr-FR')}</div>
                <div><span style={{ fontWeight: 600 }}>Service global :</span> {demandeSelectionnee.service === 'express' ? '⚡ Express' : '🛡 Standard'}</div>
              </div>
            </div>

            {/* Informations demandeur */}
            <div style={{ marginBottom: 16 }}>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: colors.text }}>👤 Informations demandeur</h3>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, fontSize: 13, padding: 12, background: colors.input, borderRadius: 8, color: colors.text }}>
                <div><span style={{ fontWeight: 600 }}>Nom :</span> {demandeSelectionnee.demandeur_nom}</div>
                <div><span style={{ fontWeight: 600 }}>Prénom :</span> {demandeSelectionnee.demandeur_prenom}</div>
                <div><span style={{ fontWeight: 600 }}>Adresse :</span> {demandeSelectionnee.demandeur_adresse}</div>
                <div><span style={{ fontWeight: 600 }}>Contact :</span> {demandeSelectionnee.demandeur_contact}</div>
                <div><span style={{ fontWeight: 600 }}>Relation :</span> {demandeSelectionnee.demandeur_relation}</div>
              </div>
            </div>

            {/* Personne concernée */}
            <div style={{ marginBottom: 20 }}>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: colors.text }}>👥 Personne concernée</h3>
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

            {/* ===== LISTE DES ACTES DEMANDÉS (TABLEAU) ===== */}
            <div>
              <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: colors.text }}>📄 Liste des actes demandés</h3>
              {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes || []).length > 0 ? (
                <div style={{ overflowX: 'auto', borderRadius: 8, border: `1px solid ${colors.cardBorder}` }}>
                  <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12 }}>
                    <thead>
                      <tr style={{ background: colors.input }}>
                        <th style={{ padding: '10px 12px', textAlign: 'left', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Type d'acte</th>
                        <th style={{ padding: '10px 12px', textAlign: 'left', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Sous-type / Document</th>
                        <th style={{ padding: '10px 12px', textAlign: 'center', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Qté acte</th>
                        <th style={{ padding: '10px 12px', textAlign: 'center', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Qté doc</th>
                        <th style={{ padding: '10px 12px', textAlign: 'center', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Langue</th>
                        <th style={{ padding: '10px 12px', textAlign: 'right', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Prix acte</th>
                        <th style={{ padding: '10px 12px', textAlign: 'right', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Prix doc</th>
                        <th style={{ padding: '10px 12px', textAlign: 'right', fontWeight: 600, color: colors.text, borderBottom: `1px solid ${colors.cardBorder}` }}>Sous-total</th>
                      </tr>
                    </thead>
                    <tbody>
                      {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes).map((item, index) => {
                        const nomActe = getNomTypeActe(item);
                        const nomSupp = getNomSupplement(item);
                        const langue = (item.langue || 'FR').toUpperCase();
                        const qteActe = parseInt(item.quantite || 1);
                        const qteSupp = item.supplement_id ? parseInt(item.quantite_supplement || 0) : 0;
                        const prixActe = parseFloat(item.prix_acte || item.prix_unitaire || 0);
                        const prixSupp = parseFloat(item.prix_supplement || 0);
                        const sousTotal = (prixActe * qteActe) + (item.supplement_id ? prixSupp * qteSupp : 0);
                        const aSupplement = item.supplement_id && nomSupp;

                        return (
                          <tr key={index} style={{ borderBottom: index < (demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes).length - 1 ? `1px solid ${colors.cardBorder}` : 'none' }}>
                            <td style={{ padding: '10px 12px', color: colors.text, fontWeight: 600 }}>
                              {nomActe}
                            </td>
                            <td style={{ padding: '10px 12px', color: aSupplement ? '#4F46E5' : colors.textMuted, fontWeight: aSupplement ? 600 : 400 }}>
                              {aSupplement ? nomSupp : '— Aucun —'}
                            </td>
                            <td style={{ padding: '10px 12px', textAlign: 'center', color: colors.text, fontWeight: 600 }}>
                              {qteActe}
                            </td>
                            <td style={{ padding: '10px 12px', textAlign: 'center', color: aSupplement ? '#4F46E5' : colors.textMuted, fontWeight: 600 }}>
                              {aSupplement ? qteSupp : '—'}
                            </td>
                            <td style={{ padding: '10px 12px', textAlign: 'center' }}>
                              <span style={{
                                padding: '2px 8px',
                                borderRadius: 10,
                                fontSize: 11,
                                fontWeight: 600,
                                background: langue === 'MG' ? '#FEF3C7' : '#DBEAFE',
                                color: langue === 'MG' ? '#92400E' : '#1E40AF'
                              }}>
                                {langue === 'MG' ? '🇲🇬 MG' : '🇫🇷 FR'}
                              </span>
                            </td>
                            <td style={{ padding: '10px 12px', textAlign: 'right', color: colors.text }}>
                              {new Intl.NumberFormat('fr-FR').format(prixActe)} Ar
                            </td>
                            <td style={{ padding: '10px 12px', textAlign: 'right', color: aSupplement ? '#4F46E5' : colors.textMuted }}>
                              {aSupplement ? `${new Intl.NumberFormat('fr-FR').format(prixSupp)} Ar` : '—'}
                            </td>
                            <td style={{ padding: '10px 12px', textAlign: 'right', color: colors.primary, fontWeight: 700 }}>
                              {new Intl.NumberFormat('fr-FR').format(sousTotal)} Ar
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                    <tfoot>
                      <tr style={{ background: colors.input }}>
                        <td colSpan={7} style={{ padding: '12px', textAlign: 'right', fontWeight: 600, color: colors.text, fontSize: 13 }}>
                          TOTAL GÉNÉRAL
                        </td>
                        <td style={{ padding: '12px', textAlign: 'right', fontWeight: 700, color: colors.primary, fontSize: 15 }}>
                          {new Intl.NumberFormat('fr-FR').format(calculerTotalDemande(demandeSelectionnee))} Ar
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              ) : (
                <div style={{ padding: 16, textAlign: 'center', color: colors.textMuted, background: colors.input, borderRadius: 8 }}>
                  Aucun acte associé à cette demande
                </div>
              )}
            </div>

            {/* ===== DÉTAILS SPÉCIFIQUES PAR ACTE ===== */}
            {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes || []).length > 0 && (
              <div style={{ marginTop: 24 }}>
                <h3 style={{ fontSize: 15, fontWeight: 600, margin: '0 0 12px 0', color: colors.text }}>📋 Détails spécifiques par acte</h3>
                {(demandeSelectionnee.demande_actes || demandeSelectionnee.demandeActes).map((item, index) => {
                  const slug = getSlugTypeActe(item);
                  const nomActe = getNomTypeActe(item);
                  const nomSupp = getNomSupplement(item);
                  const details = item.details || {};
                  const detailsAffiches = Object.keys(details)
                    .filter(key => details[key] !== null && details[key] !== undefined && String(details[key]).trim() !== '')
                    .map(key => {
                      const champ = CHAMPS_SPECIFIQUES[slug]?.find(c => c.name === key);
                      return champ ? { label: champ.label, value: details[key] } : { label: key, value: details[key] };
                    })
                    .filter(d => d.value && String(d.value).trim());

                  if (detailsAffiches.length === 0) return null;

                  return (
                    <div key={index} style={{ marginBottom: 12, padding: 12, background: colors.input, borderRadius: 8 }}>
                      <div style={{ fontSize: 13, fontWeight: 600, color: colors.text, marginBottom: 8 }}>
                        {nomActe}
                        {nomSupp && (
                          <span style={{ color: '#4F46E5', marginLeft: 8 }}>
                            + {nomSupp}
                          </span>
                        )}
                      </div>
                      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '6px 20px', fontSize: 12, color: colors.textSecondary }}>
                        {detailsAffiches.map((d, i) => (
                          <div key={i}>
                            <span style={{ fontWeight: 500, color: colors.text }}>{d.label} :</span> {String(d.value)}
                          </div>
                        ))}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}

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