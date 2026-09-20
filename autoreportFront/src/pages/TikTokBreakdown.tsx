import React, { useEffect, useState, useMemo } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Card,
  CardContent,
  Button,
  TextField,
  Chip,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  CircularProgress,
  LinearProgress,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  IconButton,
} from '@mui/material';
import {
  CalendarMonthRounded,
  FileDownloadRounded,
  RefreshRounded,
  SendRounded,
  ContentCopyRounded,
  CheckRounded,
  CloseRounded,
  TrendingUpRounded,
  TrackChangesRounded,
  AutoAwesomeRounded,
  SyncRounded,
} from '@mui/icons-material';
import { TikTokService, PlatformReportData, formatPeso, formatNumber } from '../services/api';

export const TikTokBreakdown: React.FC = () => {
  const [data, setData] = useState<PlatformReportData | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState(() => new Date().toLocaleDateString('en-CA'));
  const [targetInput, setTargetInput] = useState<number>(670000);
  const [isSimulating, setIsSimulating] = useState(false);
  const [showDispatch, setShowDispatch] = useState(false);
  const [copied, setCopied] = useState(false);
  const [isSyncing, setIsSyncing] = useState(false);

  const handleSyncApi = async () => {
    setIsSyncing(true);
    try {
      await TikTokService.sync(selectedDate);
      await fetchBreakdown(selectedDate, targetInput);
    } catch (err) {
      console.error('Failed to sync TikTok Shop Open API:', err);
    } finally {
      setIsSyncing(false);
    }
  };

  const fetchBreakdown = async (date?: string, target?: number) => {
    setLoading(true);
    try {
      const queryDate = date || selectedDate;
      const res = await TikTokService.getBreakdown(queryDate, target || targetInput);
      setData(res);
      if (res.report?.meta?.report_date) {
        setSelectedDate(res.report.meta.report_date);
      }
      if (res.report?.meta?.daily_target) {
        setTargetInput(res.report.meta.daily_target);
      }
    } catch (err) {
      console.error('Failed to load TikTok breakdown:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBreakdown();
  }, []);

  const handleDateChange = (newDate: string) => {
    setSelectedDate(newDate);
    fetchBreakdown(newDate, targetInput);
  };

  const handleTargetUpdate = () => {
    fetchBreakdown(selectedDate, targetInput);
  };

  const handleExportExcel = async () => {
    try {
      await TikTokService.exportExcel(selectedDate, targetInput);
    } catch (err) {
      console.error('Failed to export Excel:', err);
    }
  };

  const handleExportCsv = async () => {
    try {
      await TikTokService.exportCsv(selectedDate, targetInput);
    } catch (err) {
      console.error('Failed to export CSV:', err);
    }
  };

  const handleSimulateMidnight = async () => {
    if (isSimulating) return;
    setIsSimulating(true);
    try {
      await TikTokService.simulateMidnight(selectedDate);
      await fetchBreakdown(selectedDate, targetInput);
    } catch (err) {
      console.error('Simulation error:', err);
    } finally {
      setIsSimulating(false);
    }
  };

  const hoursList = useMemo(() => {
    if (!data?.report?.hours) return [];
    return Object.entries(data.report.hours).map(([key, label]) => ({
      key: parseInt(key, 10),
      label,
    }));
  }, [data?.report?.hours]);

  const dispatchMessage = useMemo(() => {
    if (!data?.report) return '';
    const meta = data.report.meta;
    const kpis = data.report.kpis;
    const latestHour = meta.latest_hour || '11:00 PM';

    let msg = `🔥 *TIKTOK HOURLY GMV REPORT* 🔥\n`;
    msg += `📅 Date: ${meta.formatted_date}\n`;
    msg += `⏰ Snapshot as of: ${latestHour} (PHT)\n\n`;

    msg += `📊 *OVERALL TIKTOK PERFORMANCE*\n`;
    msg += `• Total Sales (GMV): ${formatPeso(kpis.total_gmv)}\n`;
    msg += `• TikTok Ad Spend: ${formatPeso(kpis.total_ad_spend)}\n`;
    msg += `• Blended ROAS: ${kpis.blended_roas.toFixed(2)}x\n`;
    msg += `• Total Orders: ${formatNumber(kpis.total_orders)}\n`;
    msg += `• Daily Target: ${formatPeso(kpis.daily_target)} (${kpis.target_progress}% achieved)\n\n`;

    msg += `🏬 *TIKTOK STORE BREAKDOWN:*\n`;
    data.report.shops.forEach((s) => {
      const validHours = Object.keys(s.metrics.sales)
        .map(Number)
        .filter((h) => s.metrics.sales[h] !== null)
        .sort((a, b) => b - a);
      const lastHour = validHours[0] || 23;
      const lastSales = s.metrics.sales[lastHour] || 0;
      const lastSpend = s.metrics.ad_spend[lastHour] || 0;
      const roas = lastSpend > 0 ? (lastSales / lastSpend).toFixed(2) + 'x' : '0.00x';
      msg += `▸ *${s.shop_name}*: ${formatPeso(lastSales)} | Spend: ${formatPeso(lastSpend)} | ROAS: ${roas}\n`;
    });

    msg += `\n_Generated via React 19 + Laravel API Pipeline_`;
    return msg;
  }, [data]);

  const copyToClipboard = () => {
    navigator.clipboard.writeText(dispatchMessage);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  if (loading && !data) {
    return (
      <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', minHeight: 400, gap: 2 }}>
        <CircularProgress sx={{ color: '#E11D48' }} size={44} />
        <Typography variant="body2" color="text.secondary">
          Loading TikTok hourly metrics from API...
        </Typography>
      </Box>
    );
  }

  const report = data?.report;
  const kpis = report?.kpis;

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header with TikTok Branding */}
      <Paper sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', lg: 'row' }, alignItems: { lg: 'center' }, justifyContent: 'space-between', gap: 2 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <Box
            sx={{
              width: 44,
              height: 44,
              borderRadius: 3,
              bgcolor: '#111827',
              color: '#fff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontWeight: 900,
              fontSize: 18,
              boxShadow: '0 4px 14px rgba(17, 24, 39, 0.3)',
            }}
          >
            TT
          </Box>
          <Box>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
              <Typography variant="h5" sx={{ fontWeight: 800 }}>
                TikTok — Hourly Breakdown
              </Typography>
              <Chip label="INDEPENDENT PIPELINE" size="small" sx={{ bgcolor: '#FFF1F2', color: '#E11D48', fontWeight: 800, border: '1px solid #FECDD3' }} />
            </Box>
            <Typography variant="caption" color="text.secondary">
              Hourly GMV, Ad Spend, Orders, and ROAS across 6 official TikTok storefronts
            </Typography>
          </Box>
        </Box>

        {/* Date and Action Buttons */}
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flexWrap: 'wrap' }}>
          <TextField
            type="date"
            size="small"
            value={selectedDate}
            onChange={(e) => handleDateChange(e.target.value)}
            InputProps={{
              startAdornment: <CalendarMonthRounded sx={{ mr: 1, color: '#E11D48', fontSize: 18 }} />,
            }}
            sx={{ width: 170 }}
          />

          <Button
            variant="outlined"
            size="small"
            onClick={handleSyncApi}
            disabled={isSyncing}
            startIcon={isSyncing ? <CircularProgress size={16} color="inherit" /> : <SyncRounded />}
            sx={{ borderColor: '#E11D48', color: '#E11D48', '&:hover': { borderColor: '#BE123C', bgcolor: '#FFF1F2' }, fontWeight: 700 }}
          >
            {isSyncing ? 'Syncing...' : 'Sync TikTok API'}
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={handleExportExcel}
            startIcon={<FileDownloadRounded />}
            sx={{ bgcolor: '#10B981', '&:hover': { bgcolor: '#059669' }, fontWeight: 700 }}
          >
            Export Excel (.xlsx)
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={handleExportCsv}
            sx={{ bgcolor: '#334155', '&:hover': { bgcolor: '#1E293B' }, fontWeight: 700 }}
          >
            CSV
          </Button>

          <Button
            variant="outlined"
            size="small"
            onClick={() => setShowDispatch(true)}
            startIcon={<SendRounded />}
            sx={{ color: '#E11D48', borderColor: '#E11D48', '&:hover': { borderColor: '#BE123C', bgcolor: '#FFF1F2' } }}
          >
            Dispatch Text
          </Button>
        </Box>
      </Paper>

      {/* 2. KPI Cards */}
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #E11D48' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>Total TikTok GMV</Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#E11D48', mt: 0.5 }}>
                {formatPeso(kpis?.total_gmv)}
              </Typography>
              <Typography variant="caption" color="text.secondary">As of {report?.meta.latest_hour || 'end of day'}</Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #F59E0B' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>TikTok Ad Spend</Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#D97706', mt: 0.5 }}>
                {formatPeso(kpis?.total_ad_spend)}
              </Typography>
              <Typography variant="caption" color="text.secondary">Total campaign spend</Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #0EA5E9' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>Blended ROAS</Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#0284C7', mt: 0.5 }}>
                {kpis?.blended_roas?.toFixed(2)}x
              </Typography>
              <Typography variant="caption" color="text.secondary">GMV / Ads Spend</Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #10B981' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>Total Orders</Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#059669', mt: 0.5 }}>
                {formatNumber(kpis?.total_orders)}
              </Typography>
              <Typography variant="caption" color="text.secondary">Across 6 storefronts</Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #E11D48' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>Target Progress</Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#E11D48', mt: 0.5 }}>
                {kpis?.target_progress}%
              </Typography>
              <LinearProgress
                variant="determinate"
                value={Math.min(kpis?.target_progress || 0, 100)}
                sx={{
                  mt: 1,
                  height: 6,
                  borderRadius: 3,
                  bgcolor: 'rgba(225, 29, 72, 0.15)',
                  '& .MuiLinearProgress-bar': { bgcolor: '#E11D48' },
                }}
              />
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* 3. Daily Target Bar & Simulation */}
      <Paper sx={{ p: 2, display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, alignItems: { sm: 'center' }, justifyContent: 'space-between', gap: 2, bgcolor: '#FFF1F2', border: '1px solid #FECDD3' }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
          <TrackChangesRounded sx={{ color: '#E11D48' }} />
          <Typography variant="body2" sx={{ fontWeight: 700, color: '#9F1239' }}>
            TikTok Daily Sales Target:
          </Typography>
          <TextField
            type="number"
            size="small"
            value={targetInput}
            onChange={(e) => setTargetInput(Number(e.target.value))}
            sx={{ width: 140, bgcolor: '#fff', borderRadius: 1 }}
          />
          <Button
            variant="contained"
            size="small"
            onClick={handleTargetUpdate}
            sx={{ bgcolor: '#E11D48', '&:hover': { bgcolor: '#BE123C' } }}
          >
            Update Target
          </Button>
        </Box>

        <Button
          variant="outlined"
          size="small"
          onClick={handleSimulateMidnight}
          disabled={isSimulating}
          startIcon={isSimulating ? <CircularProgress size={16} color="inherit" /> : <AutoAwesomeRounded />}
          sx={{ color: '#E11D48', borderColor: '#E11D48', bgcolor: '#fff' }}
        >
          Simulate 12 AM Push
        </Button>
      </Paper>

      {/* 4. Complete 16-Hour Matrix Table */}
      <Paper sx={{ overflow: 'hidden', border: '1px solid #FECDD3' }}>
        <Box sx={{ bgcolor: '#E11D48', color: '#fff', px: 3, py: 2, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
            TIKTOK — HOURLY BREAKDOWN (GMV per shop by hour)
          </Typography>
          <Typography variant="caption" sx={{ color: '#FFE4E6', fontWeight: 600 }}>
            DATE: {report?.meta.formatted_date} • Standard Business Hours: 9:00 AM to 12:00 AM
          </Typography>
        </Box>

        <TableContainer sx={{ maxHeight: 600 }}>
          <Table stickyHeader size="small" sx={{ minWidth: 1200 }}>
            <TableHead>
              <TableRow>
                <TableCell sx={{ bgcolor: '#E11D48', color: '#fff', fontWeight: 800, minWidth: 180, position: 'sticky', left: 0, zIndex: 30 }}>
                  SHOP
                </TableCell>
                <TableCell sx={{ bgcolor: '#E11D48', color: '#fff', fontWeight: 800, minWidth: 90, textAlign: 'center' }}>
                  METRIC
                </TableCell>
                {hoursList.map((h) => (
                  <TableCell key={h.key} sx={{ bgcolor: '#E11D48', color: '#fff', fontWeight: 800, minWidth: 85, textAlign: 'center' }}>
                    {h.label}
                  </TableCell>
                ))}
              </TableRow>
            </TableHead>
            <TableBody>
              {report?.shops?.map((shop) => (
                <React.Fragment key={shop.shop_id}>
                  {/* Row 1: AD SPEND */}
                  <TableRow hover>
                    <TableCell
                      rowSpan={4}
                      sx={{
                        fontWeight: 800,
                        color: '#E11D48',
                        bgcolor: 'background.paper',
                        position: 'sticky',
                        left: 0,
                        zIndex: 20,
                        borderRight: '1px solid',
                        borderColor: 'divider',
                        verticalAlign: 'middle',
                      }}
                    >
                      <Typography variant="body2" sx={{ fontWeight: 800 }}>
                        {shop.shop_name}
                      </Typography>
                      <Typography variant="caption" color="text.secondary" sx={{ fontFamily: 'monospace' }}>
                        {shop.shop_code}
                      </Typography>
                    </TableCell>
                    <TableCell sx={{ fontWeight: 700, textAlign: 'center', bgcolor: '#FEF3C7', color: '#92400E', fontSize: '0.75rem' }}>
                      AD SPEND
                    </TableCell>
                    {hoursList.map((h) => (
                      <TableCell key={`spend-${h.key}`} sx={{ textAlign: 'center', bgcolor: '#FFFBEB', fontFamily: 'monospace' }}>
                        {shop.metrics.ad_spend[h.key] !== null ? formatNumber(shop.metrics.ad_spend[h.key]!) : ''}
                      </TableCell>
                    ))}
                  </TableRow>

                  {/* Row 2: ORDERS */}
                  <TableRow hover>
                    <TableCell sx={{ fontWeight: 700, textAlign: 'center', bgcolor: '#ECFDF5', color: '#065F46', fontSize: '0.75rem' }}>
                      ORDERS
                    </TableCell>
                    {hoursList.map((h) => (
                      <TableCell key={`orders-${h.key}`} sx={{ textAlign: 'center', bgcolor: '#F0FDF4', fontFamily: 'monospace' }}>
                        {shop.metrics.orders[h.key] !== null ? formatNumber(shop.metrics.orders[h.key]!) : ''}
                      </TableCell>
                    ))}
                  </TableRow>

                  {/* Row 3: SALES */}
                  <TableRow hover>
                    <TableCell sx={{ fontWeight: 700, textAlign: 'center', bgcolor: '#FFF1F2', color: '#9F1239', fontSize: '0.75rem' }}>
                      SALES
                    </TableCell>
                    {hoursList.map((h) => (
                      <TableCell key={`sales-${h.key}`} sx={{ textAlign: 'center', bgcolor: '#FFF1F2', fontFamily: 'monospace', fontWeight: 700 }}>
                        {shop.metrics.sales[h.key] !== null ? formatNumber(shop.metrics.sales[h.key]!) : ''}
                      </TableCell>
                    ))}
                  </TableRow>

                  {/* Row 4: ROAS */}
                  <TableRow hover sx={{ borderBottom: '2px solid #E2E8F0' }}>
                    <TableCell sx={{ fontWeight: 700, textAlign: 'center', bgcolor: '#E0F2FE', color: '#0369A1', fontSize: '0.75rem' }}>
                      ROAS
                    </TableCell>
                    {hoursList.map((h) => (
                      <TableCell key={`roas-${h.key}`} sx={{ textAlign: 'center', bgcolor: '#F0F9FF', fontFamily: 'monospace', fontWeight: 800, color: '#0284C7' }}>
                        {shop.metrics.roas[h.key] !== null ? `${Number(shop.metrics.roas[h.key]).toFixed(2)}x` : ''}
                      </TableCell>
                    ))}
                  </TableRow>
                </React.Fragment>
              ))}

              {/* Summary Section Header */}
              <TableRow sx={{ bgcolor: '#E11D48' }}>
                <TableCell colSpan={hoursList.length + 2} sx={{ color: '#fff', fontWeight: 900, textAlign: 'center', py: 1.2, letterSpacing: '0.05em' }}>
                  TOTAL TIKTOK HOURLY
                </TableCell>
              </TableRow>

              {/* Total Sales Row */}
              <TableRow sx={{ bgcolor: '#BE123C' }}>
                <TableCell sx={{ color: '#fff', fontWeight: 900, position: 'sticky', left: 0, bgcolor: '#BE123C', zIndex: 20 }}>
                  TOTAL TIKTOK
                </TableCell>
                <TableCell sx={{ color: '#fff', fontWeight: 800, textAlign: 'center', fontSize: '0.75rem' }}>
                  TOTAL SALES (₱)
                </TableCell>
                {hoursList.map((h) => (
                  <TableCell key={`total-${h.key}`} sx={{ color: '#fff', fontWeight: 900, textAlign: 'center', fontFamily: 'monospace' }}>
                    {report?.summary.total_sales[h.key] !== null ? formatNumber(report?.summary.total_sales[h.key]!) : ''}
                  </TableCell>
                ))}
              </TableRow>

              {/* Sales Increment Row */}
              <TableRow sx={{ bgcolor: '#F8FAFC' }}>
                <TableCell sx={{ fontWeight: 800, position: 'sticky', left: 0, bgcolor: '#F8FAFC', zIndex: 20 }}>
                  TOTAL TIKTOK
                </TableCell>
                <TableCell sx={{ fontWeight: 700, textAlign: 'center', fontSize: '0.75rem' }}>
                  SALES INCREMENT
                </TableCell>
                {hoursList.map((h) => (
                  <TableCell
                    key={`inc-${h.key}`}
                    sx={{
                      textAlign: 'center',
                      fontFamily: 'monospace',
                      fontWeight: 800,
                      color: (report?.summary.sales_increment[h.key] || 0) > 0 ? '#059669' : '#DC2626',
                    }}
                  >
                    {report?.summary.sales_increment[h.key] !== null ? formatNumber(report?.summary.sales_increment[h.key]!) : '-'}
                  </TableCell>
                ))}
              </TableRow>

              {/* Vs Target Row */}
              <TableRow sx={{ bgcolor: '#FFF1F2' }}>
                <TableCell sx={{ fontWeight: 800, color: '#E11D48', position: 'sticky', left: 0, bgcolor: '#FFF1F2', zIndex: 20 }}>
                  TOTAL TIKTOK
                </TableCell>
                <TableCell sx={{ fontWeight: 800, color: '#E11D48', textAlign: 'center', fontSize: '0.75rem' }}>
                  VS DAILY TARGET
                </TableCell>
                {hoursList.map((h) => (
                  <TableCell key={`vs-${h.key}`} sx={{ textAlign: 'center', fontFamily: 'monospace', fontWeight: 900, color: '#E11D48' }}>
                    {report?.summary.vs_daily_target[h.key] !== null ? `${report?.summary.vs_daily_target[h.key]}%` : '-'}
                  </TableCell>
                ))}
              </TableRow>
            </TableBody>
          </Table>
        </TableContainer>
      </Paper>

      {/* 5. Dispatch Chat Modal */}
      <Dialog open={showDispatch} onClose={() => setShowDispatch(false)} maxWidth="sm" fullWidth>
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <SendRounded sx={{ color: '#E11D48' }} />
            <Typography variant="h6" sx={{ fontWeight: 800 }}>TikTok Dispatch Chat Text</Typography>
          </Box>
          <IconButton onClick={() => setShowDispatch(false)} size="small">
            <CloseRounded />
          </IconButton>
        </DialogTitle>
        <DialogContent dividers>
          <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 2 }}>
            Pre-formatted message ready to paste into Viber, Telegram, or WhatsApp group chats:
          </Typography>
          <TextField
            multiline
            rows={12}
            fullWidth
            value={dispatchMessage}
            InputProps={{ readOnly: true, sx: { fontFamily: 'monospace', fontSize: '0.8rem' } }}
          />
        </DialogContent>
        <DialogActions sx={{ p: 2 }}>
          <Button onClick={() => setShowDispatch(false)}>Close</Button>
          <Button
            variant="contained"
            onClick={copyToClipboard}
            startIcon={copied ? <CheckRounded /> : <ContentCopyRounded />}
            sx={{ bgcolor: '#E11D48', '&:hover': { bgcolor: '#BE123C' } }}
          >
            {copied ? 'Copied to Clipboard!' : 'Copy Text'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};
