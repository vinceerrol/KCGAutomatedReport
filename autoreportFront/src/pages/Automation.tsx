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
} from '@mui/material';
import {
  PlayArrowRounded,
  MemoryRounded,
  AccessTimeRounded,
  HistoryRounded,
  BoltRounded,
  CheckCircleRounded,
  ErrorRounded,
} from '@mui/icons-material';
import { AutomationService, AutomationData } from '../services/api';

export const Automation: React.FC = () => {
  const [data, setData] = useState<AutomationData | null>(null);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState<'all' | 'shopee' | 'tiktok'>('all');
  const [runningAction, setRunningAction] = useState<string | null>(null);

  const fetchAutomation = async () => {
    setLoading(true);
    try {
      const res = await AutomationService.getAutomation();
      setData(res);
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
    try {
      await AutomationService.runShopee();
      await fetchAutomation();
    } catch (err) {
      console.error(err);
    } finally {
      setRunningAction(null);
    }
  };

  const handleRunTikTok = async () => {
    setRunningAction('tiktok');
    try {
      await AutomationService.runTikTok();
      await fetchAutomation();
    } catch (err) {
      console.error(err);
    } finally {
      setRunningAction(null);
    }
  };

  const handleRunConsolidated = async () => {
    setRunningAction('consolidated');
    try {
      await AutomationService.runConsolidated();
      await fetchAutomation();
    } catch (err) {
      console.error(err);
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
            startIcon={runningAction === 'shopee' ? <CircularProgress size={16} color="inherit" /> : <PlayArrowRounded />}
            sx={{ bgcolor: '#EE4D2D', '&:hover': { bgcolor: '#C7381B' }, fontWeight: 700 }}
          >
            {runningAction === 'shopee' ? 'Running...' : 'Run Shopee'}
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={handleRunTikTok}
            disabled={runningAction !== null}
            startIcon={runningAction === 'tiktok' ? <CircularProgress size={16} color="inherit" /> : <PlayArrowRounded />}
            sx={{ bgcolor: '#E11D48', '&:hover': { bgcolor: '#BE123C' }, fontWeight: 700 }}
          >
            {runningAction === 'tiktok' ? 'Running...' : 'Run TikTok'}
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={handleRunConsolidated}
            disabled={runningAction !== null}
            startIcon={runningAction === 'consolidated' ? <CircularProgress size={16} color="inherit" /> : <PlayArrowRounded />}
            sx={{ bgcolor: '#1E293B', '&:hover': { bgcolor: '#0F172A' }, fontWeight: 700 }}
          >
            {runningAction === 'consolidated' ? 'Running...' : 'Run Consolidated'}
          </Button>
        </Box>
      </Paper>

      {/* 2. Status Cards Grid */}
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6} md={3}>
          <Paper sx={{ p: 2.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'text.secondary', mb: 1 }}>
              <MemoryRounded fontSize="small" />
              <Typography variant="caption" sx={{ fontWeight: 700 }}>ENGINE STATUS</Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <Box sx={{ width: 10, height: 10, borderRadius: '50%', bgcolor: '#10B981', animation: 'pulse 2s infinite' }} />
              <Typography variant="h6" sx={{ fontWeight: 800 }}>{data?.status || 'Active'}</Typography>
            </Box>
            <Typography variant="caption" color="text.secondary">Background daemon active</Typography>
          </Paper>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Paper sx={{ p: 2.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'text.secondary', mb: 1 }}>
              <AccessTimeRounded fontSize="small" />
              <Typography variant="caption" sx={{ fontWeight: 700 }}>CRON SCHEDULE</Typography>
            </Box>
            <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>Hourly On The Hour</Typography>
            <Typography variant="caption" sx={{ fontFamily: 'monospace', bgcolor: 'primary.light', color: 'primary.main', px: 1, py: 0.25, borderRadius: 1 }}>
              {data?.cronExpression || '0 * * * *'}
            </Typography>
          </Paper>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Paper sx={{ p: 2.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'text.secondary', mb: 1 }}>
              <HistoryRounded fontSize="small" />
              <Typography variant="caption" sx={{ fontWeight: 700 }}>LAST SNAPSHOT RUN</Typography>
            </Box>
            <Typography variant="subtitle2" sx={{ fontWeight: 800 }}>{data?.lastRun || 'N/A'}</Typography>
            <Typography variant="caption" color="text.secondary">Successfully logged</Typography>
          </Paper>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Paper sx={{ p: 2.5 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'text.secondary', mb: 1 }}>
              <BoltRounded fontSize="small" sx={{ color: 'primary.main' }} />
              <Typography variant="caption" sx={{ fontWeight: 700 }}>NEXT SCHEDULED</Typography>
            </Box>
            <Typography variant="subtitle2" sx={{ fontWeight: 800, color: 'primary.main' }}>{data?.nextRun || 'N/A'}</Typography>
            <Typography variant="caption" color="text.secondary">Automatic cron trigger</Typography>
          </Paper>
        </Grid>
      </Grid>

      {/* 3. Execution Audit Logs Table */}
      <Paper sx={{ overflow: 'hidden' }}>
        <Box sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, alignItems: { sm: 'center' }, justifyContent: 'space-between', gap: 2, borderBottom: '1px solid', borderColor: 'divider' }}>
          <Box>
            <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
              Execution Audit Log Trail
            </Typography>
            <Typography variant="caption" color="text.secondary">
              Real-time audit log of automated background tasks and manual trigger events
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
