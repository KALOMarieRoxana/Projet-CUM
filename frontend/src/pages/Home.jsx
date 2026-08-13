// src/pages/Accueil.jsx
import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Mail, Phone, MapPin, X, Shield, Zap } from 'lucide-react';

// Import du logo et de l'image
import logo from '../assets/image/logo.png';
const MAHAJANGA_IMAGE = new URL('../assets/image/cum.jpg', import.meta.url).href;

export default function Accueil() {
  const [serviceModalOpen, setServiceModalOpen] = useState(false);
  const [contactModalOpen, setContactModalOpen] = useState(false);

  return (
    <div style={{ 
      minHeight: '100vh', 
      background: 'transparent', 
      color: '#1a1a2e', 
      fontFamily: 'Inter, sans-serif',
      position: 'relative',
      overflow: 'hidden'
    }}>
      
      {/*  IMAGE DE FOND PLEINE PAGE */}
      <div style={{ 
        position: 'fixed', 
        inset: 0, 
        backgroundImage: `url(${MAHAJANGA_IMAGE})`, 
        backgroundSize: 'cover', 
        backgroundPosition: 'center',
        filter: 'brightness(0.65) saturate(1.1)',
        zIndex: 0
      }} />

      {/* HEADER – transparent, superposé à l'image, en haut à gauche */}
      <header style={{ 
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        zIndex: 10,
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'space-between', 
        padding: '16px 32px',
        background: 'transparent',   // ← complètement transparent
        backdropFilter: 'none',      // ← pas de flou
        borderBottom: 'none',        // ← pas de bordure
        flexShrink: 0
      }}>
        
        {/* PARTIE GAUCHE : Logo + Nom + Liens */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 24 }}>
          {/* Logo + Nom du site */}
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <img 
              src={logo} 
              alt="Logo" 
              style={{ 
                height: '80px', 
                width: 'auto',
                objectFit: 'contain',
                filter: 'drop-shadow(0 2px 8px rgba(0,0,0,0.3))'
              }} 
            />
            <span style={{ 
              fontSize: '30px', 
              fontWeight: 500, 
              color: '#e9c421',
              textShadow: '0 2px 12px rgba(0,0,0,0.6)',
              letterSpacing: '-0.3px'
            }}>
              E
            </span>
            <span style={{ 
              fontSize: '30px', 
              fontWeight: 500, 
              color: 'rgba(255, 255, 255, 0.8)',
              textShadow: '0 2px 8px rgba(0,0,0,0.5)'
            }}>
              - Citoyens
            </span>
          </div>

          {/* Séparateur */}
          <span style={{ color: 'rgba(255,255,255,0.3)', fontSize: 20 }}>|</span>

          {/* Liens de navigation */}
          <Link to="/" style={{ color: '#e9c421', textDecoration: 'none', fontWeight: 600, fontSize: 20, textShadow: '0 2px 8px rgba(0,0,0,0.5)' }}>Accueil</Link>
          <Link to="/information" style={{ color: '#a5b4fc', textDecoration: 'none', fontWeight: 600, fontSize: 20, textShadow: '0 2px 8px rgba(0,0,0,0.5)' }}>Information</Link>
        </div>
        
        {/* PARTIE DROITE : Boutons Service / Contact / Se connecter */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          <button 
            onClick={() => setServiceModalOpen(true)} 
            style={{ 
              color: '#ffffff', 
              background: 'rgba(255,255,255,0.15)',
              backdropFilter: 'blur(4px)',
              border: '1px solid rgba(255,255,255,0.3)', 
              borderRadius: 999, 
              padding: '6px 18px', 
              cursor: 'pointer', 
              fontWeight: 600,
              fontSize: 13,
              transition: 'all 0.2s',
              textShadow: '0 1px 6px rgba(0,0,0,0.3)'
            }}
            onMouseEnter={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.3)'}
            onMouseLeave={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.15)'}
          >
            Service
          </button>
          <button 
            onClick={() => setContactModalOpen(true)} 
            style={{ 
              color: '#ffffff', 
              background: 'rgba(255,255,255,0.15)',
              backdropFilter: 'blur(4px)',
              border: '1px solid rgba(255,255,255,0.3)', 
              borderRadius: 999, 
              padding: '6px 18px', 
              cursor: 'pointer', 
              fontWeight: 600,
              fontSize: 13,
              transition: 'all 0.2s',
              textShadow: '0 1px 6px rgba(0,0,0,0.3)'
            }}
            onMouseEnter={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.3)'}
            onMouseLeave={(e) => e.currentTarget.style.background = 'rgba(255,255,255,0.15)'}
          >
            Contact
          </button>
          {/* BOUTON SE CONNECTER dans le header */}
          <Link to="/connexion">
            <button style={{ 
              padding: '6px 22px', 
              borderRadius: 999, 
              border: 'none', 
              background: 'linear-gradient(135deg,#4f46e5,#7c3aed)', 
              color: '#fff', 
              fontSize: 13, 
              fontWeight: 700, 
              cursor: 'pointer', 
              boxShadow: '0 8px 24px rgba(79,70,229,0.4)',
              transition: 'transform 0.2s, box-shadow 0.2s'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'scale(1.05)';
              e.currentTarget.style.boxShadow = '0 12px 32px rgba(79,70,229,0.6)';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'scale(1)';
              e.currentTarget.style.boxShadow = '0 8px 24px rgba(79,70,229,0.4)';
            }}>
              Se connecter
            </button>
          </Link>
        </div>
      </header>

      {/* CENTRE DE L'IMAGE (optionnel : on peut garder un petit message ou rien) */}
      <div style={{ 
        position: 'absolute',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        zIndex: 5,
        textAlign: 'center',
        width: '100%',
        maxWidth: 600,
        padding: '0 15px'
      }}>
        <h1 style={{ 
          fontSize: 'clamp(1.8rem, 3vw, 3rem)', 
          fontWeight: 600, 
          color: '#ffffff', 
          textShadow: '0 6px 6px rgba(0,0,0,0.7)',
          marginBottom: 8
        }}>
          Bienvenue à E-Citoyens
        </h1>
        <p style={{ 
          fontSize: 'clamp(1rem, 1.5vw, 1.3rem)', 
          color: 'rgba(231, 230, 230, 0.85)',
          textShadow: '0 2px 12px rgba(0,0,0,0.5)'
        }}>
          Accédez facilement à vos documents et informations en ligne.
        </p>
        {/* Le bouton "Se connecter" est déjà dans le header, donc plus besoin ici */}
      </div>

      {/* FOOTER – en bas à droite sur l'image */}
      <div style={{ 
        position: 'absolute',
        bottom: '14px',
        right: '32px',
        zIndex: 10,
        fontSize: '11px',
        color: 'rgba(255,255,255,0.7)',
        textShadow: '0 2px 10px rgba(0,0,0,0.5)',
        letterSpacing: '0.3px',
        backgroundColor: 'transparent',
        padding: 0,
        margin: 0,
        lineHeight: '1.3',
        textAlign: 'right'
      }}>
        &copy; {new Date().getFullYear()} Portail Citoyen - État Civil de Mahajanga.
      </div>

      {/* MODALES (inchangées) */}
      {serviceModalOpen && (
        <div style={{ position: 'fixed', inset: 0, zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,0.5)', padding: 20 }} onClick={() => setServiceModalOpen(false)}>
          <div style={{ background: '#ffffff', borderRadius: 18, width: '100%', maxWidth: 620, padding: 32, border: '1px solid #e2e8f0', boxShadow: '0 24px 80px rgba(0,0,0,0.15)' }} onClick={(e) => e.stopPropagation()}>
            <button onClick={() => setServiceModalOpen(false)} style={{ position: 'absolute', top: 18, right: 18, background: 'transparent', border: 'none', color: '#64748b', cursor: 'pointer' }}><X size={24} /></button>
            <h2 style={{ fontSize: 22, fontWeight: 700, color: '#1a1a2e', marginBottom: 22 }}>Nos services</h2>
            <div style={{ display: 'grid', gap: 18 }}>
              <div style={{ background: '#eef2ff', borderRadius: 16, padding: 18, border: '1px solid #c7d2fe' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 12 }}><Shield size={20} color="#4f46e5" /><h3 style={{ fontSize: 17, fontWeight: 700, margin: 0, color: '#1a1a2e' }}>Standard</h3></div>
                <p style={{ color: '#334155', margin: 0 }}>Traitement en 5 à 7 jours ouvrés. Prix indicatif : 5 000 Ar. Convient pour les demandes sans urgence.</p>
              </div>
              <div style={{ background: '#fef3c7', borderRadius: 16, padding: 18, border: '1px solid #fcd34d' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 12 }}><Zap size={20} color="#f59e0b" /><h3 style={{ fontSize: 17, fontWeight: 700, margin: 0, color: '#1a1a2e' }}>Express</h3></div>
                <p style={{ color: '#334155', margin: 0 }}>Traitement prioritaire sous 24 à 48 heures. Prix indicatif : 10 000 Ar. Pour les urgences de voyage ou dossier médical.</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {contactModalOpen && (
        <div style={{ position: 'fixed', inset: 0, zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,0.5)', padding: 20 }} onClick={() => setContactModalOpen(false)}>
          <div style={{ background: '#ffffff', borderRadius: 18, width: '100%', maxWidth: 560, padding: 32, border: '1px solid #e2e8f0', boxShadow: '0 24px 80px rgba(0,0,0,0.15)' }} onClick={(e) => e.stopPropagation()}>
            <button onClick={() => setContactModalOpen(false)} style={{ position: 'absolute', top: 18, right: 18, background: 'transparent', border: 'none', color: '#64748b', cursor: 'pointer' }}><X size={24} /></button>
            <h2 style={{ fontSize: 22, fontWeight: 700, color: '#1a1a2e', marginBottom: 22 }}>Contact</h2>
            <div style={{ color: '#334155', lineHeight: 1.75, display: 'grid', gap: 14 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Mail size={18} /> contact@etatcivil.mg</div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><Phone size={18} /> +261 34 12 345 67</div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}><MapPin size={18} /> Avenue de l’Indépendance, Mahajanga</div>
              <div style={{ color: '#64748b' }}>Horaires : Lundi – Vendredi, 8h – 17h</div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}