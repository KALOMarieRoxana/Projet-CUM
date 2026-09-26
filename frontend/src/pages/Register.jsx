import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Onglets } from './Login';
import { verifierImageCin } from '../utils/verifierCin';
import { Mail, CheckCircle, X } from 'lucide-react'; // ✅ NOUVEAU
import '../styles/animations.css';

export default function Register() {
  const [formulaire, setFormulaire] = useState({
    nom: '', prenom: '', adresse: '',
    contact: '', relation: '', email: '', mot_de_passe: '', mot_de_passe_confirmation: ''
  });
  const { inscrire, chargement, erreur } = useAuth();
  const navigate = useNavigate();

  // ✅ NOUVEAU : Modale de succès
  const [modalSuccesOuverte, setModalSuccesOuverte] = useState(false);
  const [emailInscrit, setEmailInscrit] = useState('');
  const [compteARebours, setCompteARebours] = useState(4);

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

  // ✅ NOUVEAU : Compte à rebours automatique
  useEffect(() => {
    if (!modalSuccesOuverte) return;

    setCompteARebours(4);
    const intervalle = setInterval(() => {
      setCompteARebours((prev) => {
        if (prev <= 1) {
          clearInterval(intervalle);
          navigate('/connexion');
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(intervalle);
  }, [modalSuccesOuverte, navigate]);

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

  const compresserImageVersBase64 = (fichier) =>
    new Promise((resolve, reject) => {
      const image = new Image();
      const lecteur = new FileReader();

      lecteur.onload = () => {
        image.onload = () => {
          const canvas = document.createElement('canvas');
          const ratio = Math.min(1, 1200 / Math.max(image.width, image.height));
          const largeur = Math.max(1, Math.round(image.width * ratio));
          const hauteur = Math.max(1, Math.round(image.height * ratio));

          canvas.width = largeur;
          canvas.height = hauteur;

          const contexte = canvas.getContext('2d');
          contexte.drawImage(image, 0, 0, largeur, hauteur);

          resolve(canvas.toDataURL('image/jpeg', 0.72));
        };

        image.onerror = reject;
        image.src = lecteur.result;
      };

      lecteur.onerror = reject;
      lecteur.readAsDataURL(fichier);
    });

  // ===== FONCTION MODIFIÉE : ouvre la modale au lieu d'afficher un texte =====
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

    const cinRectoBase64 = await compresserImageVersBase64(photoRecto);
    const cinVersoBase64 = await compresserImageVersBase64(photoVerso);

    // ✅ Sauvegarder l'email avant de vider le formulaire
    const emailUtilise = formulaire.email;

    const estInscrit = await inscrire({
      ...formulaire,
      cin_recto_base64: cinRectoBase64,
      cin_verso_base64: cinVersoBase64
    });

    if (estInscrit) {
      // ✅ Sauvegarder l'email et ouvrir la modale
      setEmailInscrit(emailUtilise);
      setModalSuccesOuverte(true);

      // Réinitialiser le formulaire
      setFormulaire({
        nom: '', prenom: '', adresse: '',
        contact: '', relation: '', email: '', mot_de_passe: '', mot_de_passe_confirmation: ''
      });
      setPhotoRecto(null); setApercuRecto(null); setRectoValide(false);
      setPhotoVerso(null); setApercuVerso(null); setVersoValide(false);
    }
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

          {/* Affichage de l'erreur uniquement (le succès passe par la modale) */}
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

            <div className="ligne-double">
              <div className="champ" style={{ animationDelay: '0.14s' }}>
                <label>Contact</label>
                <input value={formulaire.contact} onChange={majChamp('contact')} placeholder="Téléphone" />
              </div>
              <div className="champ" style={{ animationDelay: '0.16s' }}>
                <label>Relation</label>
                <select value={formulaire.relation} onChange={majChamp('relation')} required>
                  <option value="">Sélectionner…</option>
                  <option value="epoux">Époux</option>
                  <option value="epouse">Épouse</option>
                  <option value="pere">Père</option>
                  <option value="mere">Mère</option>
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

      {/* ✅ MODALE DE SUCCÈS */}
      {modalSuccesOuverte && (
        <div style={styles.modalOverlay}>
          <div style={styles.modalContainer}>
            {/* Bouton fermer */}
            <button
              onClick={() => navigate('/connexion')}
              style={styles.modalCloseBtn}
              aria-label="Fermer"
            >
              <X size={18} />
            </button>

            {/* Icône succès */}
            <div style={styles.modalIconWrapper}>
              <div style={styles.modalIconCircle}>
                <CheckCircle size={36} color="#fff" />
              </div>
            </div>

            {/* Titre */}
            <h2 style={styles.modalTitle}>Inscription réussie !</h2>

            {/* Message principal */}
            <p style={styles.modalMessage}>
              Un email de confirmation a été envoyé à :
            </p>

            {/* Email en surbrillance */}
            <div style={styles.modalEmailBox}>
              <Mail size={16} color="#4F46E5" />
              <span style={styles.modalEmail}>{emailInscrit}</span>
            </div>

            {/* Instructions */}
            <p style={styles.modalInstructions}>
              Cliquez sur le lien dans l'email pour <strong>vérifier votre compte</strong> avant de vous connecter.
            </p>

            {/* Info anti-spam */}
            <div style={styles.modalInfoBox}>
              💡 Pensez à vérifier vos <strong>spams</strong> si vous ne voyez pas l'email.
            </div>

            {/* Bouton principal */}
            <button
              onClick={() => navigate('/connexion')}
              style={styles.modalButton}
            >
              Aller à la connexion
            </button>

            {/* Compte à rebours */}
            <p style={styles.modalCountdown}>
              Redirection automatique dans <strong>{compteARebours}</strong> seconde{compteARebours > 1 ? 's' : ''}…
            </p>
          </div>
        </div>
      )}
    </div>
  );
}

// ===== STYLES DE LA MODALE =====
const styles = {
  modalOverlay: {
    position: 'fixed',
    inset: 0,
    background: 'rgba(15, 23, 42, 0.6)',
    backdropFilter: 'blur(6px)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 3000,
    padding: 20,
    animation: 'fadeIn 0.2s ease-out',
  },
  modalContainer: {
    background: '#fff',
    borderRadius: 20,
    padding: '40px 32px 28px',
    maxWidth: 440,
    width: '100%',
    textAlign: 'center',
    boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
    position: 'relative',
    animation: 'slideUp 0.3s ease-out',
  },
  modalCloseBtn: {
    position: 'absolute',
    top: 16,
    right: 16,
    background: '#F3F4F6',
    border: 'none',
    borderRadius: 8,
    width: 32,
    height: 32,
    cursor: 'pointer',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    color: '#6B7280',
  },
  modalIconWrapper: {
    display: 'flex',
    justifyContent: 'center',
    marginBottom: 20,
  },
  modalIconCircle: {
    width: 72,
    height: 72,
    borderRadius: '50%',
    background: 'linear-gradient(135deg, #10B981, #059669)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    boxShadow: '0 10px 25px rgba(16, 185, 129, 0.3)',
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: 700,
    color: '#111827',
    margin: '0 0 12px 0',
  },
  modalMessage: {
    fontSize: 14,
    color: '#6B7280',
    margin: '0 0 16px 0',
    lineHeight: 1.5,
  },
  modalEmailBox: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    background: '#EEF2FF',
    borderRadius: 10,
    padding: '12px 16px',
    marginBottom: 20,
    border: '1px solid #C7D2FE',
  },
  modalEmail: {
    fontSize: 14,
    fontWeight: 600,
    color: '#4F46E5',
    wordBreak: 'break-all',
  },
  modalInstructions: {
    fontSize: 13,
    color: '#4B5563',
    margin: '0 0 16px 0',
    lineHeight: 1.6,
  },
  modalInfoBox: {
    background: '#FEF3C7',
    borderRadius: 8,
    padding: '10px 14px',
    fontSize: 12,
    color: '#92400E',
    marginBottom: 24,
    textAlign: 'left',
    border: '1px solid #FDE68A',
  },
  modalButton: {
    width: '100%',
    padding: '14px',
    borderRadius: 10,
    border: 'none',
    background: 'linear-gradient(135deg, #6366F1, #8B5CF6)',
    color: '#fff',
    fontSize: 14,
    fontWeight: 600,
    cursor: 'pointer',
    marginBottom: 12,
    boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)',
    transition: 'transform 0.15s',
  },
  modalCountdown: {
    fontSize: 12,
    color: '#9CA3AF',
    margin: 0,
  },
};