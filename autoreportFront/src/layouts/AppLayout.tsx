import React, { useState, useEffect } from 'react';
import {
  Box,
  Drawer,
  AppBar,
  Toolbar,
  Typography,
  IconButton,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Button,
  Chip,
  Container,
  Divider,
  useMediaQuery,
  useTheme,
  Snackbar,
  Alert,
  CircularProgress,
  Tooltip,
} from '@mui/material';
import {
  DashboardRounded,
  ShoppingBagRounded,
  TrendingUpRounded,
  AssessmentRounded,
  StoreRounded,
  LayersRounded,
  MemoryRounded,
  PlayArrowRounded,
  MenuRounded,
  Brightness4Rounded,
  Brightness7Rounded,
  AccessTimeRounded,
  AutoAwesomeRounded,
} from '@mui/icons-material';
import { Link, useLocation } from 'react-router-dom';
import { ReportsService } from '../services/api';

const DRAWER_WIDTH = 260;

interface AppLayoutProps {
  children: React.ReactNode;
  mode: 'light' | 'dark';
  onToggleTheme: () => void;
}

export const AppLayout: React.FC<AppLayoutProps> = ({ children, mode, onToggleTheme }) => {
  const theme = useTheme();
  const location = useLocation();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const [mobileOpen, setMobileOpen] = useState(false);
  const [isGenerating, setIsGenerating] = useState(false);
  const [phtTime, setPhtTime] = useState('');
  const [toast, setToast] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false,
    message: '',
    severity: 'success',
  });

  // Clock in Asia/Manila (PHT)
  useEffect(() => {
    const updateTime = () => {
      try {
        const formatted = new Intl.DateTimeFormat('en-US', {
          timeZone: 'Asia/Manila',
          hour: 'numeric',
          minute: '2-digit',
          second: '2-digit',
          hour12: true,
        }).format(new Date());
        setPhtTime(formatted);
      } catch {
        setPhtTime('PHT');
      }
    };
    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  const handleDrawerToggle = () => {
    setMobileOpen(!mobileOpen);
  };

  const triggerGlobalReport = async () => {
    if (isGenerating) return;
    setIsGenerating(true);
    try {
      const res = await ReportsService.generateReport();
      setToast({
        open: true,
        message: res.message || 'Hourly report snapshot generated successfully!',
        severity: 'success',
      });
    } catch (err: any) {
      setToast({
        open: true,
        message: err?.response?.data?.message || 'Failed to trigger report generation.',
        severity: 'error',
      });
    } finally {
      setIsGenerating(false);
    }
  };

  const navItems = [
    { label: 'Dashboard', path: '/', icon: <DashboardRounded /> },
    {
      label: 'Shopee Breakdown',
      path: '/shopee',
      icon: <ShoppingBagRounded sx={{ color: '#EE4D2D' }} />,
      badge: 'Shopee',
      badgeColor: '#EE4D2D',
    },
    {
      label: 'TikTok Breakdown',
      path: '/tiktok',
      icon: <TrendingUpRounded sx={{ color: '#E11D48' }} />,
      badge: 'TikTok',
      badgeColor: '#E11D48',
    },
    { label: 'Reports', path: '/reports', icon: <AssessmentRounded /> },
    { label: 'Shops', path: '/shops', icon: <StoreRounded /> },
    { label: 'Platforms', path: '/platforms', icon: <LayersRounded /> },
    { label: 'Automation', path: '/automation', icon: <MemoryRounded /> },
  ];

  const drawerContent = (
    <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
      {/* Brand Header */}
      <Box sx={{ p: 2.5, display: 'flex', alignItems: 'center', gap: 1.5 }}>
        <Box
          sx={{
            width: 40,
            height: 40,
            borderRadius: 3,
            bgcolor: 'primary.main',
            color: '#fff',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontWeight: 900,
            fontSize: 18,
            boxShadow: '0 4px 12px rgba(79, 70, 229, 0.3)',
          }}
        >
          K
        </Box>
        <Box>
          <Typography variant="subtitle1" sx={{ fontWeight: 800, lineHeight: 1.2 }}>
            Hourly Reporting
          </Typography>
          <Typography variant="caption" sx={{ color: 'text.secondary', fontWeight: 500 }}>
            React 19 + Laravel API
          </Typography>
        </Box>
      </Box>

      <Divider sx={{ mx: 2 }} />

      {/* Nav List */}
      <List sx={{ px: 1.5, py: 2, flexGrow: 1 }}>
        {navItems.map((item) => {
          const isActive =
            item.path === '/'
              ? location.pathname === '/'
              : location.pathname.startsWith(item.path);

          return (
            <ListItem key={item.label} disablePadding sx={{ mb: 0.75 }}>
              <ListItemButton
                component={Link}
                to={item.path}
                onClick={() => isMobile && setMobileOpen(false)}
                sx={{
                  borderRadius: 2.5,
                  py: 1.2,
                  px: 2,
                  bgcolor: isActive
                    ? item.badgeColor
                      ? `${item.badgeColor}15`
                      : 'primary.main'
                    : 'transparent',
                  color: isActive
                    ? item.badgeColor
                      ? item.badgeColor
                      : '#FFFFFF'
                    : 'text.primary',
                  border: isActive && item.badgeColor ? `1px solid ${item.badgeColor}40` : 'none',
                  '&:hover': {
                    bgcolor: isActive
                      ? item.badgeColor
                        ? `${item.badgeColor}25`
                        : 'primary.dark'
                      : 'action.hover',
                  },
                }}
              >
                <ListItemIcon
                  sx={{
                    minWidth: 38,
                    color: isActive
                      ? item.badgeColor
                        ? item.badgeColor
                        : '#FFFFFF'
                      : 'text.secondary',
                  }}
                >
                  {item.icon}
                </ListItemIcon>
                <ListItemText
                  primary={item.label}
                  primaryTypographyProps={{
                    fontSize: '0.875rem',
                    fontWeight: isActive ? 700 : 500,
                  }}
                />
                {item.badge && (
                  <Chip
                    label={item.badge}
                    size="small"
                    sx={{
                      height: 20,
                      fontSize: '0.65rem',
                      fontWeight: 800,
                      bgcolor: item.badgeColor,
                      color: '#fff',
                    }}
                  />
                )}
              </ListItemButton>
            </ListItem>
          );
        })}
      </List>

      {/* Drawer Footer Status */}
      <Box sx={{ p: 2, m: 1.5, borderRadius: 3, bgcolor: mode === 'dark' ? 'rgba(255,255,255,0.03)' : '#F1F5F9' }}>
        <Typography variant="caption" sx={{ fontWeight: 700, color: 'text.secondary', display: 'block' }}>
          CONNECTED MARKETPLACES
        </Typography>
        <Box sx={{ display: 'flex', gap: 1, mt: 1 }}>
          <Chip label="Shopee" size="small" sx={{ bgcolor: '#EE4D2D', color: '#fff', fontSize: '0.7rem', fontWeight: 700 }} />
          <Chip label="TikTok" size="small" sx={{ bgcolor: '#E11D48', color: '#fff', fontSize: '0.7rem', fontWeight: 700 }} />
        </Box>
      </Box>
    </Box>
  );

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh', bgcolor: 'background.default' }}>
      {/* Prototype Banner */}
      <Box
        sx={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          zIndex: (theme) => theme.zIndex.drawer + 2,
          bgcolor: mode === 'dark' ? '#3B2D08' : '#FEF3C7',
          color: mode === 'dark' ? '#FDE68A' : '#92400E',
          borderBottom: '1px solid',
          borderColor: mode === 'dark' ? '#78350F' : '#FCD34D',
          px: 2,
          py: 0.5,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          fontSize: '0.75rem',
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Chip
            icon={<AutoAwesomeRounded sx={{ fontSize: '14px !important', color: 'inherit !important' }} />}
            label="PROTOTYPE"
            size="small"
            sx={{ height: 20, fontSize: '0.65rem', fontWeight: 900, bgcolor: '#F59E0B', color: '#000' }}
          />
          <Typography variant="caption" sx={{ fontWeight: 600 }}>
            React 19 Frontend + Laravel REST API — Independent Shopee & TikTok Multi-Channel Reporting
          </Typography>
        </Box>
        <Box sx={{ display: { xs: 'none', sm: 'flex' }, alignItems: 'center', gap: 0.5 }}>
          <AccessTimeRounded sx={{ fontSize: 14 }} />
          <Typography variant="caption" sx={{ fontWeight: 600 }}>
            Philippine Standard Time (PHT, UTC+8)
          </Typography>
        </Box>
      </Box>

      {/* Top App Bar */}
      <AppBar
        position="fixed"
        sx={{
          top: 29, // height of prototype banner
          width: { md: `calc(100% - ${DRAWER_WIDTH}px)` },
          ml: { md: `${DRAWER_WIDTH}px` },
          bgcolor: mode === 'dark' ? 'rgba(17, 24, 39, 0.8)' : 'rgba(255, 255, 255, 0.85)',
          backdropFilter: 'blur(12px)',
          borderBottom: '1px solid',
          borderColor: 'divider',
          color: 'text.primary',
          boxShadow: 'none',
          zIndex: (theme) => theme.zIndex.drawer + 1,
        }}
      >
        <Toolbar sx={{ justifyContent: 'space-between' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <IconButton
              color="inherit"
              edge="start"
              onClick={handleDrawerToggle}
              sx={{ mr: 1, display: { md: 'none' } }}
            >
              <MenuRounded />
            </IconButton>

            {/* Live Clock Chip */}
            <Chip
              icon={
                <Box
                  sx={{
                    width: 8,
                    height: 8,
                    borderRadius: '50%',
                    bgcolor: '#10B981',
                    ml: 1,
                    animation: 'pulse 2s infinite',
                  }}
                />
              }
              label={`${phtTime || 'Loading...'} PHT`}
              size="small"
              sx={{
                fontWeight: 700,
                fontFamily: 'monospace',
                fontSize: '0.75rem',
                bgcolor: mode === 'dark' ? 'rgba(255,255,255,0.05)' : '#F1F5F9',
                color: 'text.primary',
                border: '1px solid',
                borderColor: 'divider',
              }}
            />
          </Box>

          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            {/* Theme Toggle */}
            <Tooltip title={`Switch to ${mode === 'light' ? 'Dark' : 'Light'} Mode`}>
              <IconButton onClick={onToggleTheme} color="inherit" size="small">
                {mode === 'dark' ? <Brightness7Rounded /> : <Brightness4Rounded />}
              </IconButton>
            </Tooltip>

            {/* Quick Action Generate */}
            <Button
              variant="contained"
              color="primary"
              size="small"
              onClick={triggerGlobalReport}
              disabled={isGenerating}
              startIcon={isGenerating ? <CircularProgress size={16} color="inherit" /> : <PlayArrowRounded />}
              sx={{ fontSize: '0.8125rem' }}
            >
              {isGenerating ? 'Generating...' : 'Generate Snapshot'}
            </Button>
          </Box>
        </Toolbar>
      </AppBar>

      {/* Navigation Drawer */}
      <Box component="nav" sx={{ width: { md: DRAWER_WIDTH }, flexShrink: { md: 0 } }}>
        {/* Mobile Drawer */}
        <Drawer
          variant="temporary"
          open={mobileOpen}
          onClose={handleDrawerToggle}
          ModalProps={{ keepMounted: true }}
          sx={{
            display: { xs: 'block', md: 'none' },
            '& .MuiDrawer-paper': {
              boxSizing: 'border-box',
              width: DRAWER_WIDTH,
              top: 29,
              height: 'calc(100% - 29px)',
            },
          }}
        >
          {drawerContent}
        </Drawer>

        {/* Desktop Permanent Drawer */}
        <Drawer
          variant="permanent"
          sx={{
            display: { xs: 'none', md: 'block' },
            '& .MuiDrawer-paper': {
              boxSizing: 'border-box',
              width: DRAWER_WIDTH,
              top: 29,
              height: 'calc(100% - 29px)',
              borderRight: '1px solid',
              borderColor: 'divider',
            },
          }}
          open
        >
          {drawerContent}
        </Drawer>
      </Box>

      {/* Main Content Area */}
      <Box
        component="main"
        sx={{
          flexGrow: 1,
          p: { xs: 2, sm: 3, md: 4 },
          width: { md: `calc(100% - ${DRAWER_WIDTH}px)` },
          mt: '93px', // 29px (notice) + 64px (appbar)
          minHeight: 'calc(100vh - 93px)',
          display: 'flex',
          flexDirection: 'column',
        }}
      >
        <Container maxWidth="xl" sx={{ flexGrow: 1, p: '0 !important' }}>
          {children}
        </Container>

        {/* Footer */}
        <Box
          component="footer"
          sx={{
            mt: 6,
            pt: 3,
            borderTop: '1px solid',
            borderColor: 'divider',
            display: 'flex',
            flexDirection: { xs: 'column', sm: 'row' },
            justifyContent: 'space-between',
            alignItems: 'center',
            gap: 2,
            color: 'text.secondary',
            fontSize: '0.75rem',
          }}
        >
          <Typography variant="caption">
            <strong>KCG Automated Reporting System</strong> • React 19 + Material UI v6 • {phtTime} PHT
          </Typography>
          <Typography variant="caption">
            Connected to Laravel API via Axios • Independent Shopee & TikTok Automation
          </Typography>
        </Box>
      </Box>

      {/* Global Alert Notification Toast */}
      <Snackbar
        open={toast.open}
        autoHideDuration={5000}
        onClose={() => setToast({ ...toast, open: false })}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert
          onClose={() => setToast({ ...toast, open: false })}
          severity={toast.severity}
          variant="filled"
          sx={{ width: '100%', borderRadius: 2 }}
        >
          {toast.message}
        </Alert>
      </Snackbar>
    </Box>
  );
};
