import { Clock, CheckCircle, XCircle, FileText, TrendingUp } from 'lucide-react';

const COULEURS_STATUT = {
  'en attente': { couleur: '#F59E0B', trackColor: '#FEF3C7', label: 'En attente', icon: Clock },
  'acceptée':   { couleur: '#10B981', trackColor: '#D1FAE5', label: 'Acceptées', icon: CheckCircle },
  'refusée':    { couleur: '#EF4444', trackColor: '#FEE2E2', label: 'Refusées', icon: XCircle },
  'en cours':   { couleur: '#3B82F6', trackColor: '#DBEAFE', label: 'En cours', icon: TrendingUp },
};

export default function Statistiques({ demandes, periodeStats, setPeriodeStats }) {
  // === Calcul du donut par statut ===
  const total = demandes.length || 1;
  const statuts = Object.keys(COULEURS_STATUT).map(key => {
    const count = demandes.filter(d => d.statut === key).length;
    return {
      key,
      count,
      pourcentage: Math.round((count / total) * 100),
      ...COULEURS_STATUT[key],
    };
  }).filter(s => s.count > 0);

  // === Calcul du donut SVG ===
  const rayon = 60;
  const circonference = 2 * Math.PI * rayon;
  let offsetCumule = 0;

  // === Calcul des données d'analytique selon la période ===
  const calculerAnalytique = (periode) => {
    const maintenant = new Date();
    let labels = [];
    let data = [];

    switch (periode) {
      case '24h': {
        for (let i = 23; i >= 0; i--) {
          const h = new Date(maintenant.getTime() - i * 60 * 60 * 1000);
          const label = h.getHours() + 'h';
          const count = demandes.filter(d => {
            if (!d.created_at) return false;
            const dc = new Date(d.created_at);
            return dc.toDateString() === h.toDateString() && dc.getHours() === h.getHours();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
      case '7jours': {
        for (let i = 6; i >= 0; i--) {
          const j = new Date(maintenant.getTime() - i * 24 * 60 * 60 * 1000);
          const label = j.toLocaleDateString('fr-FR', { weekday: 'short' });
          const count = demandes.filter(d => {
            if (!d.created_at) return false;
            return new Date(d.created_at).toDateString() === j.toDateString();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
      case '30jours': {
        for (let i = 29; i >= 0; i--) {
          const j = new Date(maintenant.getTime() - i * 24 * 60 * 60 * 1000);
          const label = j.getDate() + '/' + (j.getMonth() + 1);
          const count = demandes.filter(d => {
            if (!d.created_at) return false;
            return new Date(d.created_at).toDateString() === j.toDateString();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
      case '12mois':
      default: {
        for (let i = 11; i >= 0; i--) {
          const m = new Date(maintenant.getFullYear(), maintenant.getMonth() - i, 1);
          const label = m.toLocaleDateString('fr-FR', { month: 'short' }) + ' ' + String(m.getFullYear()).slice(2);
          const count = demandes.filter(d => {
            if (!d.created_at) return false;
            const dc = new Date(d.created_at);
            return dc.getFullYear() === m.getFullYear() && dc.getMonth() === m.getMonth();
          }).length;
          labels.push(label);
          data.push(count);
        }
        break;
      }
    }

    return { labels, data };
  };

  const { labels, data } = calculerAnalytique(periodeStats);
  const maxValue = Math.max(...data, 1);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>

      {/* ===== DONUT PAR STATUT ===== */}
      <div style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', padding: 24, boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
        <h3 style={{ margin: '0 0 4px 0', fontSize: 16, fontWeight: 700, color: '#111827' }}>Classements par statut</h3>
        <p style={{ margin: '0 0 24px 0', fontSize: 12, color: '#6B7280' }}>Répartition des statuts de demande</p>

        <div style={{ display: 'flex', alignItems: 'center', gap: 40, flexWrap: 'wrap' }}>
          {/* Donut SVG */}
          <div style={{ position: 'relative', width: 180, height: 180, flexShrink: 0 }}>
            <svg width="180" height="180" viewBox="0 0 180 180">
              {/* Fond */}
              <circle cx="90" cy="90" r={rayon} fill="none" stroke="#F3F4F6" strokeWidth="22" />
              
              {/* Segments */}
              {statuts.map((s) => {
                const dash = (s.pourcentage / 100) * circonference;
                const offset = -offsetCumule;
                offsetCumule += dash;
                return (
                  <circle
                    key={s.key}
                    cx="90" cy="90" r={rayon}
                    fill="none"
                    stroke={s.couleur}
                    strokeWidth="22"
                    strokeDasharray={`${dash} ${circonference - dash}`}
                    strokeDashoffset={offset}
                    transform="rotate(-90 90 90)"
                    style={{ transition: 'stroke-dasharray 0.6s ease' }}
                  />
                );
              })}

              {/* Centre */}
              <text x="90" y="86" textAnchor="middle" fontSize="26" fontWeight="700" fill="#111827">
                {demandes.length}
              </text>
              <text x="90" y="106" textAnchor="middle" fontSize="11" fill="#6B7280">
                Demandes
              </text>
            </svg>
          </div>

          {/* Légende */}
          <div style={{ flex: 1, minWidth: 200 }}>
            {statuts.map((s) => (
              <div key={s.key} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 0', borderBottom: '1px solid #F3F4F6' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                  <span style={{ width: 12, height: 12, borderRadius: '50%', background: s.couleur }} />
                  <span style={{ fontSize: 13, color: '#374151' }}>{s.label}</span>
                </div>
                <span style={{ fontSize: 14, fontWeight: 700, color: '#111827' }}>{s.pourcentage} %</span>
              </div>
            ))}
            {statuts.length === 0 && (
              <div style={{ padding: 20, textAlign: 'center', color: '#9CA3AF', fontSize: 13 }}>
                Aucune donnée disponible
              </div>
            )}
          </div>
        </div>
      </div>

      {/* ===== ANALYTIQUE ===== */}
      <div style={{ background: '#FFFFFF', borderRadius: 14, border: '1px solid #E5E7EB', padding: 24, boxShadow: '0 1px 3px rgba(0,0,0,0.06)' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 24, flexWrap: 'wrap', gap: 12 }}>
          <div>
            <h3 style={{ margin: '0 0 4px 0', fontSize: 16, fontWeight: 700, color: '#111827' }}>Analytique</h3>
            <p style={{ margin: 0, fontSize: 12, color: '#6B7280' }}>
              Analyse des demandes sur les {periodeStats === '24h' ? '24 dernières heures' : periodeStats === '7jours' ? '7 derniers jours' : periodeStats === '30jours' ? '30 derniers jours' : '12 derniers mois'}
            </p>
          </div>

          {/* Filtres de période */}
          <div style={{ display: 'flex', background: '#F3F4F6', borderRadius: 10, padding: 4 }}>
            {[
              { key: '12mois', label: '12 mois' },
              { key: '30jours', label: '30 jours' },
              { key: '7jours', label: '7 jours' },
              { key: '24h', label: '24 heures' },
            ].map(({ key, label }) => (
              <button
                key={key}
                onClick={() => setPeriodeStats(key)}
                style={{
                  padding: '6px 14px',
                  borderRadius: 8,
                  border: 'none',
                  background: periodeStats === key ? '#FFFFFF' : 'transparent',
                  color: periodeStats === key ? '#111827' : '#6B7280',
                  fontSize: 12,
                  fontWeight: periodeStats === key ? 600 : 400,
                  cursor: 'pointer',
                  boxShadow: periodeStats === key ? '0 1px 3px rgba(0,0,0,0.1)' : 'none'
                }}
              >
                {label}
              </button>
            ))}
          </div>
        </div>

        {/* Graphique en barres */}
        <div style={{ position: 'relative', height: 260, paddingLeft: 40, paddingBottom: 30 }}>
          {/* Axe Y */}
          {[0, 0.25, 0.5, 0.75, 1].map((ratio) => {
            const valeur = Math.round(maxValue * (1 - ratio));
            return (
              <div key={ratio} style={{ position: 'absolute', left: 0, right: 0, top: `${ratio * 100}%`, display: 'flex', alignItems: 'center' }}>
                <span style={{ fontSize: 11, color: '#9CA3AF', width: 35, textAlign: 'right', paddingRight: 6 }}>{valeur}</span>
                <div style={{ flex: 1, height: 1, background: '#F3F4F6' }} />
              </div>
            );
          })}

          {/* Barres */}
          <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: 6, height: 220, paddingLeft: 40 }}>
            {data.map((valeur, i) => {
              const hauteur = (valeur / maxValue) * 200;
              return (
                <div key={i} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
                  <div style={{ fontSize: 10, fontWeight: 600, color: '#4F46E5', height: 14 }}>
                    {valeur > 0 ? valeur : ''}
                  </div>
                  <div
                    title={`${labels[i]} : ${valeur} demande${valeur > 1 ? 's' : ''}`}
                    style={{
                      width: '100%',
                      maxWidth: 40,
                      height: Math.max(hauteur, 4),
                      background: valeur > 0 ? 'linear-gradient(180deg,#6366F1,#8B5CF6)' : '#E5E7EB',
                      borderRadius: 6,
                      transition: 'all 0.4s ease',
                    }}
                  />
                </div>
              );
            })}
          </div>

          {/* Labels X */}
          <div style={{ display: 'flex', justifyContent: 'space-between', paddingLeft: 40, marginTop: 6, gap: 6 }}>
            {labels.map((label, i) => (
              <div key={i} style={{ flex: 1, textAlign: 'center', fontSize: 10, color: '#6B7280', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {label}
              </div>
            ))}
          </div>
        </div>

        {/* Résumé */}
        <div style={{ display: 'flex', justifyContent: 'space-around', marginTop: 20, paddingTop: 16, borderTop: '1px solid #F3F4F6' }}>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: 20, fontWeight: 700, color: '#4F46E5' }}>{demandes.length}</div>
            <div style={{ fontSize: 11, color: '#6B7280' }}>Total</div>
          </div>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: 20, fontWeight: 700, color: '#10B981' }}>{demandes.filter(d => d.statut === 'acceptée').length}</div>
            <div style={{ fontSize: 11, color: '#6B7280' }}>Acceptées</div>
          </div>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: 20, fontWeight: 700, color: '#F59E0B' }}>{demandes.filter(d => d.statut === 'en attente').length}</div>
            <div style={{ fontSize: 11, color: '#6B7280' }}>En attente</div>
          </div>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: 20, fontWeight: 700, color: '#EF4444' }}>{demandes.filter(d => d.statut === 'refusée').length}</div>
            <div style={{ fontSize: 11, color: '#6B7280' }}>Refusées</div>
          </div>
        </div>
      </div>
    </div>
  );
}