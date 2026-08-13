import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import '../styles/animations.css';

export default function Login() {
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const { connecter, chargement, erreur } = useAuth();
  const navigate = useNavigate();

  const gererSoumission = async (e) => {
    e.preventDefault();
    const succes = await connecter(email, motDePasse);
    if (succes) navigate('/tableau-de-bord');
  };


  return (
    <div className="page-auth">
      <div className="dossier">
        <div className="dossier-panneau">
          <div>
            <div className="dossier-eyebrow">République — Service Public</div>
            <h1 className="dossier-titre">Portail Citoyen d'État Civil</h1>
            <p className="dossier-texte">
              Retrouvez vos demandes, suivez leurs statuts et accédez à vos
              actes officiels en toute sécurité.
            </p>
          </div>
          <div className="sceau-wrapper">
            <div className="sceau">EC</div>
            <p className="dossier-texte" style={{ margin: 0 }}>
              Connexion chiffrée<br />et authentifiée
            </p>
          </div>
        </div>

        <div className="dossier-formulaire">
          <Onglets actif="connexion" />

          {erreur && <div className="message-erreur">{erreur}</div>}

          <form onSubmit={gererSoumission}>
            <div className="champ" style={{ animationDelay: '0.05s' }}>
              <label>Adresse email</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="vous@exemple.com"
                required
              />
            </div>
            <div className="champ" style={{ animationDelay: '0.1s' }}>
              <label>Mot de passe</label>
              <input
                type="password"
                value={motDePasse}
                onChange={(e) => setMotDePasse(e.target.value)}
                placeholder="••••••••"
                required
              />
            </div>

            <button
              type="submit"
              className={`bouton-tamponner ${chargement ? 'en-chargement' : ''}`}
              disabled={false}
            >
              {chargement ? 'Connexion en cours…' : 'Se connecter'}
            </button>
          </form>

          <p style={{ fontSize: 13, color: 'var(--ardoise)', marginTop: 18 }}>
            Pas encore de compte ? <Link to="/inscription" style={{ color: 'var(--sceau)', fontWeight: 600 }}>Créer un compte</Link>
          </p>
        </div>
      </div>
    </div>
  );
}

export function Onglets({ actif }) {
  const navigate = useNavigate();
  return (
    <div className="onglets">
      <button
        className={`onglet ${actif === 'connexion' ? 'actif' : ''}`}
        onClick={() => navigate('/connexion')}
        type="button"
      >
        Connexion
      </button>
      <button
        className={`onglet ${actif === 'inscription' ? 'actif' : ''}`}
        onClick={() => navigate('/inscription')}
        type="button"
      >
        Inscription
      </button>
      <span
        className="onglet-indicateur"
        style={{
          left: actif === 'connexion' ? '0%' : '50%',
          width: '50%'
        }}
      />
    </div>
  );
}