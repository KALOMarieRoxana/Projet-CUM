import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Mail, Phone, MapPin, Shield, Server, X, Zap, Info } from 'lucide-react';

export default function Information() {
  const [serviceModalOpen, setServiceModalOpen] = useState(false);
  const [contactModalOpen, setContactModalOpen] = useState(false);

  return (
    <div style={{ minHeight: '100vh', background: '#0F0F0F', color: '#E5E7EB', fontFamily: 'Inter, sans-serif' }}>
      <header style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '20px 32px', borderBottom: '1px solid #262626' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 24 }}>
          <Link to="/" style={{ color: '#F9FAFB', textDecoration: 'none', fontWeight: 700, fontSize: 18 }}>Accueil</Link>
          <Link to="/information" style={{ color: '#A5B4FC', textDecoration: 'none', fontWeight: 700, fontSize: 18 }}>Information</Link>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
          <button onClick={() => setServiceModalOpen(true)} style={{ color: '#F9FAFB', background: 'transparent', border: '1px solid #6366F1', borderRadius: 999, padding: '10px 18px', cursor: 'pointer', fontWeight: 600 }}>Service</button>
          <button onClick={() => setContactModalOpen(true)} style={{ color: '#F9FAFB', background: 'transparent', border: '1px solid #6366F1', borderRadius: 999, padding: '10px 18px', cursor: 'pointer', fontWeight: 600 }}>Contact</button>
        </div>
      </header>

      <main style={{ maxWidth: 1000, margin: '0 auto', padding: '48px 24px 80px' }}>
        <section style={{ marginBottom: 40 }}>
          <p style={{ textTransform: 'uppercase', letterSpacing: '0.2em', color: '#818CF8', fontSize: 12, marginBottom: 12 }}>Portail citoyen</p>
          <h1 style={{ fontSize: 'clamp(2.5rem, 4vw, 3.5rem)', fontWeight: 800, marginBottom: 20, color: '#F9FAFB' }}>Toutes les informations sur les demandes d’état civil</h1>
          <p style={{ fontSize: 17, lineHeight: 1.8, color: '#D1D5DB', maxWidth: 760 }}>Sur cette page, vous trouverez les types d'actes, les documents requis, les modalités, les délais et les procédures pour vos demandes à Mahajanga.</p>
        </section>

        <section style={{ display: 'grid', gap: 24 }}>
          <div style={{ background: '#161616', borderRadius: 20, border: '1px solid #262626', padding: 28 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 18 }}>
              <Info size={24} color="#818CF8" />
              <h2 style={{ fontSize: 22, fontWeight: 700, margin: 0, color: '#F9FAFB' }}>Informations générales</h2>
            </div>
            <p style={{ color: '#D1D5DB', lineHeight: 1.75, marginBottom: 16 }}>Le portail permet aux citoyens de Mahajanga de déposer des demandes d'acte en ligne, de suivre l'avancement et de recevoir les documents officiels en toute sécurité.</p>
            <ul style={{ paddingLeft: 20, color: '#D1D5DB', lineHeight: 1.75 }}>
              <li>Accès sécurisé avec compte personnel.</li>
              <li>Suivi en temps réel des demandes.</li>
              <li>Support pour naissance, mariage, décès, divorce et autres actes.</li>
            </ul>
          </div>

          <div style={{ display: 'grid', gap: 24, gridTemplateColumns: '1fr 1fr' }}>
            <div style={{ background: '#161616', borderRadius: 20, border: '1px solid #262626', padding: 24 }}>
              <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 14, color: '#F9FAFB' }}>Types d'actes</h3>
              <ul style={{ paddingLeft: 20, color: '#D1D5DB', lineHeight: 1.8 }}>
                <li>Acte de naissance</li>
                <li>Acte de mariage</li>
                <li>Acte de décès</li>
                <li>Acte de divorce</li>
                <li>Bulletin de naissance</li>
              </ul>
            </div>
            <div style={{ background: '#161616', borderRadius: 20, border: '1px solid #262626', padding: 24 }}>
              <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 14, color: '#F9FAFB' }}>Documents requis</h3>
              <ul style={{ paddingLeft: 20, color: '#D1D5DB', lineHeight: 1.8 }}>
                <li>Pièce d'identité du demandeur</li>
                <li>Pièce d'identité de la personne concernée</li>
                <li>Justificatif de domicile</li>
                <li>Formulaire rempli</li>
              </ul>
            </div>
          </div>

          <div style={{ background: '#161616', borderRadius: 20, border: '1px solid #262626', padding: 24 }}>
            <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 14, color: '#F9FAFB' }}>Procédure</h3>
            <ol style={{ paddingLeft: 20, color: '#D1D5DB', lineHeight: 1.8 }}>
              <li>Créer un compte ou se connecter.</li>
              <li>Choisir le type d'acte et compléter le formulaire.</li>
              <li>Téléverser les pièces justificatives.</li>
              <li>Sélectionner le service standard ou express.</li>
              <li>Soumettre la demande et suivre le statut.</li>
            </ol>
          </div>

          <div style={{ display: 'grid', gap: 24, gridTemplateColumns: '1fr 1fr' }}>
            <div style={{ background: '#161616', borderRadius: 20, border: '1px solid #262626', padding: 24 }}>
              <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 14, color: '#F9FAFB' }}>Délais</h3>
              <p style={{ color: '#D1D5DB', lineHeight: 1.75 }}>Les délais varient selon le service choisi :</p>
              <ul style={{ paddingLeft: 20, color: '#D1D5DB', lineHeight: 1.8 }}>
                <li>Standard : 5 à 7 jours ouvrés.</li>
                <li>Express : 24 à 48 heures.</li>
              </ul>
            </div>
            <div style={{ background: '#161616', borderRadius: 20, border: '1px solid #262626', padding: 24 }}>
              <h3 style={{ fontSize: 18, fontWeight: 700, marginBottom: 14, color: '#F9FAFB' }}>Horaires et contact</h3>
              <div style={{ display: 'grid', gap: 14, color: '#D1D5DB', lineHeight: 1.75 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Mail size={18} /> contact@etatcivil.mg</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Phone size={18} /> +261 34 12 345 67</div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><MapPin size={18} /> Avenue de l’Indépendance, Mahajanga</div>
                <div style={{ fontSize: 14, color: '#9CA3AF' }}>Lundi – Vendredi, 8h – 17h</div>
              </div>
            </div>
          </div>
        </section>
      </main>

      {serviceModalOpen && (
        <div style={{ position: 'fixed', inset: 0, zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,0.72)', padding: 20 }} onClick={() => setServiceModalOpen(false)}>
          <div style={{ background: '#1A1A1A', borderRadius: 18, width: '100%', maxWidth: 620, padding: 32, border: '1px solid #2A2A2A', boxShadow: '0 24px 80px rgba(0,0,0,0.45)' }} onClick={(e) => e.stopPropagation()}>
            <button onClick={() => setServiceModalOpen(false)} style={{ position: 'absolute', top: 18, right: 18, background: 'transparent', border: 'none', color: '#9CA3AF', cursor: 'pointer' }}><X size={24} /></button>
            <h2 style={{ fontSize: 22, fontWeight: 700, color: '#F9FAFB', marginBottom: 22 }}>Nos services</h2>
            <div style={{ display: 'grid', gap: 18 }}>
              <div style={{ background: 'rgba(99,102,241,0.08)', borderRadius: 16, padding: 18, border: '1px solid rgba(99,102,241,0.2)' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 12 }}><Shield size={20} color="#818CF8" /><h3 style={{ fontSize: 17, fontWeight: 700, margin: 0, color: '#F9FAFB' }}>Standard</h3></div>
                <p style={{ color: '#D1D5DB', margin: 0 }}>Traitement en 5 à 7 jours ouvrés. Prix indicatif : 5 000 Ar. Convient pour les demandes sans urgence.</p>
              </div>
              <div style={{ background: 'rgba(245,158,11,0.08)', borderRadius: 16, padding: 18, border: '1px solid rgba(245,158,11,0.2)' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 12 }}><Zap size={20} color="#F59E0B" /><h3 style={{ fontSize: 17, fontWeight: 700, margin: 0, color: '#F9FAFB' }}>Express</h3></div>
                <p style={{ color: '#D1D5DB', margin: 0 }}>Traitement prioritaire sous 24 à 48 heures. Prix indicatif : 10 000 Ar. Pour les urgences de voyage ou dossier médical.</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {contactModalOpen && (
        <div style={{ position: 'fixed', inset: 0, zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,0.72)', padding: 20 }} onClick={() => setContactModalOpen(false)}>
          <div style={{ background: '#1A1A1A', borderRadius: 18, width: '100%', maxWidth: 560, padding: 32, border: '1px solid #2A2A2A', boxShadow: '0 24px 80px rgba(0,0,0,0.45)' }} onClick={(e) => e.stopPropagation()}>
            <button onClick={() => setContactModalOpen(false)} style={{ position: 'absolute', top: 18, right: 18, background: 'transparent', border: 'none', color: '#9CA3AF', cursor: 'pointer' }}><X size={24} /></button>
            <h2 style={{ fontSize: 22, fontWeight: 700, color: '#F9FAFB', marginBottom: 22 }}>Contact</h2>
            <div style={{ color: '#D1D5DB', lineHeight: 1.75, display: 'grid', gap: 14 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Mail size={18} /> contact@etatcivil.mg</div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Phone size={18} /> +261 34 12 345 67</div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><MapPin size={18} /> Avenue de l’Indépendance, Mahajanga</div>
              <div style={{ color: '#9CA3AF' }}>Horaires : Lundi – Vendredi, 8h – 17h</div>
            </div>
          </div>
        </div>
      )}

      <footer style={{ borderTop: '1px solid #262626', padding: '20px 32px', textAlign: 'center', fontSize: 13, color: '#6B7280' }}>
        &copy; {new Date().getFullYear()} Portail Citoyen – État Civil de Mahajanga. Tous droits réservés.
      </footer>
    </div>
  );
}
