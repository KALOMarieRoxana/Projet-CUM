import { createContext, useContext, useEffect, useState } from 'react';
import { THEMES } from './themes';

const ThemeContext = createContext();

export function ThemeProvider({ children }) {
  const [themeKey, setThemeKey] = useState(() => {
    return localStorage.getItem('app-theme') || 'light';
  });

  useEffect(() => {
    localStorage.setItem('app-theme', themeKey);
    document.body.style.background = THEMES[themeKey].colors.bg;
  }, [themeKey]);

  const theme = THEMES[themeKey];
  const colors = theme.colors;

  const changerTheme = (key) => {
    if (THEMES[key]) setThemeKey(key);
  };

  return (
    <ThemeContext.Provider value={{ themeKey, theme, colors, changerTheme, THEMES }}>
      {children}
    </ThemeContext.Provider>
  );
}

export function useTheme() {
  const ctx = useContext(ThemeContext);
  if (!ctx) throw new Error('useTheme doit être utilisé dans ThemeProvider');
  return ctx;
}