import { useState, useEffect } from 'react';
import api from '../../api/axiosConfig';

export default function GestionPrix() {
    const [types, setTypes] = useState([]);
    const [chargement, setChargement] = useState(true);
    const [succes, setSucces] = useState('');

    useEffect(() => {
        chargerTypes();
    }, []);

    const chargerTypes = async () => {
        try {
            const res = await api.get('/types-actes');
            setTypes(res.data.types || []);
        } catch (err) {
            console.error(err);
        } finally {
            setChargement(false);
        }
    };

    const handleChange = (id, champ, valeur) => {
        setTypes(prev => prev.map(t => 
            t.id === id ? { ...t, [champ]: valeur } : t
        ));
    };

    const sauvegarder = async (type) => {
        try {
            await api.put(`/admin/types-actes/${type.id}`, {
                montantStandardMG: type.montantStandardMG,
                montantExpressMG: type.montantExpressMG,
                montantStandardFR: type.montantStandardFR,
                montantExpressFR: type.montantExpressFR,
            });
            setSucces(`✅ Prix de ${type.nom} mis à jour !`);
            setTimeout(() => setSucces(''), 3000);
        } catch (err) {
            alert('Erreur lors de la sauvegarde');
        }
    };

    if (chargement) return <div>Chargement...</div>;

    return (
        <div style={{ padding: 32 }}>
            <h1>💰 Gestion des prix des actes</h1>

            {succes && (
                <div style={{ padding: 12, background: '#D1FAE5', color: '#065F46', borderRadius: 8, marginBottom: 20 }}>
                    {succes}
                </div>
            )}

            {types.map(type => (
                <div key={type.id} style={{
                    background: '#FFF',
                    padding: 20,
                    borderRadius: 12,
                    border: '1px solid #E5E7EB',
                    marginBottom: 16,
                }}>
                    <h3 style={{ marginTop: 0 }}>📄 {type.nom}</h3>

                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: 16 }}>
                        <div>
                            <label style={{ fontSize: 12, fontWeight: 600 }}>Standard MG (Ar)</label>
                            <input
                                type="number"
                                value={type.montantStandardMG}
                                onChange={(e) => handleChange(type.id, 'montantStandardMG', e.target.value)}
                                style={{ width: '100%', padding: 8, borderRadius: 6, border: '1px solid #E5E7EB' }}
                            />
                        </div>
                        <div>
                            <label style={{ fontSize: 12, fontWeight: 600 }}>Express MG (Ar)</label>
                            <input
                                type="number"
                                value={type.montantExpressMG}
                                onChange={(e) => handleChange(type.id, 'montantExpressMG', e.target.value)}
                                style={{ width: '100%', padding: 8, borderRadius: 6, border: '1px solid #E5E7EB' }}
                            />
                        </div>
                        <div>
                            <label style={{ fontSize: 12, fontWeight: 600 }}>Standard FR (Ar)</label>
                            <input
                                type="number"
                                value={type.montantStandardFR}
                                onChange={(e) => handleChange(type.id, 'montantStandardFR', e.target.value)}
                                style={{ width: '100%', padding: 8, borderRadius: 6, border: '1px solid #E5E7EB' }}
                            />
                        </div>
                        <div>
                            <label style={{ fontSize: 12, fontWeight: 600 }}>Express FR (Ar)</label>
                            <input
                                type="number"
                                value={type.montantExpressFR}
                                onChange={(e) => handleChange(type.id, 'montantExpressFR', e.target.value)}
                                style={{ width: '100%', padding: 8, borderRadius: 6, border: '1px solid #E5E7EB' }}
                            />
                        </div>
                    </div>

                    <button
                        onClick={() => sauvegarder(type)}
                        style={{
                            marginTop: 16,
                            padding: '10px 20px',
                            background: '#4F46E5',
                            color: '#FFF',
                            border: 'none',
                            borderRadius: 8,
                            cursor: 'pointer',
                            fontWeight: 600,
                        }}
                    >
                        💾 Enregistrer
                    </button>
                </div>
            ))}
        </div>
    );
}