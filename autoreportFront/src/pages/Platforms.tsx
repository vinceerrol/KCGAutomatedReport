import React, { useEffect, useState } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Card,
  CardContent,
  Chip,
  Button,
  CircularProgress,
  Divider,
} from '@mui/material';
import {
  LayersRounded,
  CheckCircleRounded,
  StoreRounded,
  LaunchRounded,
  SyncRounded,
  KeyRounded,
  ApiRounded,
} from '@mui/icons-material';
import { Link } from 'react-router-dom';
import {
  PlatformsService,
  ConnectorsStatusData,
  ShopeeService,
  TikTokService,
  formatPeso,
  formatNumber,
} from '../services/api';

export const Platforms: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [connectors, setConnectors] = useState<ConnectorsStatusData | null>(null);
  const [loading, setLoading] = useState(true);
  const [syncingPlatform, setSyncingPlatform] = useState<string | null>(null);
  const [syncMessage, setSyncMessage] = useState<string | null>(null);

  const fetchPlatforms = async () => {
    setLoading(true);
    try {
      const [res, conn] = await Promise.all([
        PlatformsService.getPlatforms(),
        PlatformsService.getConnectorsStatus(),
      ]);
      setData(res);
      setConnectors(conn);
    } catch (err) {
      console.error('Failed to load platforms:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPlatforms();
  }, []);

  const handleSync = async (code: string) => {
    setSyncingPlatform(code);
    setSyncMessage(null);
    try {
      if (code === 'shopee') {
        const res = await ShopeeService.sync();
        setSyncMessage(`Shopee: ${res.message || 'Orders synchronized successfully!'}`);
      } else {
        const res = await TikTokService.sync();
        setSyncMessage(`TikTok: ${res.message || 'Orders synchronized successfully!'}`);
      }
      await fetchPlatforms();
    } catch (err: any) {
      setSyncMessage(`Sync error: ${err.message || 'Failed to sync API'}`);
    } finally {
      setSyncingPlatform(null);
    }
  };

  const platformsList = data?.platforms || [];

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header Banner */}
      <Paper sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', md: 'row' }, alignItems: { md: 'center' }, justifyContent: 'space-between', gap: 2 }}>
        <Box>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            <Typography variant="h5" sx={{ fontWeight: 800 }}>
              Marketplace Open API Connectors
            </Typography>
            <Chip
              label="OPEN API READY"
              size="small"
              color="success"
              sx={{ fontWeight: 800, height: 22 }}
            />
          </Box>
          <Typography variant="caption" color="text.secondary">
            Centralized multi-channel ingestion pipelines ready for official Shopee Open Platform and TikTok Shop Partner APIs
          </Typography>
        </Box>

        {syncMessage && (
          <Chip
            label={syncMessage}
            color="primary"
            variant="outlined"
            onDelete={() => setSyncMessage(null)}
            sx={{ fontWeight: 600 }}
          />
        )}
      </Paper>

      {/* 2. Platform Cards Grid */}
      {loading ? (
        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', p: 8 }}>
          <CircularProgress size={36} />
        </Box>
      ) : (
        <Grid container spacing={3}>
          {platformsList.map((p: any) => {
            const isShopee = p.code === 'shopee';
            const themeColor = isShopee ? '#EE4D2D' : '#E11D48';
            const connInfo = isShopee ? connectors?.shopee : connectors?.tiktok;

            return (
              <Grid item xs={12} md={6} key={p.platform_id}>
                <Card sx={{ borderTop: `4px solid ${themeColor}`, height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                  <CardContent sx={{ p: 3 }}>
                    {/* Platform Header */}
                    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2.5 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                        <Box
                          sx={{
                            width: 44,
                            height: 44,
                            borderRadius: 2.5,
                            bgcolor: themeColor,
                            color: '#fff',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            fontWeight: 900,
                            fontSize: 18,
                            boxShadow: `0 4px 14px ${themeColor}40`,
                          }}
                        >
                          {isShopee ? 'S' : 'TT'}
                        </Box>
                        <Box>
                          <Typography variant="h6" sx={{ fontWeight: 800 }}>
                            {p.name}
                          </Typography>
                          <Typography variant="caption" color="text.secondary">
                            {connInfo?.platform || (isShopee ? 'Shopee Open Platform' : 'TikTok Shop Open API')}
                          </Typography>
                        </Box>
                      </Box>

                      <Chip
                        label={connInfo?.mode?.toUpperCase() || 'SANDBOX'}
                        size="small"
                        sx={{
                          fontWeight: 800,
                          fontSize: '0.7rem',
                          bgcolor: `${themeColor}15`,
                          color: themeColor,
                          border: `1px solid ${themeColor}40`,
                        }}
                      />
                    </Box>

                    {/* Performance Metrics */}
                    <Grid container spacing={1.5} sx={{ mb: 2.5 }}>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 1.5, bgcolor: 'background.default' }}>
                          <Typography variant="caption" color="text.secondary">Gross GMV</Typography>
                          <Typography variant="h6" sx={{ fontWeight: 800, color: themeColor }}>
                            {formatPeso(p.gross_sales)}
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 1.5, bgcolor: 'background.default' }}>
                          <Typography variant="caption" color="text.secondary">Net Sales</Typography>
                          <Typography variant="h6" sx={{ fontWeight: 800 }}>
                            {formatPeso(p.net_sales)}
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 1.5, bgcolor: 'background.default' }}>
                          <Typography variant="caption" color="text.secondary">Total Orders</Typography>
                          <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
                            {formatNumber(p.orders)}
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 1.5, bgcolor: 'background.default' }}>
                          <Typography variant="caption" color="text.secondary">Units Sold</Typography>
                          <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
                            {formatNumber(p.units_sold)}
                          </Typography>
                        </Paper>
                      </Grid>
                    </Grid>

                    {/* Open API Technical Specifications */}
                    <Box sx={{ mb: 2.5, p: 2, bgcolor: 'background.default', borderRadius: 2 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 1.5 }}>
                        <ApiRounded fontSize="small" sx={{ color: themeColor }} />
                        <Typography variant="caption" sx={{ fontWeight: 800, textTransform: 'uppercase', letterSpacing: 0.5 }}>
                          Open API Technical Interface
                        </Typography>
                      </Box>
                      <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 0.5, fontFamily: 'monospace' }}>
                        <strong>Auth:</strong> {connInfo?.auth_type}
                      </Typography>
                      <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 0.5, fontFamily: 'monospace' }}>
                        <strong>Orders:</strong> {connInfo?.base_url}{connInfo?.orders_endpoint}
                      </Typography>
                      <Typography variant="caption" color="text.secondary" sx={{ display: 'block', fontFamily: 'monospace' }}>
                        <strong>Active Stores:</strong> {connInfo?.shops_count || p.shops_count || 0} stores linked
                      </Typography>
                    </Box>

                    {/* Capabilities Checkmarks */}
                    <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap', mb: 3, pt: 1, borderTop: '1px solid', borderColor: 'divider' }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, color: '#059669', fontSize: '0.75rem', fontWeight: 600 }}>
                        <CheckCircleRounded fontSize="small" /> Hourly Batch Pulls
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, color: '#059669', fontSize: '0.75rem', fontWeight: 600 }}>
                        <CheckCircleRounded fontSize="small" /> HMAC-SHA256 Signatures
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, color: '#059669', fontSize: '0.75rem', fontWeight: 600 }}>
                        <CheckCircleRounded fontSize="small" /> Auto Token Refresh
                      </Box>
                    </Box>

                    {/* Action Buttons */}
                    <Box sx={{ display: 'flex', gap: 1.5 }}>
                      <Button
                        variant="outlined"
                        fullWidth
                        onClick={() => handleSync(p.code)}
                        disabled={syncingPlatform === p.code}
                        startIcon={syncingPlatform === p.code ? <CircularProgress size={16} color="inherit" /> : <SyncRounded />}
                        sx={{
                          borderColor: themeColor,
                          color: themeColor,
                          '&:hover': { borderColor: themeColor, bgcolor: `${themeColor}10` },
                          fontWeight: 700,
                        }}
                      >
                        {syncingPlatform === p.code ? 'Syncing...' : 'Sync Live Orders'}
                      </Button>

                      <Button
                        component={Link}
                        to={isShopee ? '/shopee' : '/tiktok'}
                        variant="contained"
                        fullWidth
                        endIcon={<LaunchRounded />}
                        sx={{
                          bgcolor: themeColor,
                          '&:hover': { bgcolor: isShopee ? '#C7381B' : '#BE123C' },
                          fontWeight: 700,
                        }}
                      >
                        View Matrix
                      </Button>
                    </Box>
                  </CardContent>
                </Card>
              </Grid>
            );
          })}
        </Grid>
      )}
    </Box>
  );
};
