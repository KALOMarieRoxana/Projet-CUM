import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../theme/ThemeContext';
import api from '../api/axiosConfig';
import {
    FileText, Home, Plus, LogOut, ArrowLeft, Download,
    Eye, X, Search, Calendar, User, Clock, CheckCircle
} from 'lucide-react';
import logo from '../assets/image/logo.png';

const API_URL = process.env.REACT_APP_API_URL || 'http://127.0.0.1:8000';

export default function MesTelechargements() {
    const { utilisateur, deconnecter } = useAuth();
    const { colors } = useTheme();
    const navigate = useNavigate();
    
    const [demandes, setDemandes] = useState([]);
    const [chargement, setChargement] = useState(true);
    const [erreur, setErreur] = useState('');
    const [recherche, setRecherche] = useState('');
    const [pdfSelectionne, setPdfSelectionne] = useState(null);

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
            const toutes = res.data.demandes || [];
            
            // ✅ Filtrer : garder seulement celles avec PDF
            const avecPdf = toutes.filter(d => 
                d.statut === 'acceptée' && d.pdf_path
            );
            
            setDemandes(avecPdf);
        } catch (err) {
            console.error('Erreur:', err);
            setErreur('Impossible de charger vos documents.');
        } finally {
            setChargement(false);
        }
    };

    const gererDeconnexion = () => {
        deconnecter();
        navigate('/connexion');
    };

    const getNomTypeActe = (item) => {
        if (typeof item.type_acte === 'object' && item.type_acte?.nom) return item.type_acte.nom;
        if (typeof item.typeActe === 'object' && item.typeActe?.nom) return item.typeActe.nom;
        return 'Document';
    };

    // Filtrer par recherche
    const demandesFiltrees = demandes.filter(d => {
        if (!recherche) return true;
        const search = recherche.toLowerCase();
        return (
            d.reference?.toLowerCase().includes(search) ||
            d.demandeur_nom?.toLowerCase().includes(search) ||
            d.demandeur_prenom?.toLowerCase().includes(search) ||
            d.personne_nom?.toLowerCase().includes(search) ||
            d.personne_prenom?.toLowerCase().includes(search)
        );
    });

    if (chargement) {
        return (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: colors.bg }}>
                <div style={{ fontSize: 16, color: colors.textSecondary }}>Chargement...</div>
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
                        { icon: FileText, label: 'Mes demandes', actif: false, lien: '/mes-demandes' },
                        { icon: Plus, label: 'Nouvelle demande', actif: false, lien: '/nouvelle-demande' },
                        { icon: Download, label: 'Mes téléchargements', actif: true, lien: '/mes-telechargements' },
                    ].map(({ icon: Icon, label, actif, lien }) => (
                        <Link key={label} to={lien} style={{ textDecoration: 'none' }}>
                            <div style={{
                                display: 'flex', alignItems: 'center', gap: 10,
                                padding: '10px 12px', borderRadius: 8, marginBottom: 4,
                                background: actif ? colors.primaryLight : 'transparent',
                                color: actif ? colors.primary : colors.textSecondary,
                                fontWeight: actif ? 600 : 400,
                                cursor: 'pointer'
                            }}>
                                <Icon size={16} />
                                <span style={{ fontSize: 13 }}>{label}</span>
                            </div>
                        </Link>
                    ))}
                </nav>

                <div style={{ padding: '16px 12px', borderTop: `1px solid ${colors.sidebarBorder}` }}>
                    <button
                        onClick={gererDeconnexion}
                        style={{ width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', borderRadius: 8, border: 'none', background: 'transparent', color: colors.danger, cursor: 'pointer', fontSize: 13 }}
                    >
                        <LogOut size={16} />
                        Se déconnecter
                    </button>
                </div>
            </div>

            {/* ===== CONTENU PRINCIPAL ===== */}
            <div style={{ marginLeft: 240, flex: 1, padding: '32px 32px' }}>

                {/* Header */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 32 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <Link to="/tableau-de-bord" style={{ color: colors.primary, textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 4 }}>
                            <ArrowLeft size={18} />
                            <span style={{ fontSize: 13 }}>Retour</span>
                        </Link>
                        <h1 style={{ margin: 0, fontSize: 24, fontWeight: 700, color: colors.text }}>
                             Mes téléchargements
                        </h1>
                    </div>

                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, position: 'relative' }}>
                        <Search size={16} style={{ position: 'absolute', left: 12, color: colors.textMuted }} />
                        <input
                            type="text"
                            placeholder="Rechercher..."
                            value={recherche}
                            onChange={(e) => setRecherche(e.target.value)}
                            style={{ padding: '8px 12px 8px 36px', borderRadius: 8, border: `1px solid ${colors.inputBorder}`, fontSize: 13, width: 280, background: colors.input, color: colors.text }}
                        />
                    </div>
                </div>

                {/* Info */}
                {demandes.length > 0 && (
                    <div style={{
                        padding: 16,
                        background: 'linear-gradient(135deg, #D1FAE5, #A7F3D0)',
                        border: '1px solid #52e7e7',
                        borderRadius: 12,
                        marginBottom: 24,
                        display: 'flex',
                        alignItems: 'center',
                        gap: 12
                    }}>
                        <div style={{
                            width: 40, height: 40, borderRadius: '50%',
                            background: '#30f381', color: '#FFF',
                            display: 'flex', alignItems: 'center', justifyContent: 'center'
                        }}>
                            <CheckCircle size={20} />
                        </div>
                        <div>
                            <div style={{ fontSize: 14, fontWeight: 600, color: '#065F46' }}>
                                {demandes.length} document{demandes.length > 1 ? 's' : ''} disponible{demandes.length > 1 ? 's' : ''}
                            </div>
                            <div style={{ fontSize: 12, color: '#047857' }}>
                                Cliquez sur un document pour le visualiser
                            </div>
                        </div>
                    </div>
                )}

                {erreur && (
                    <div style={{ padding: '12px 16px', borderRadius: 10, background: '#FEE2E2', color: '#991B1B', marginBottom: 24, fontSize: 13 }}>
                        {erreur}
                    </div>
                )}

                {/* Liste des PDF */}
                {demandesFiltrees.length === 0 ? (
                    <div style={{
                        textAlign: 'center',
                        padding: 60,
                        background: colors.card,
                        borderRadius: 14,
                        border: `1px solid ${colors.cardBorder}`
                    }}>
                        <Download size={64} color={colors.textMuted} style={{ marginBottom: 16 }} />
                        <h3 style={{ fontSize: 18, fontWeight: 600, color: colors.text, marginBottom: 8 }}>
                            Aucun document disponible
                        </h3>
                        <p style={{ color: colors.textSecondary, fontSize: 14, marginBottom: 20 }}>
                            Vos documents apparaîtront ici une fois vos demandes acceptées.
                        </p>
                        <Link to="/nouvelle-demande">
                            <button style={{
                                padding: '10px 20px', borderRadius: 8,
                                border: 'none', background: colors.primary,
                                color: '#FFF', fontSize: 13, fontWeight: 600,
                                cursor: 'pointer'
                            }}>
                                Faire une nouvelle demande
                            </button>
                        </Link>
                    </div>
                ) : (
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))', gap: 20 }}>
                        {demandesFiltrees.map((demande) => (
                            <div
                                key={demande.id_demande}
                                style={{
                                    background: colors.card,
                                    borderRadius: 14,
                                    border: `1px solid ${colors.cardBorder}`,
                                    overflow: 'hidden',
                                    transition: 'all 0.2s',
                                    cursor: 'pointer'
                                }}
                                onMouseEnter={(e) => {
                                    e.currentTarget.style.transform = 'translateY(-4px)';
                                    e.currentTarget.style.boxShadow = '0 8px 20px rgba(0,0,0,0.1)';
                                }}
                                onMouseLeave={(e) => {
                                    e.currentTarget.style.transform = 'translateY(0)';
                                    e.currentTarget.style.boxShadow = 'none';
                                }}
                            >
                                {/* Header avec icône PDF */}
                                <div style={{
                                    padding: 20,
                                    background: 'linear-gradient(135deg, #10B981, #4b7dda)',
                                    color: '#FFF',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 12
                                }}>
                                    <div style={{
                                        width: 48, height: 48, borderRadius: 12,
                                        background: 'rgba(255,255,255,0.2)',
                                        display: 'flex', alignItems: 'center', justifyContent: 'center'
                                    }}>
                                        <FileText size={24} />
                                    </div>
                                    <div style={{ flex: 1, minWidth: 0 }}>
                                        <div style={{ fontSize: 16, fontWeight: 700, marginBottom: 2 }}>
                                            {demande.reference}
                                        </div>
                                        <div style={{ fontSize: 11, opacity: 0.9 }}>
                                            {new Date(demande.date_traitement || demande.created_at).toLocaleDateString('fr-FR')}
                                        </div>
                                    </div>
                                </div>

                                {/* Corps */}
                                <div style={{ padding: 20 }}>
                                    <div style={{ marginBottom: 12 }}>
                                        <div style={{ fontSize: 11, color: colors.textSecondary, marginBottom: 4 }}>
                                            👤 Demandeur
                                        </div>
                                        <div style={{ fontSize: 13, fontWeight: 600, color: colors.text }}>
                                            {demande.demandeur_prenom} {demande.demandeur_nom}
                                        </div>
                                    </div>

                                    <div style={{ marginBottom: 12 }}>
                                        <div style={{ fontSize: 11, color: colors.textSecondary, marginBottom: 4 }}>
                                            📄 Type d'acte
                                        </div>
                                        <div style={{ fontSize: 13, color: colors.text }}>
                                            {demande.demande_actes?.map(a => getNomTypeActe(a)).join(', ') || 'Document'}
                                        </div>
                                    </div>

                                    <div style={{ marginBottom: 16 }}>
                                        <div style={{ fontSize: 11, color: colors.textSecondary, marginBottom: 4 }}>
                                            📅 Traité le
                                        </div>
                                        <div style={{ fontSize: 13, color: colors.text }}>
                                            {demande.date_traitement 
                                                ? new Date(demande.date_traitement).toLocaleString('fr-FR')
                                                : '—'
                                            }
                                        </div>
                                    </div>

                                    {/* Actions */}
                                    <div style={{ display: 'flex', gap: 8 }}>
                                        <button
                                            onClick={() => setPdfSelectionne(demande)}
                                            style={{
                                                flex: 1,
                                                padding: '10px',
                                                borderRadius: 8,
                                                border: `1px solid ${colors.cardBorder}`,
                                                background: colors.input,
                                                color: colors.primary,
                                                fontSize: 12,
                                                fontWeight: 600,
                                                cursor: 'pointer',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                gap: 6
                                            }}
                                        >
                                            <Eye size={14} /> Voir
                                        </button>
                                        <a
                                            href={`${API_URL}/storage/${demande.pdf_path}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            download
                                            style={{
                                                flex: 1,
                                                padding: '10px',
                                                borderRadius: 8,
                                                border: 'none',
                                                background: 'linear-gradient(135deg, #10B981, #059669)',
                                                color: '#FFF',
                                                fontSize: 12,
                                                fontWeight: 600,
                                                cursor: 'pointer',
                                                textDecoration: 'none',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                gap: 6
                                            }}
                                        >
                                            <Download size={14} /> Télécharger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* ===== MODAL APERÇU PDF ===== */}
            {pdfSelectionne && (
                <div
                    style={{
                        position: 'fixed',
                        inset: 0,
                        background: 'rgba(0,0,0,0.8)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        zIndex: 3000,
                        backdropFilter: 'blur(4px)'
                    }}
                    onClick={() => setPdfSelectionne(null)}
                >
                    <div
                        style={{
                            background: '#FFF',
                            borderRadius: 16,
                            width: '90%',
                            maxWidth: 1000,
                            height: '90vh',
                            display: 'flex',
                            flexDirection: 'column',
                            overflow: 'hidden'
                        }}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Header modal */}
                        <div style={{
                            padding: '16px 20px',
                            borderBottom: '1px solid #E5E7EB',
                            display: 'flex',
                            justifyContent: 'space-between',
                            alignItems: 'center',
                            background: 'linear-gradient(135deg, #10B981, #059669)',
                            color: '#FFF'
                        }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                <FileText size={20} />
                                <div>
                                    <div style={{ fontSize: 15, fontWeight: 700 }}>
                                        {pdfSelectionne.reference}
                                    </div>
                                    <div style={{ fontSize: 11, opacity: 0.9 }}>
                                        Aperçu du document
                                    </div>
                                </div>
                            </div>
                            <div style={{ display: 'flex', gap: 8 }}>
                                <a
                                    href={`${API_URL}/storage/${pdfSelectionne.pdf_path}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    download
                                    style={{
                                        padding: '8px 14px',
                                        borderRadius: 8,
                                        background: 'rgba(255,255,255,0.2)',
                                        color: '#FFF',
                                        fontSize: 12,
                                        fontWeight: 600,
                                        textDecoration: 'none',
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 6
                                    }}
                                >
                                    <Download size={14} /> Télécharger
                                </a>
                                <button
                                    onClick={() => setPdfSelectionne(null)}
                                    style={{
                                        width: 36, height: 36,
                                        borderRadius: 8,
                                        border: 'none',
                                        background: 'rgba(255,255,255,0.2)',
                                        color: '#FFF',
                                        cursor: 'pointer',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center'
                                    }}
                                >
                                    <X size={18} />
                                </button>
                            </div>
                        </div>

                        {/* Iframe PDF */}
                        <iframe
                            src={`${API_URL}/storage/${pdfSelectionne.pdf_path}`}
                            style={{
                                width: '100%',
                                flex: 1,
                                border: 'none'
                            }}
                            title="Aperçu PDF"
                        />
                    </div>
                </div>
            )}
        </div>
    );
}