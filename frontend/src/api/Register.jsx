import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Onglets } from './Login';
import { verifierImageCin } from '../utils/verifierCin';
import '../styles/animations.css';

export default function Register() {
  const [formulaire, setFormulaire] = useState({
    nom: '', prenom: '', adresse: '',
    contact: '', relation: '', email: '', mot_de_passe: '', mot_de_passe_confirmation: ''
  });
  const { inscrire, chargement, erreur } = useAuth();
  const navigate = useNavigate();

  // ===== Photo CIN recto =====
  const [photoRecto, setPhotoRecto] = useState(null);
  const [apercuRecto, setApercuRecto] = useState(null);
  const [verifRectoEnCours, setVerifRectoEnCours] = useState(false);
  const [progressionRecto, setProgressionRecto] = useState(0);
  const [rectoValide, setRectoValide] = useState(false);
  const [erreurRecto, setErreurRecto] = useState('');

  // ===== Photo CIN verso =====
  const [photoVerso, setPhotoVerso] = useState(null);
  const [apercuVerso, setApercuVerso] = useState(null);
  const [verifVersoEnCours, setVerifVersoEnCours] = useState(false);
  const [progressionVerso, setProgressionVerso] = useState(0);
  const [versoValide, setVersoValide] = useState(false);
  const [erreurVerso, setErreurVerso] = useState('');

  const majChamp = (champ) => (e) =>
    setFormulaire({ ...formulaire, [champ]: e.target.value });

  const gererSelectionImage = async (e, cote) => {
    const fichier = e.target.files[0];
    if (!fichier) return;

    const estRecto = cote === 'recto';
    const setApercu = estRecto ? setApercuRecto : setApercuVerso;
    const setValide = estRecto ? setRectoValide : setVersoValide;
    const setErreurImg = estRecto ? setErreurRecto : setErreurVerso;
    const setEnCours = estRecto ? setVerifRectoEnCours : setVerifVersoEnCours;
    const setProgression = estRecto ? setProgressionRecto : setProgressionVerso;
    const setPhoto = estRecto ? setPhotoRecto : setPhotoVerso;

    setErreurImg('');
    setValide(false);
    setPhoto(null);
    setApercu(URL.createObjectURL(fichier));

    if (!fichier.type.startsWith('image/')) {
      setErreurImg('Le fichier doit être une image (JPG, PNG...).');
      return;
    }
    if (fichier.size > 8 * 1024 * 1024) {
      setErreurImg('L\'image est trop volumineuse (8 Mo maximum).');
      return;
    }

    try {
      setEnCours(true);
      setProgression(0);

      const resultat = await verifierImageCin(fichier, setProgression);

      if (!resultat.valide) {
        setErreurImg(`Cette image ne semble pas être le ${cote} d'une carte d'identité nationale (CIN). Merci d'uploader une photo claire et lisible.`);
        return;
      }

      setValide(true);
      setPhoto(fichier);
    } catch (err) {
      setErreurImg("Impossible d'analyser l'image. Réessaie avec une photo plus nette.");
    } finally {
      setEnCours(false);
    }
  };

  const convertirEnBase64 = (fichier) =>
    new Promise((resolve, reject) => {
      const lecteur = new FileReader();
      lecteur.onload = () => resolve(lecteur.result);
      lecteur.onerror = reject;
      lecteur.readAsDataURL(fichier);
    });

  const gererSoumission = async (e) => {
    e.preventDefault();

    if (!rectoValide || !photoRecto) {
      setErreurRecto('Merci d\'uploader une photo valide du recto de ta CIN.');
      return;
    }
    if (!versoValide || !photoVerso) {
      setErreurVerso('Merci d\'uploader une photo valide du verso de ta CIN.');
      return;
    }

    const cinRectoBase64 = await convertirEnBase64(photoRecto);
    const cinVersoBase64 = await convertirEnBase64(photoVerso);

    const succes = await inscrire({
      ...formulaire,
      cin_recto_base64: cinRectoBase64,
      cin_verso_base64: cinVersoBase64
    });
    if (succes) navigate('/tableau-de-bord');
  };

  const boutonDesactive =
    chargement || verifRectoEnCours || verifVersoEnCours || !rectoValide || !versoValide;

  return (
    <div className="page-auth">
      <div className="dossier">
        <div className="dossier-panneau">
          <div>
            <div className="dossier-eyebrow">République — Service Public</div>
            <h1 className="dossier-titre">Créer votre dossier citoyen</h1>
            <p className="dossier-texte">
              Renseignez vos informations pour ouvrir votre espace personnel
              et soumettre vos demandes d'état civil en ligne.
            </p>
          </div>
          <div className="sceau-wrapper">
            <div className="sceau">EC</div>
            <p className="dossier-texte" style={{ margin: 0 }}>
              Vos données sont<br />protégées et chiffrées
            </p>
          </div>
        </div>

        <div className="dossier-formulaire">
          <Onglets actif="inscription" />

          {erreur && <div className="message-erreur">{erreur}</div>}

          <form onSubmit={gererSoumission}>
            <div className="ligne-double">
              <div className="champ" style={{ animationDelay: '0.02s' }}>
                <label>Nom</label>
                <input value={formulaire.nom} onChange={majChamp('nom')} required />
              </div>
              <div className="champ" style={{ animationDelay: '0.05s' }}>
                <label>Prénom</label>
                <input value={formulaire.prenom} onChange={majChamp('prenom')} required />
              </div>
            </div>

            <div className="champ" style={{ animationDelay: '0.08s' }}>
              <label>Adresse</label>
              <input value={formulaire.adresse} onChange={majChamp('adresse')} placeholder="Quartier, ville" />
            </div>

            {/* ===== Photos CIN recto / verso ===== */}
            <div className="ligne-double" style={{ animationDelay: '0.1s' }}>
              <div className="champ">
                <label>Photo CIN — recto</label>
                <input
                  type="file"
                  accept="image/*"
                  onChange={(e) => gererSelectionImage(e, 'recto')}
                  required
                />
                {apercuRecto && (
                  <div style={{ marginTop: 8, display: 'flex', alignItems: 'center', gap: 8 }}>
                    <img
                      src={apercuRecto}
                      alt="Aperçu recto CIN"
                      style={{ width: 64, height: 42, objectFit: 'cover', borderRadius: 5, border: '1px solid #E7E1D2' }}
                    />
                    <div style={{ fontSize: 12 }}>
                      {verifRectoEnCours && (
                        <span style={{ color: 'var(--ardoise)' }}>Vérif… {progressionRecto}%</span>
                      )}
                      {!verifRectoEnCours && rectoValide && (
                        <span style={{ color: '#2E7D32', fontWeight: 600 }}>✓ Reconnu</span>
                      )}
                    </div>
                  </div>
                )}
                {erreurRecto && (
                  <div className="message-erreur" style={{ marginTop: 8, fontSize: 12 }}>{erreurRecto}</div>
                )}
              </div>

              <div className="champ">
                <label>Photo CIN — verso</label>
                <input
                  type="file"
                  accept="image/*"
                  onChange={(e) => gererSelectionImage(e, 'verso')}
                  required
                />
                {apercuVerso && (
                  <div style={{ marginTop: 8, display: 'flex', alignItems: 'center', gap: 8 }}>
                    <img
                      src={apercuVerso}
                      alt="Aperçu verso CIN"
                      style={{ width: 64, height: 42, objectFit: 'cover', borderRadius: 5, border: '1px solid #E7E1D2' }}
                    />
                    <div style={{ fontSize: 12 }}>
                      {verifVersoEnCours && (
                        <span style={{ color: 'var(--ardoise)' }}>Vérif… {progressionVerso}%</span>
                      )}
                      {!verifVersoEnCours && versoValide && (
                        <span style={{ color: '#2E7D32', fontWeight: 600 }}>✓ Reconnu</span>
                      )}
                    </div>
                  </div>
                )}
                {erreurVerso && (
                  <div className="message-erreur" style={{ marginTop: 8, fontSize: 12 }}>{erreurVerso}</div>
                )}
              </div>
            </div>
            {/* ===== Fin photos CIN ===== */}

            <div className="ligne-double">
              <div className="champ" style={{ animationDelay: '0.14s' }}>
                <label>Contact</label>
                <input value={formulaire.contact} onChange={majChamp('contact')} placeholder="Téléphone" />
              </div>
              <div className="champ" style={{ animationDelay: '0.16s' }}>
                <label>Relation</label>
                <select value={formulaire.relation} onChange={majChamp('relation')} required>
                  <option value="">Sélectionner…</option>
                  <option value="chef_menage">Chef de ménage</option>
                  <option value="epoux">Époux</option>
                  <option value="epouse">Épouse</option>
                  <option value="pere">Père</option>
                  <option value="mere">Mère</option>
                  <option value="enfant">Enfant</option>
                  <option value="etudiant">Étudiant</option>
                  <option value="frere_soeur">Frère / Sœur</option>
                  <option value="autre">Autre</option>
                </select>
              </div>
            </div>

            <div className="champ" style={{ animationDelay: '0.18s' }}>
              <label>Adresse email</label>
              <input type="email" value={formulaire.email} onChange={majChamp('email')} required />
            </div>

            <div className="champ" style={{ animationDelay: '0.2s' }}>
              <label>Mot de passe</label>
              <input
                type="password"
                value={formulaire.mot_de_passe}
                onChange={majChamp('mot_de_passe')}
                required
              />
            </div>

            <div className="champ" style={{ animationDelay: '0.22s' }}>
              <label>Confirmer le mot de passe</label>
              <input
                type="password"
                value={formulaire.mot_de_passe_confirmation}
                onChange={majChamp('mot_de_passe_confirmation')}
                required
              />
            </div>
              
            <button
              type="submit"
              disabled={boutonDesactive}
              className={`bouton-tamponner ${boutonDesactive ? 'desactive' : ''}`}
              aria-busy={chargement}
            >
              {chargement ? 'Création en cours…' : 'Créer mon compte'}
            </button>
          </form>
                
          <p style={{ fontSize: 13, color: 'var(--ardoise)', marginTop: 18 }}>
            Déjà inscrit ? <Link to="/connexion" style={{ color: 'var(--sceau)', fontWeight: 600 }}>Se connecter</Link>
          </p>
        </div>
      </div>
    </div>
  );
}
