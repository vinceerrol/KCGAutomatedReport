import React, { useState, useMemo } from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { ThemeProvider, CssBaseline } from '@mui/material';
import { getAppTheme } from './theme/theme';
import { AppLayout } from './layouts/AppLayout';
import { Dashboard } from './pages/Dashboard';
import { ShopeeBreakdown } from './pages/ShopeeBreakdown';
import { TikTokBreakdown } from './pages/TikTokBreakdown';
import { Reports } from './pages/Reports';
import { Shops } from './pages/Shops';
import { Platforms } from './pages/Platforms';
import { Automation } from './pages/Automation';

export const App: React.FC = () => {
  const [mode, setMode] = useState<'light' | 'dark'>('light');

  const theme = useMemo(() => getAppTheme(mode), [mode]);

  const toggleTheme = () => {
    setMode((prev) => (prev === 'light' ? 'dark' : 'light'));
  };

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <BrowserRouter>
        <AppLayout mode={mode} onToggleTheme={toggleTheme}>
          <Routes>
            <Route path="/" element={<Dashboard />} />
            <Route path="/shopee" element={<ShopeeBreakdown />} />
            <Route path="/tiktok" element={<TikTokBreakdown />} />
            <Route path="/reports" element={<Reports />} />
            <Route path="/shops" element={<Shops />} />
            <Route path="/platforms" element={<Platforms />} />
            <Route path="/automation" element={<Automation />} />
          </Routes>
        </AppLayout>
      </BrowserRouter>
    </ThemeProvider>
  );
};

export default App;
