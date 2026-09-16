import { createContext, useContext, useEffect, useState } from 'react';
import api from '../api/axiosConfig';

const TypesActesContext = createContext();

export function TypesActesProvider({ children }) {
    const [typesActes, setTypesActes] = useState([]);
    const [chargement, setChargement] = useState(true);

    useEffect(() => {
        const charger = async () => {
            try {
                const res = await api.get('/types-actes');
                setTypesActes(res.data.types || res.data || []);
            } catch (err) {
                console.error('Erreur types actes:', err);
            } finally {
                setChargement(false);
            }
        };
        charger();
    }, []);

    return (
        <TypesActesContext.Provider value={{ typesActes, chargement }}>
            {children}
        </TypesActesContext.Provider>
    );
}

export const useTypesActes = () => useContext(TypesActesContext);