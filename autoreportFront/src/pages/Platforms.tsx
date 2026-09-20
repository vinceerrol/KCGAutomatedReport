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
} from '@mui/material';
import {
  LayersRounded,
  CheckCircleRounded,
  StoreRounded,
  LaunchRounded,
} from '@mui/icons-material';
import { Link } from 'react-router-dom';
import { PlatformsService, formatPeso, formatNumber } from '../services/api';

export const Platforms: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchPlatforms = async () => {
      setLoading(true);
      try {
        const res = await PlatformsService.getPlatforms();
        setData(res);
      } catch (err) {
        console.error('Failed to load platforms:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchPlatforms();
  }, []);

  const platformsList = data?.platforms || [];

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header Banner */}
      <Paper sx={{ p: 2.5 }}>
        <Typography variant="h5" sx={{ fontWeight: 800 }}>
          Marketplace Platform Connectors
        </Typography>
        <Typography variant="caption" color="text.secondary">
          Central multi-channel ingestion pipelines for Shopee Open Platform and TikTok Shop Partner APIs
        </Typography>
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

            return (
              <Grid item xs={12} md={6} key={p.platform_id}>
                <Card sx={{ borderTop: `4px solid ${themeColor}`, height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                  <CardContent sx={{ p: 3 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2.5 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                        <Box
                          sx={{
                            width: 48,
                            height: 48,
                            borderRadius: 3,
                            bgcolor: isShopee ? '#EE4D2D' : '#111827',
                            color: '#fff',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            fontWeight: 900,
                            fontSize: 20,
                            boxShadow: `0 4px 14px ${themeColor}40`,
                          }}
                        >
                          {isShopee ? 'S' : 'TT'}
                        </Box>
                        <Box>
                          <Typography variant="h6" sx={{ fontWeight: 800 }}>
                            {p.name}
                          </Typography>
                          <Typography variant="caption" color="text.secondary" sx={{ fontFamily: 'monospace' }}>
                            Connector Code: {p.code}
                          </Typography>
                        </Box>
                      </Box>
                      <Chip label="ACTIVE" size="small" color="success" sx={{ fontWeight: 800, height: 22 }} />
                    </Box>

                    {/* Metrics Grid */}
                    <Grid container spacing={2} sx={{ mb: 3 }}>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 2, bgcolor: isShopee ? 'rgba(238, 77, 45, 0.05)' : 'rgba(225, 29, 72, 0.05)' }}>
                          <Typography variant="caption" color="text.secondary">Connected Stores</Typography>
                          <Typography variant="h6" sx={{ fontWeight: 900, mt: 0.5 }}>
                            {p.shops_count} Storefronts
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 2, bgcolor: isShopee ? 'rgba(238, 77, 45, 0.05)' : 'rgba(225, 29, 72, 0.05)' }}>
                          <Typography variant="caption" color="text.secondary">Today's Net Sales</Typography>
                          <Typography variant="h6" sx={{ fontWeight: 900, color: themeColor, mt: 0.5 }}>
                            {formatPeso(p.net_sales)}
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 2, bgcolor: 'background.default' }}>
                          <Typography variant="caption" color="text.secondary">Total Orders</Typography>
                          <Typography variant="subtitle1" sx={{ fontWeight: 800, mt: 0.5 }}>
                            {formatNumber(p.orders)}
                          </Typography>
                        </Paper>
                      </Grid>
                      <Grid item xs={6}>
                        <Paper sx={{ p: 2, bgcolor: 'background.default' }}>
                          <Typography variant="caption" color="text.secondary">Units Sold</Typography>
                          <Typography variant="subtitle1" sx={{ fontWeight: 800, mt: 0.5 }}>
                            {formatNumber(p.units_sold)}
                          </Typography>
                        </Paper>
                      </Grid>
                    </Grid>

                    {/* Capabilities Checkmarks */}
                    <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap', mb: 3, pt: 2, borderTop: '1px solid', borderColor: 'divider' }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, color: '#059669', fontSize: '0.75rem', fontWeight: 600 }}>
                        <CheckCircleRounded fontSize="small" /> Hourly Batch Pulls
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, color: '#059669', fontSize: '0.75rem', fontWeight: 600 }}>
                        <CheckCircleRounded fontSize="small" /> Voucher Reconciliation
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, color: '#059669', fontSize: '0.75rem', fontWeight: 600 }}>
                        <CheckCircleRounded fontSize="small" /> Refund Tracking
                      </Box>
                    </Box>

                    {/* Action Button */}
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
                        py: 1.2,
                      }}
                    >
                      Open {p.name} Hourly Breakdown
                    </Button>
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
