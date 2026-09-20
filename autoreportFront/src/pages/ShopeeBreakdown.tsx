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
  Tooltip,
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
  ShoppingBagRounded,
  TrackChangesRounded,
  AttachMoneyRounded,
  AutoAwesomeRounded,
} from '@mui/icons-material';
import { ShopeeService, PlatformReportData, formatPeso, formatNumber } from '../services/api';

export const ShopeeBreakdown: React.FC = () => {
  const [data, setData] = useState<PlatformReportData | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState('2026-09-19');
  const [targetInput, setTargetInput] = useState<number>(500000);
  const [isSimulating, setIsSimulating] = useState(false);
  const [showDispatch, setShowDispatch] = useState(false);
  const [copied, setCopied] = useState(false);

  const fetchBreakdown = async (date?: string, target?: number) => {
    setLoading(true);
    try {
      const res = await ShopeeService.getBreakdown(date || selectedDate, target || targetInput);
      setData(res);
      if (res.report?.meta?.daily_target) {
        setTargetInput(res.report.meta.daily_target);
      }
    } catch (err) {
      console.error('Failed to load Shopee breakdown:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBreakdown('2026-09-19', 500000);
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
      await ShopeeService.exportExcel(selectedDate, targetInput);
    } catch (err) {
      console.error('Failed to export Excel:', err);
    }
  };

  const handleExportCsv = async () => {
    try {
      await ShopeeService.exportCsv(selectedDate, targetInput);
    } catch (err) {
      console.error('Failed to export CSV:', err);
    }
  };

  const handleSimulateMidnight = async () => {
    if (isSimulating) return;
    setIsSimulating(true);
    try {
      await ShopeeService.simulateMidnight(selectedDate);
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

  // Formatted chat message generator
  const dispatchMessage = useMemo(() => {
    if (!data?.report) return '';
    const meta = data.report.meta;
    const kpis = data.report.kpis;
    const latestHour = meta.latest_hour || '11:00 PM';

    let msg = `🟠 *SHOPEE HOURLY GMV REPORT* 🟠\n`;
    msg += `📅 Date: ${meta.formatted_date}\n`;
    msg += `⏰ Snapshot as of: ${latestHour} (PHT)\n\n`;

    msg += `📊 *OVERALL SHOPEE PERFORMANCE*\n`;
    msg += `• Total Sales (GMV): ${formatPeso(kpis.total_gmv)}\n`;
    msg += `• Shopee Ads Spend: ${formatPeso(kpis.total_ad_spend)}\n`;
    msg += `• Blended ROAS: ${kpis.blended_roas.toFixed(2)}x\n`;
    msg += `• Total Orders: ${formatNumber(kpis.total_orders)}\n`;
    msg += `• Daily Target: ${formatPeso(kpis.daily_target)} (${kpis.target_progress}% achieved)\n\n`;

    msg += `🏬 *SHOPEE STORE BREAKDOWN:*\n`;
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
        <CircularProgress sx={{ color: '#EE4D2D' }} size={44} />
        <Typography variant="body2" color="text.secondary">
          Loading Shopee hourly metrics from API...
        </Typography>
      </Box>
    );
  }

  const report = data?.report;
  const kpis = report?.kpis;

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header with Shopee Branding */}
      <Paper sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', lg: 'row' }, alignItems: { lg: 'center' }, justifyContent: 'space-between', gap: 2 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <Box
            sx={{
              width: 44,
              height: 44,
              borderRadius: 3,
              bgcolor: '#EE4D2D',
              color: '#fff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontWeight: 900,
              fontSize: 20,
              boxShadow: '0 4px 14px rgba(238, 77, 45, 0.3)',
            }}
          >
            S
          </Box>
          <Box>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
              <Typography variant="h5" sx={{ fontWeight: 800 }}>
                Shopee — Hourly Breakdown
              </Typography>
              <Chip label="INDEPENDENT PIPELINE" size="small" sx={{ bgcolor: '#FFF0EB', color: '#EE4D2D', fontWeight: 800, border: '1px solid #FFCCBA' }} />
            </Box>
            <Typography variant="caption" color="text.secondary">
              Hourly GMV, Shopee Ads Spend, Orders, and ROAS across 4 official Shopee storefronts
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
              startAdornment: <CalendarMonthRounded sx={{ mr: 1, color: '#EE4D2D', fontSize: 18 }} />,
            }}
            sx={{ width: 170 }}
          />

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
            sx={{ color: '#EE4D2D', borderColor: '#EE4D2D', '&:hover': { borderColor: '#C7381B', bgcolor: '#FFF0EB' } }}
          >
            Dispatch Text
          </Button>
        </Box>
      </Paper>

      {/* 2. KPI Cards */}
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #EE4D2D' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>Total Shopee GMV</Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#EE4D2D', mt: 0.5 }}>
                {formatPeso(kpis?.total_gmv)}
              </Typography>
              <Typography variant="caption" color="text.secondary">As of {report?.meta.latest_hour || 'end of day'}</Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #F59E0B' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>Shopee Ads Spend</Typography>
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
              <Typography variant="caption" color="text.secondary">Across 4 storefronts</Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '4px solid #EE4D2D' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>Target Progress</Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#EE4D2D', mt: 0.5 }}>
                {kpis?.target_progress}%
              </Typography>
              <LinearProgress
                variant="determinate"
                value={Math.min(kpis?.target_progress || 0, 100)}
                sx={{
                  mt: 1,
                  height: 6,
                  borderRadius: 3,
                  bgcolor: 'rgba(238, 77, 45, 0.15)',
                  '& .MuiLinearProgress-bar': { bgcolor: '#EE4D2D' },
                }}
              />
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* 3. Daily Target Bar & Simulation */}
      <Paper sx={{ p: 2, display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, alignItems: { sm: 'center' }, justifyContent: 'space-between', gap: 2, bgcolor: '#FFF5F2', border: '1px solid #FFD9CE' }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
          <TrackChangesRounded sx={{ color: '#EE4D2D' }} />
          <Typography variant="body2" sx={{ fontWeight: 700, color: '#9A240C' }}>
            Shopee Daily Sales Target:
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
            sx={{ bgcolor: '#EE4D2D', '&:hover': { bgcolor: '#C7381B' } }}
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
          sx={{ color: '#EE4D2D', borderColor: '#EE4D2D', bgcolor: '#fff' }}
        >
          Simulate 12 AM Push
        </Button>
      </Paper>

      {/* 4. Complete 16-Hour Matrix Table */}
      <Paper sx={{ overflow: 'hidden', border: '1px solid #FFCCBA' }}>
        <Box sx={{ bgcolor: '#EE4D2D', color: '#fff', px: 3, py: 2, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>
            SHOPEE — HOURLY BREAKDOWN (GMV per shop by hour)
          </Typography>
          <Typography variant="caption" sx={{ color: '#FFD9CE', fontWeight: 600 }}>
            DATE: {report?.meta.formatted_date} • Standard Business Hours: 9:00 AM to 12:00 AM
          </Typography>
        </Box>

        <TableContainer sx={{ maxHeight: 600 }}>
          <Table stickyHeader size="small" sx={{ minWidth: 1200 }}>
            <TableHead>
              <TableRow>
                <TableCell sx={{ bgcolor: '#EE4D2D', color: '#fff', fontWeight: 800, minWidth: 180, position: 'sticky', left: 0, zIndex: 30 }}>
                  SHOP
                </TableCell>
                <TableCell sx={{ bgcolor: '#EE4D2D', color: '#fff', fontWeight: 800, minWidth: 90, textAlign: 'center' }}>
                  METRIC
                </TableCell>
                {hoursList.map((h) => (
                  <TableCell key={h.key} sx={{ bgcolor: '#EE4D2D', color: '#fff', fontWeight: 800, minWidth: 85, textAlign: 'center' }}>
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
                        color: '#EE4D2D',
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
                    <TableCell sx={{ fontWeight: 700, textAlign: 'center', bgcolor: '#FFF0EB', color: '#9A240C', fontSize: '0.75rem' }}>
                      SALES
                    </TableCell>
                    {hoursList.map((h) => (
                      <TableCell key={`sales-${h.key}`} sx={{ textAlign: 'center', bgcolor: '#FFF7ED', fontFamily: 'monospace', fontWeight: 700 }}>
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
              <TableRow sx={{ bgcolor: '#EE4D2D' }}>
                <TableCell colSpan={hoursList.length + 2} sx={{ color: '#fff', fontWeight: 900, textAlign: 'center', py: 1.2, letterSpacing: '0.05em' }}>
                  TOTAL SHOPEE HOURLY
                </TableCell>
              </TableRow>

              {/* Total Sales Row */}
              <TableRow sx={{ bgcolor: '#C7381B' }}>
                <TableCell sx={{ color: '#fff', fontWeight: 900, position: 'sticky', left: 0, bgcolor: '#C7381B', zIndex: 20 }}>
                  TOTAL SHOPEE
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
                  TOTAL SHOPEE
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
              <TableRow sx={{ bgcolor: '#FFF5F2' }}>
                <TableCell sx={{ fontWeight: 800, color: '#EE4D2D', position: 'sticky', left: 0, bgcolor: '#FFF5F2', zIndex: 20 }}>
                  TOTAL SHOPEE
                </TableCell>
                <TableCell sx={{ fontWeight: 800, color: '#EE4D2D', textAlign: 'center', fontSize: '0.75rem' }}>
                  VS DAILY TARGET
                </TableCell>
                {hoursList.map((h) => (
                  <TableCell key={`vs-${h.key}`} sx={{ textAlign: 'center', fontFamily: 'monospace', fontWeight: 900, color: '#EE4D2D' }}>
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
            <SendRounded sx={{ color: '#EE4D2D' }} />
            <Typography variant="h6" sx={{ fontWeight: 800 }}>Shopee Dispatch Chat Text</Typography>
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
            sx={{ bgcolor: '#EE4D2D', '&:hover': { bgcolor: '#C7381B' } }}
          >
            {copied ? 'Copied to Clipboard!' : 'Copy Text'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};
