import { useState, useEffect } from 'react';
import { Zap, Shield, CheckCircle, X, Timer, Clock } from 'lucide-react';
import api from '../api/axiosConfig';

export default function ModalEstimation({ demande, onClose, onStatutChange }) {
    const [tempsRestant, setTempsRestant] = useState('');
    const [progression, setProgression] = useState(0);
    const [estimationPrete, setEstimationPrete] = useState(false);
    const [statutChange, setStatutChange] = useState(false);
    const [nouveauStatut, setNouveauStatut] = useState(null);

    // ⏱️ Compte à rebours
    useEffect(() => {
        if (!demande) return;

        const calculer = () => {
            const maintenant = new Date();
            const dateEstimation = new Date(demande.date_estimation);
            const dateCreation = new Date(demande.created_at);
            
            const diffMs = dateEstimation - maintenant;
            const totalMs = dateEstimation - dateCreation;
            const ecouleMs = maintenant - dateCreation;

            const pourcentage = Math.min(100, Math.max(0, (ecouleMs / totalMs) * 100));
            setProgression(pourcentage);

            if (diffMs <= 0) {
                setEstimationPrete(true);
                setTempsRestant('00:00:00');
                return;
            }

            const jours = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            const heures = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            const secondes = Math.floor((diffMs % (1000 * 60)) / 1000);

            if (jours > 0) {
                setTempsRestant(`${jours}j ${String(heures).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}min`);
            } else {
                setTempsRestant(`${String(heures).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secondes).padStart(2, '0')}`);
            }
        };

        calculer();
        const interval = setInterval(calculer, 1000);
        return () => clearInterval(interval);
    }, [demande]);

    // 🔄 POLLING : Vérifier le statut toutes les 10 secondes
    useEffect(() => {
        if (!demande?.reference) return;

        const verifierStatut = async () => {
            try {
                const res = await api.get('/demandes/${demandes.reference}/status');
                
                if (demandeActuelle && demandeActuelle.statut !== 'en_attente') {
                    console.log('✅ Statut changé :', demandeActuelle.statut);
                    setStatutChange(true);
                    setNouveauStatut(demandeActuelle.statut);
                    
                    // ⏱️ Attendre 2 secondes puis fermer
                    setTimeout(() => {
                        if (onStatutChange) {
                            onStatutChange(demandeActuelle);
                        }
                        onClose();
                    }, 2000);
                }
            } catch (err) {
                // Silencieux
            }
        };

        // Vérifier toutes les 10 secondes
        const interval = setInterval(verifierStatut, 10000);
        
        // Vérifier immédiatement
        verifierStatut();

        return () => clearInterval(interval);
    }, [demande, onClose, onStatutChange]);

    if (!demande) return null;

    const isExpress = demande.service === 'express';
    const serviceLabel = isExpress ? 'Express' : 'Standard';
    const delaiHeures = demande.delai_heures || (isExpress ? 24 : 72);
    const couleurService = isExpress ? '#D97706' : '#4F46E5';
    const bgService = isExpress ? '#FEF3C7' : '#EEF2FF';

    // ✅ Si le statut a changé → Afficher un message spécial
    if (statutChange) {
        const isAcceptee = nouveauStatut === 'acceptée';
        const couleurStatut = isAcceptee ? '#059669' : '#DC2626';
        const bgStatut = isAcceptee ? '#D1FAE5' : '#FEE2E2';
        
        return (
            <div style={{
                position: 'fixed', inset: 0,
                background: 'rgba(0,0,0,0.6)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                zIndex: 3000, backdropFilter: 'blur(4px)'
            }}>
                <div style={{
                    background: '#FFF', borderRadius: 20,
                    width: '100%', maxWidth: 480, padding: 40,
                    boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
                    textAlign: 'center',
                    animation: 'slideUp 0.3s ease-in-out'
                }}>
                    <div style={{
                        width: 80, height: 80, borderRadius: '50%',
                        background: bgStatut, color: couleurStatut,
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        margin: '0 auto 20px',
                        animation: 'pulse 1s infinite'
                    }}>
                        {isAcceptee ? <CheckCircle size={40} /> : <X size={40} />}
                    </div>

                    <h2 style={{
                        margin: '0 0 12px', fontSize: 24,
                        fontWeight: 700, color: couleurStatut
                    }}>
                        {isAcceptee ? '✅ Demande acceptée !' : '❌ Demande refusée'}
                    </h2>

                    <p style={{
                        margin: '0 0 24px', fontSize: 14,
                        color: '#6B7280', lineHeight: 1.6
                    }}>
                        {isAcceptee 
                            ? 'Votre demande a été traitée avec succès. Redirection en cours...'
                            : 'Votre demande a été refusée. Redirection en cours...'
                        }
                    </p>

                    <div style={{
                        width: '100%', height: 4,
                        background: '#F3F4F6', borderRadius: 2,
                        overflow: 'hidden'
                    }}>
                        <div style={{
                            height: '100%',
                            background: couleurStatut,
                            animation: 'progress 2s linear forwards',
                            borderRadius: 2
                        }} />
                    </div>
                </div>

                <style>{`
                    @keyframes progress {
                        from { width: 0%; }
                        to { width: 100%; }
                    }
                `}</style>
            </div>
        );
    }

    // Modal normal
    return (
        <div style={{
            position: 'fixed', inset: 0,
            background: 'rgba(0,0,0,0.6)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            zIndex: 3000, backdropFilter: 'blur(4px)'
        }}>
            <div style={{
                background: '#FFF', borderRadius: 20,
                width: '100%', maxWidth: 520, padding: 32,
                boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
                position: 'relative',
                animation: 'slideUp 0.3s ease-in-out'
            }}>
                <button
                    onClick={onClose}
                    style={{
                        position: 'absolute', top: 16, right: 16,
                        background: 'rgba(0,0,0,0.05)',
                        border: 'none', borderRadius: 8,
                        width: 32, height: 32,
                        cursor: 'pointer',
                        display: 'flex', alignItems: 'center', justifyContent: 'center'
                    }}
                >
                    <X size={18} />
                </button>

                {/* Header */}
                <div style={{ textAlign: 'center', marginBottom: 24 }}>
                    <div style={{
                        width: 72, height: 72, borderRadius: '50%',
                        background: bgService, color: couleurService,
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        margin: '0 auto 16px',
                        animation: estimationPrete ? 'pulse 1s infinite' : 'none'
                    }}>
                        {estimationPrete ? <CheckCircle size={36} /> : (isExpress ? <Zap size={36} /> : <Shield size={36} />)}
                    </div>

                    <h2 style={{
                        margin: '0 0 8px', fontSize: 22,
                        fontWeight: 700, color: '#111827'
                    }}>
                        {estimationPrete ? '✅ Estimation terminée !' : 'Estimation en cours...'}
                    </h2>

                    <p style={{ margin: 0, fontSize: 14, color: '#6B7280' }}>
                        Demande <strong>{demande.reference}</strong>
                    </p>
                </div>

                {/* Badge Service */}
                <div style={{
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    gap: 8, padding: '12px 16px',
                    background: bgService, color: couleurService,
                    borderRadius: 12, marginBottom: 24,
                    fontWeight: 600, fontSize: 14
                }}>
                    {isExpress ? <Zap size={16} /> : <Shield size={16} />}
                    Service {serviceLabel} — Délai max {delaiHeures}h
                </div>

                {/* Progression */}
                <div style={{ marginBottom: 24 }}>
                    <div style={{
                        display: 'flex', justifyContent: 'space-between',
                        marginBottom: 8, fontSize: 12, color: '#6B7280'
                    }}>
                        <span>Progression</span>
                        <span style={{ fontWeight: 600, color: couleurService }}>
                            {Math.round(progression)}%
                        </span>
                    </div>

                    <div style={{
                        width: '100%', height: 12, background: '#F3F4F6',
                        borderRadius: 6, overflow: 'hidden'
                    }}>
                        <div style={{
                            width: `${progression}%`, height: '100%',
                            background: `linear-gradient(90deg, ${couleurService}, ${isExpress ? '#F59E0B' : '#8B5CF6'})`,
                            transition: 'width 1s linear', borderRadius: 6
                        }} />
                    </div>
                </div>

                {/* Compte à rebours */}
                <div style={{
                    textAlign: 'center', padding: 24,
                    background: estimationPrete ? '#D1FAE5' : '#F9FAFB',
                    borderRadius: 12, marginBottom: 24,
                    border: estimationPrete ? '2px solid #10B981' : 'none'
                }}>
                    <div style={{
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        gap: 8, marginBottom: 12
                    }}>
                        {estimationPrete ? (
                            <CheckCircle size={18} color="#059669" />
                        ) : (
                            <Timer size={18} color={couleurService} />
                        )}
                        <span style={{
                            fontSize: 13,
                            color: estimationPrete ? '#065F46' : '#6B7280',
                            fontWeight: 500
                        }}>
                            {estimationPrete ? 'Estimation disponible' : 'Temps restant estimé'}
                        </span>
                    </div>

                    <div style={{
                        fontSize: estimationPrete ? 42 : 32,
                        fontWeight: 800,
                        color: estimationPrete ? '#059669' : couleurService,
                        fontFamily: 'monospace',
                        letterSpacing: 1
                    }}>
                        {estimationPrete ? '✓' : tempsRestant}
                    </div>

                    {/* ✅ Message d'attente de validation */}
                    <div style={{
                        fontSize: 11,
                        color: '#6B7280',
                        marginTop: 12,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        gap: 6
                    }}>
                        <div style={{
                            width: 6, height: 6, borderRadius: '50%',
                            background: '#10B981',
                            animation: 'pulse 1s infinite'
                        }} />
                        En attente de la réponse de l'administration
                    </div>
                </div>

                {/* Info */}
                <div style={{
                    padding: 16,
                    background: isExpress ? '#FEF3C7' : '#EFF6FF',
                    border: `1px solid ${isExpress ? '#FDE68A' : '#BFDBFE'}`,
                    borderRadius: 10, marginBottom: 24
                }}>
                    <div style={{ display: 'flex', alignItems: 'flex-start', gap: 10 }}>
                        <Clock
                            size={18}
                            color={isExpress ? '#D97706' : '#2563EB'}
                            style={{ flexShrink: 0, marginTop: 2 }}
                        />
                        <div style={{
                            fontSize: 13,
                            color: isExpress ? '#92400E' : '#1E40AF',
                            lineHeight: 1.6
                        }}>
                            {isExpress ? (
                                <>
                                    <strong>⚡ Service Express</strong>
                                    <br />
                                    Votre demande sera traitée dans un délai maximum de <strong>24 heures</strong>.
                                    <br />
                                    <em style={{ fontSize: 11, color: '#D97706' }}>
                                        💡 Le modal se fermera automatiquement si la demande est traitée.
                                    </em>
                                </>
                            ) : (
                                <>
                                    <strong>🛡 Service Standard</strong>
                                    <br />
                                    Votre demande sera traitée dans un délai maximum de <strong>72 heures</strong>.
                                    <br />
                                    <em style={{ fontSize: 11, color: '#2563EB' }}>
                                        💡 Le modal se fermera automatiquement si la demande est traitée.
                                    </em>
                                </>
                            )}
                        </div>
                    </div>
                </div>

                {/* Bouton */}
                <button
                    onClick={onClose}
                    style={{
                        width: '100%', padding: '12px',
                        borderRadius: 10, border: 'none',
                        background: `linear-gradient(135deg, ${couleurService}, ${isExpress ? '#F59E0B' : '#8B5CF6'})`,
                        color: '#FFF', fontSize: 14,
                        fontWeight: 600, cursor: 'pointer'
                    }}
                >
                    Fermer (l'estimation continue)
                </button>
            </div>

            <style>{`
                @keyframes slideUp {
                    from { opacity: 0; transform: translateY(20px) scale(0.95); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
                @keyframes pulse {
                    0%, 100% { transform: scale(1); opacity: 1; }
                    50% { transform: scale(1.05); opacity: 0.8; }
                }
            `}</style>
        </div>
    );
}