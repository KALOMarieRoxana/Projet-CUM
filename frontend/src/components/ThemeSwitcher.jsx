import { useState, useRef, useEffect } from 'react';
import { Palette, Check, ChevronDown } from 'lucide-react';
import { useTheme } from '../theme/ThemeContext';

export default function ThemeSwitcher() {
  const { themeKey, changerTheme, THEMES, colors } = useTheme();
  const [ouvert, setOuvert] = useState(false);
  const ref = useRef(null);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (ref.current && !ref.current.contains(event.target)) {
        setOuvert(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div ref={ref} style={{ position: 'relative' }}>
      <button
        onClick={() => setOuvert(!ouvert)}
        style={{
          display: 'flex',
          alignItems: 'center',
          gap: 8,
          padding: '8px 12px',
          borderRadius: 10,
          border: `1px solid ${colors.cardBorder}`,
          background: colors.card,
          color: colors.text,
          cursor: 'pointer',
          fontSize: 13,
        }}
      >
        <Palette size={16} />
        <span style={{ fontSize: 12 }}>{THEMES[themeKey].name}</span>
        <ChevronDown size={14} style={{ transform: ouvert ? 'rotate(180deg)' : 'rotate(0)', transition: '0.2s' }} />
      </button>

      {ouvert && (
        <div
          style={{
            position: 'absolute',
            top: 'calc(100% + 8px)',
            right: 0,
            width: 200,
            background: colors.card,
            borderRadius: 12,
            border: `1px solid ${colors.cardBorder}`,
            boxShadow: '0 10px 30px rgba(0,0,0,0.15)',
            padding: 8,
            zIndex: 2000,
          }}
        >
          {Object.entries(THEMES).map(([key, t]) => (
            <button
              key={key}
              onClick={() => {
                changerTheme(key);
                setOuvert(false);
              }}
              style={{
                width: '100%',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                padding: '8px 12px',
                borderRadius: 8,
                border: 'none',
                background: themeKey === key ? colors.primaryLight : 'transparent',
                color: themeKey === key ? colors.primary : colors.text,
                cursor: 'pointer',
                fontSize: 13,
              }}
            >
              <span style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <span
                  style={{
                    width: 16,
                    height: 16,
                    borderRadius: '50%',
                    background: t.colors.primaryGradient,
                    border: `2px solid ${t.colors.cardBorder}`,
                  }}
                />
                {t.name}
              </span>
              {themeKey === key && <Check size={14} />}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}