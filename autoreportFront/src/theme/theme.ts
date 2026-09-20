import { createTheme, responsiveFontSizes, alpha } from '@mui/material/styles';

// Extend Material UI theme for custom platform colors
declare module '@mui/material/styles' {
  interface Palette {
    shopee: Palette['primary'];
    tiktok: Palette['primary'];
  }
  interface PaletteOptions {
    shopee?: PaletteOptions['primary'];
    tiktok?: PaletteOptions['primary'];
  }
}

export function getAppTheme(mode: 'light' | 'dark' = 'light') {
  const isDark = mode === 'dark';

  let theme = createTheme({
    palette: {
      mode,
      primary: {
        main: '#4F46E5', // Indigo 600
        light: '#818CF8',
        dark: '#3730A3',
        contrastText: '#FFFFFF',
      },
      secondary: {
        main: '#0EA5E9', // Sky 500
        light: '#38BDF8',
        dark: '#0369A1',
        contrastText: '#FFFFFF',
      },
      shopee: {
        main: '#EE4D2D', // Official Shopee Orange
        light: '#FF7A59',
        dark: '#C7381B',
        contrastText: '#FFFFFF',
      },
      tiktok: {
        main: '#E11D48', // TikTok Rose/Pink
        light: '#F43F5E',
        dark: '#BE123C',
        contrastText: '#FFFFFF',
      },
      background: {
        default: isDark ? '#0B0F19' : '#F8FAFC',
        paper: isDark ? '#111827' : '#FFFFFF',
      },
      text: {
        primary: isDark ? '#F9FAFB' : '#0F172A',
        secondary: isDark ? '#9CA3AF' : '#64748B',
      },
      divider: isDark ? alpha('#FFFFFF', 0.1) : '#E2E8F0',
    },
    typography: {
      fontFamily: '"Inter", "Roboto", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
      h1: { fontWeight: 800, letterSpacing: '-0.025em' },
      h2: { fontWeight: 800, letterSpacing: '-0.02em' },
      h3: { fontWeight: 700, letterSpacing: '-0.02em' },
      h4: { fontWeight: 700, letterSpacing: '-0.015em' },
      h5: { fontWeight: 600, letterSpacing: '-0.01em' },
      h6: { fontWeight: 600 },
      button: { textTransform: 'none', fontWeight: 600 },
    },
    shape: {
      borderRadius: 14,
    },
    components: {
      MuiButton: {
        styleOverrides: {
          root: {
            borderRadius: 12,
            boxShadow: 'none',
            padding: '8px 16px',
            fontSize: '0.875rem',
            '&:hover': {
              boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
            },
          },
          containedPrimary: {
            background: 'linear-gradient(135deg, #4F46E5 0%, #4338CA 100%)',
          },
        },
      },
      MuiPaper: {
        defaultProps: {
          elevation: 0,
        },
        styleOverrides: {
          root: {
            borderRadius: 16,
            border: isDark ? '1px solid rgba(255,255,255,0.08)' : '1px solid #E2E8F0',
            backgroundImage: 'none',
          },
        },
      },
      MuiCard: {
        defaultProps: {
          elevation: 0,
        },
        styleOverrides: {
          root: {
            borderRadius: 16,
            border: isDark ? '1px solid rgba(255,255,255,0.08)' : '1px solid #E2E8F0',
            transition: 'all 0.2s ease-in-out',
            '&:hover': {
              boxShadow: isDark
                ? '0 8px 24px rgba(0,0,0,0.4)'
                : '0 8px 24px rgba(15,23,42,0.06)',
            },
          },
        },
      },
      MuiTableCell: {
        styleOverrides: {
          root: {
            borderColor: isDark ? 'rgba(255,255,255,0.08)' : '#F1F5F9',
            fontSize: '0.8125rem',
          },
          head: {
            fontWeight: 700,
            textTransform: 'uppercase',
            letterSpacing: '0.04em',
            fontSize: '0.725rem',
          },
        },
      },
      MuiChip: {
        styleOverrides: {
          root: {
            fontWeight: 600,
            borderRadius: 8,
          },
        },
      },
    },
  });

  return responsiveFontSizes(theme);
}
