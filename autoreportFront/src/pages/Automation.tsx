import React, { useEffect, useState, useMemo } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Card,
  CardContent,
  Chip,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Tabs,
  Tab,
  CircularProgress,
  Alert,
} from '@mui/material';
import {
  PlayArrowRounded,
  MemoryRounded,
  AccessTimeRounded,
  HistoryRounded,
  BoltRounded,
  CheckCircleRounded,
  ErrorRounded,
  SyncRounded,
  ApiRounded,
} from '@mui/icons-material';
import {
  AutomationService,
  AutomationData,
  PlatformsService,
  ConnectorsStatusData,
  ShopeeService,
  TikTokService,
} from '../services/api';

export const Automation: React.FC = () => {
  const [data, setData] = useState<AutomationData | null>(null);
  const [connectors, setConnectors] = useState<ConnectorsStatusData | null>(null);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState<'all' | 'shopee' | 'tiktok'>('all');
  const [runningAction, setRunningAction] = useState<string | null>(null);
  const [notification, setNotification] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

  const fetchAutomation = async () => {
    setLoading(true);
    try {
      const [res, conn] = await Promise.all([
        AutomationService.getAutomation(),
        PlatformsService.getConnectorsStatus(),
      ]);
      setData(res);
      setConnectors(conn);
    } catch (err) {
      console.error('Failed to load automation:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAutomation();
  }, []);

  const handleRunShopee = async () => {
    setRunningAction('shopee');
    setNotification(null);
    try {
      await AutomationService.runShopee();
      setNotification({ type: 'success', message: 'Shopee hourly automation executed successfully!' });
      await fetchAutomation();
    } catch (err: any) {
      setNotification({ type: 'error', message: err.message || 'Failed to run Shopee automation.' });
    } finally {
      setRunningAction(null);
    }
  };

  const handleRunTikTok = async () => {
    setRunningAction('tiktok');
    setNotification(null);
    try {
      await AutomationService.runTikTok();
      setNotification({ type: 'success', message: 'TikTok hourly automation executed successfully!' });
      await fetchAutomation();
    } catch (err: any) {
      setNotification({ type: 'error', message: err.message || 'Failed to run TikTok automation.' });
    } finally {
      setRunningAction(null);
    }
  };

  const handleRunConsolidated = async () => {
    setRunningAction('consolidated');
    setNotification(null);
    try {
      await AutomationService.runConsolidated();
      setNotification({ type: 'success', message: 'Consolidated hourly snapshot executed successfully!' });
      await fetchAutomation();
    } catch (err: any) {
      setNotification({ type: 'error', message: err.message || 'Failed to run consolidated automation.' });
    } finally {
      setRunningAction(null);
    }
  };

  const handleSyncShopeeApi = async () => {
    setRunningAction('sync-shopee');
    setNotification(null);
    try {
      const res = await ShopeeService.sync();
      setNotification({ type: 'success', message: res.message || 'Shopee Open Platform API synced successfully!' });
      await fetchAutomation();
    } catch (err: any) {
      setNotification({ type: 'error', message: err.message || 'Shopee API sync failed.' });
    } finally {
      setRunningAction(null);
    }
  };

  const handleSyncTikTokApi = async () => {
    setRunningAction('sync-tiktok');
    setNotification(null);
    try {
      const res = await TikTokService.sync();
      setNotification({ type: 'success', message: res.message || 'TikTok Shop Open API synced successfully!' });
      await fetchAutomation();
    } catch (err: any) {
      setNotification({ type: 'error', message: err.message || 'TikTok API sync failed.' });
    } finally {
      setRunningAction(null);
    }
  };

  const filteredLogs = useMemo(() => {
    if (!data?.logs) return [];
    if (tab === 'shopee') {
      return data.logs.filter(
        (l) => l.job.toLowerCase().includes('shopee') || l.message.toLowerCase().includes('shopee')
      );
    }
    if (tab === 'tiktok') {
      return data.logs.filter(
        (l) => l.job.toLowerCase().includes('tiktok') || l.message.toLowerCase().includes('tiktok')
      );
    }
    return data.logs;
  }, [data?.logs, tab]);

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header Banner */}
      <Paper sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', lg: 'row' }, alignItems: { lg: 'center' }, justifyContent: 'space-between', gap: 2 }}>
        <Box>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            <Typography variant="h5" sx={{ fontWeight: 800 }}>
              Automation Engine & Background Scheduler
            </Typography>
            <Chip label="INDEPENDENT SCHEDULERS ACTIVE" size="small" color="success" sx={{ fontWeight: 800, height: 22 }} />
          </Box>
          <Typography variant="caption" color="text.secondary">
            Independent background reporting daemons for Shopee, TikTok Shop, and Unified Reporting
          </Typography>
        </Box>

        {/* Independent Trigger Buttons */}
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flexWrap: 'wrap' }}>
          <Button
            variant="contained"
            size="small"
            onClick={handleRunShopee}
            disabled={runningAction !== null}
            startIcon={runningAction === 'shopee' ? <CircularProgress size={16} color="inherit" /> : <BoltRounded />}
            sx={{ bgcolor: '#EE4D2D', '&:hover': { bgcolor: '#C7381B' }, fontWeight: 700 }}
          >
            Run Shopee Pipeline
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={handleRunTikTok}
            disabled={runningAction !== null}
            startIcon={runningAction === 'tiktok' ? <CircularProgress size={16} color="inherit" /> : <BoltRounded />}
            sx={{ bgcolor: '#E11D48', '&:hover': { bgcolor: '#BE123C' }, fontWeight: 700 }}
          >
            Run TikTok Pipeline
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={handleRunConsolidated}
            disabled={runningAction !== null}
            startIcon={runningAction === 'consolidated' ? <CircularProgress size={16} color="inherit" /> : <PlayArrowRounded />}
            sx={{ bgcolor: '#4F46E5', '&:hover': { bgcolor: '#4338CA' }, fontWeight: 700 }}
          >
            Run Consolidated
          </Button>
        </Box>
      </Paper>

      {/* Notification Toast Alert */}
      {notification && (
        <Alert severity={notification.type} onClose={() => setNotification(null)} sx={{ fontWeight: 600 }}>
          {notification.message}
        </Alert>
      )}

      {/* 2. Open API Live Synchronizers Panel */}
      <Paper sx={{ p: 2.5 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            <ApiRounded color="primary" />
            <Box>
              <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
                Live Open Platform API Synchronizers
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Pre-configured for official Shopee Open Platform V2 and TikTok Shop Open API endpoints
              </Typography>
            </Box>
          </Box>
          <Chip label="REAL API READY" size="small" color="primary" sx={{ fontWeight: 800 }} />
        </Box>

        <Grid container spacing={2}>
          {/* Shopee Open Platform Card */}
          <Grid item xs={12} md={6}>
            <Card sx={{ bgcolor: 'background.default', border: '1px solid', borderColor: 'divider' }}>
              <CardContent sx={{ p: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1.5 }}>
                  <Typography variant="subtitle2" sx={{ fontWeight: 800, color: '#EE4D2D' }}>
                    Shopee Open Platform API v2.0
                  </Typography>
                  <Chip
                    label={connectors?.shopee.mode.toUpperCase() || 'SANDBOX'}
                    size="small"
                    sx={{ height: 20, fontSize: '0.65rem', fontWeight: 800, bgcolor: '#EE4D2D15', color: '#EE4D2D' }}
                  />
                </Box>
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 1, fontFamily: 'monospace' }}>
                  Base: {connectors?.shopee.base_url || 'https://partner.shopeemobile.com'}
                </Typography>
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 2, fontFamily: 'monospace' }}>
                  Auth: {connectors?.shopee.auth_type}
                </Typography>
                <Button
                  variant="outlined"
                  size="small"
                  fullWidth
                  onClick={handleSyncShopeeApi}
                  disabled={runningAction !== null}
                  startIcon={runningAction === 'sync-shopee' ? <CircularProgress size={16} color="inherit" /> : <SyncRounded />}
                  sx={{ borderColor: '#EE4D2D', color: '#EE4D2D', fontWeight: 700 }}
                >
                  {runningAction === 'sync-shopee' ? 'Syncing...' : 'Sync Shopee API Now'}
                </Button>
              </CardContent>
            </Card>
          </Grid>

          {/* TikTok Shop Open API Card */}
          <Grid item xs={12} md={6}>
            <Card sx={{ bgcolor: 'background.default', border: '1px solid', borderColor: 'divider' }}>
              <CardContent sx={{ p: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1.5 }}>
                  <Typography variant="subtitle2" sx={{ fontWeight: 800, color: '#E11D48' }}>
                    TikTok Shop Partner Open API v202309
                  </Typography>
                  <Chip
                    label={connectors?.tiktok.mode.toUpperCase() || 'SANDBOX'}
                    size="small"
                    sx={{ height: 20, fontSize: '0.65rem', fontWeight: 800, bgcolor: '#E11D4815', color: '#E11D48' }}
                  />
                </Box>
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 1, fontFamily: 'monospace' }}>
                  Base: {connectors?.tiktok.base_url || 'https://open-api.tiktokglobalshop.com'}
                </Typography>
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 2, fontFamily: 'monospace' }}>
                  Auth: {connectors?.tiktok.auth_type}
                </Typography>
                <Button
                  variant="outlined"
                  size="small"
                  fullWidth
                  onClick={handleSyncTikTokApi}
                  disabled={runningAction !== null}
                  startIcon={runningAction === 'sync-tiktok' ? <CircularProgress size={16} color="inherit" /> : <SyncRounded />}
                  sx={{ borderColor: '#E11D48', color: '#E11D48', fontWeight: 700 }}
                >
                  {runningAction === 'sync-tiktok' ? 'Syncing...' : 'Sync TikTok API Now'}
                </Button>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </Paper>

      {/* 3. Cron Schedules & Status Cards */}
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent sx={{ p: 2.5, display: 'flex', alignItems: 'center', gap: 2 }}>
              <Box sx={{ width: 44, height: 44, borderRadius: 2.5, bgcolor: 'primary.main', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <MemoryRounded />
              </Box>
              <Box>
                <Typography variant="caption" color="text.secondary">Scheduler Engine</Typography>
                <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
                  {data?.status || 'Active'}
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent sx={{ p: 2.5, display: 'flex', alignItems: 'center', gap: 2 }}>
              <Box sx={{ width: 44, height: 44, borderRadius: 2.5, bgcolor: '#10B981', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <AccessTimeRounded />
              </Box>
              <Box>
                <Typography variant="caption" color="text.secondary">Cron Frequency</Typography>
                <Typography variant="subtitle1" sx={{ fontWeight: 800, fontFamily: 'monospace' }}>
                  {data?.cronExpression || '0 * * * *'}
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent sx={{ p: 2.5, display: 'flex', alignItems: 'center', gap: 2 }}>
              <Box sx={{ width: 44, height: 44, borderRadius: 2.5, bgcolor: '#F59E0B', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <HistoryRounded />
              </Box>
              <Box>
                <Typography variant="caption" color="text.secondary">Next Automated Run</Typography>
                <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
                  {data?.nextRun || 'Top of Hour'}
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent sx={{ p: 2.5, display: 'flex', alignItems: 'center', gap: 2 }}>
              <Box sx={{ width: 44, height: 44, borderRadius: 2.5, bgcolor: '#8B5CF6', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <CheckCircleRounded />
              </Box>
              <Box>
                <Typography variant="caption" color="text.secondary">Snapshots Recorded</Typography>
                <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
                  {data?.totalReportsCount || 0} Reports
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* 4. Execution Audit Log Trail */}
      <Paper sx={{ p: 2.5 }}>
        <Box sx={{ display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, alignItems: { sm: 'center' }, justifyContent: 'space-between', gap: 2, mb: 2 }}>
          <Box>
            <Typography variant="h6" sx={{ fontWeight: 800 }}>
              Execution Audit Log Trail
            </Typography>
            <Typography variant="caption" color="text.secondary">
              Real-time audit log of automated background tasks, Open API syncs, and manual trigger events
            </Typography>
          </Box>

          <Tabs
            value={tab}
            onChange={(_, val) => setTab(val)}
            sx={{ minHeight: 36, bgcolor: 'background.default', borderRadius: 2, p: 0.5 }}
          >
            <Tab value="all" label={`All Logs (${data?.logs?.length || 0})`} sx={{ minHeight: 32, fontSize: '0.75rem', fontWeight: 700 }} />
            <Tab value="shopee" label="Shopee Logs" sx={{ minHeight: 32, fontSize: '0.75rem', fontWeight: 700, '&.Mui-selected': { color: '#EE4D2D' } }} />
            <Tab value="tiktok" label="TikTok Logs" sx={{ minHeight: 32, fontSize: '0.75rem', fontWeight: 700, '&.Mui-selected': { color: '#E11D48' } }} />
          </Tabs>
        </Box>

        <TableContainer sx={{ minHeight: 400 }}>
          {loading ? (
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', p: 8 }}>
              <CircularProgress size={36} />
            </Box>
          ) : (
            <Table size="small">
              <TableHead sx={{ bgcolor: 'background.default' }}>
                <TableRow>
                  <TableCell>Event ID</TableCell>
                  <TableCell>Timestamp (PHT)</TableCell>
                  <TableCell>Job Name</TableCell>
                  <TableCell align="center">Status</TableCell>
                  <TableCell>Execution Message</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {filteredLogs.map((log) => {
                  const isShopee = log.job.includes('Shopee');
                  const isTikTok = log.job.includes('TikTok');

                  return (
                    <TableRow key={log.id} hover>
                      <TableCell sx={{ fontFamily: 'monospace', color: 'text.secondary' }}>#{log.id}</TableCell>
                      <TableCell sx={{ whiteSpace: 'nowrap', fontWeight: 600 }}>
                        {new Date(log.created_at).toLocaleString()}
                      </TableCell>
                      <TableCell sx={{ fontWeight: 700, color: isShopee ? '#EE4D2D' : (isTikTok ? '#E11D48' : 'primary.main') }}>
                        {log.job}
                      </TableCell>
                      <TableCell align="center">
                        <Chip
                          icon={log.status === 'SUCCESS' ? <CheckCircleRounded fontSize="small" /> : <ErrorRounded fontSize="small" />}
                          label={log.status}
                          size="small"
                          color={log.status === 'SUCCESS' ? 'success' : 'error'}
                          sx={{ height: 22, fontWeight: 800, fontSize: '0.65rem' }}
                        />
                      </TableCell>
                      <TableCell>{log.message}</TableCell>
                    </TableRow>
                  );
                })}
                {filteredLogs.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={5} sx={{ textAlign: 'center', py: 6, color: 'text.secondary' }}>
                      No execution logs found for selected category.
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          )}
        </TableContainer>
      </Paper>
    </Box>
  );
};
