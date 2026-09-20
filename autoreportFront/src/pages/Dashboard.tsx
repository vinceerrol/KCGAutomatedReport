import React, { useEffect, useState } from 'react';
import {
  Box,
  Grid,
  Paper,
  Typography,
  Card,
  CardContent,
  Chip,
  Button,
  TextField,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  CircularProgress,
  LinearProgress,
} from '@mui/material';
import {
  TrendingUpRounded,
  ShoppingBagRounded,
  StoreRounded,
  LayersRounded,
  ArrowUpwardRounded,
  ArrowDownwardRounded,
  RefreshRounded,
  CalendarMonthRounded,
  LaunchRounded,
} from '@mui/icons-material';
import { Link } from 'react-router-dom';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import { DashboardService, DashboardData, formatPeso, formatNumber } from '../services/api';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

export const Dashboard: React.FC = () => {
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState(() => new Date().toLocaleDateString('en-CA'));

  const fetchDashboard = async (date?: string) => {
    setLoading(true);
    try {
      const res = await DashboardService.getDashboard(date || selectedDate);
      setData(res);
      if (res.meta?.selected_date) {
        setSelectedDate(res.meta.selected_date);
      }
    } catch (err) {
      console.error('Failed to load dashboard:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  if (loading && !data) {
    return (
      <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', minHeight: 400, gap: 2 }}>
        <CircularProgress size={44} />
        <Typography variant="body2" color="text.secondary">
          Fetching central metrics from Laravel API...
        </Typography>
      </Box>
    );
  }

  // Chart data
  const chartLabels = data?.hourlyPerformance?.map((h) => `${h.hour}:00`) || [];
  const chartSales = data?.hourlyPerformance?.map((h) => h.net_sales) || [];

  const chartData = {
    labels: chartLabels,
    datasets: [
      {
        label: 'Net Sales (₱)',
        data: chartSales,
        borderColor: '#4F46E5',
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#4F46E5',
        pointRadius: 4,
        pointHoverRadius: 6,
      },
    ],
  };

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx: any) => `Net Sales: ₱${Number(ctx.parsed.y).toLocaleString()}`,
        },
      },
    },
    scales: {
      y: {
        grid: { color: 'rgba(0,0,0,0.05)' },
        ticks: { callback: (val: any) => `₱${Number(val).toLocaleString()}` },
      },
      x: { grid: { display: false } },
    },
  };

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header with Date Filter */}
      <Paper sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, alignItems: { sm: 'center' }, justifyContent: 'space-between', gap: 2 }}>
        <Box>
          <Typography variant="h5" sx={{ fontWeight: 800, letterSpacing: '-0.02em' }}>
            Executive Operations Dashboard
          </Typography>
          <Typography variant="caption" color="text.secondary">
            Consolidated multi-channel performance across Shopee and TikTok Shop
          </Typography>
        </Box>

        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
          <TextField
            type="date"
            size="small"
            value={selectedDate}
            onChange={(e) => {
              setSelectedDate(e.target.value);
              fetchDashboard(e.target.value);
            }}
            InputProps={{
              startAdornment: <CalendarMonthRounded sx={{ mr: 1, color: 'text.secondary', fontSize: 18 }} />,
            }}
            sx={{ width: 170 }}
          />
          <Button
            variant="outlined"
            size="small"
            startIcon={<RefreshRounded />}
            onClick={() => fetchDashboard(selectedDate)}
          >
            Refresh
          </Button>
        </Box>
      </Paper>

      {/* 2. Top KPI Cards */}
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '3px solid #4F46E5' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>
                Total Net Sales
              </Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: 'primary.main', mt: 0.5 }}>
                {formatPeso(data?.kpis.net_sales)}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Gross minus vouchers
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '3px solid #0EA5E9' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>
                Gross Sales
              </Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, mt: 0.5 }}>
                {formatPeso(data?.kpis.gross_sales)}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Before discounts/refunds
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '3px solid #F59E0B' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>
                Discounts & Vouchers
              </Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#D97706', mt: 0.5 }}>
                {formatPeso(data?.kpis.discounts)}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Platform subsidies
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '3px solid #10B981' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>
                Total Orders
              </Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, color: '#059669', mt: 0.5 }}>
                {formatNumber(data?.kpis.total_orders)}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Across all storefronts
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={2.4}>
          <Card sx={{ borderTop: '3px solid #6366F1' }}>
            <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
              <Typography variant="caption" color="text.secondary" sx={{ fontWeight: 600 }}>
                Units Sold
              </Typography>
              <Typography variant="h5" sx={{ fontWeight: 900, mt: 0.5 }}>
                {formatNumber(data?.kpis.units_sold)}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Items shipped
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* 3. Hourly Sales Trend Chart & Current vs Previous Comparison */}
      <Grid container spacing={3}>
        <Grid item xs={12} lg={8}>
          <Paper sx={{ p: 3, height: 380, display: 'flex', flexDirection: 'column' }}>
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
              <Box>
                <Typography variant="subtitle1" sx={{ fontWeight: 700 }}>
                  Hourly Net Sales Progression (PHT)
                </Typography>
                <Typography variant="caption" color="text.secondary">
                  Hourly cumulative velocity throughout the business day
                </Typography>
              </Box>
            </Box>
            <Box sx={{ flexGrow: 1, position: 'relative' }}>
              <Line data={chartData} options={chartOptions as any} />
            </Box>
          </Paper>
        </Grid>

        <Grid item xs={12} lg={4}>
          <Paper sx={{ p: 3, height: 380, display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
            <Box>
              <Typography variant="subtitle1" sx={{ fontWeight: 700 }}>
                Current vs Previous Hour Delta
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Hour {data?.comparison?.current?.hour}:00 vs Hour {data?.comparison?.previous?.hour}:00
              </Typography>

              <Box sx={{ mt: 3, p: 2, borderRadius: 3, bgcolor: (data?.comparison?.diff?.sales || 0) >= 0 ? '#ECFDF5' : '#FEF2F2' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  {(data?.comparison?.diff?.sales || 0) >= 0 ? (
                    <ArrowUpwardRounded sx={{ color: '#059669' }} />
                  ) : (
                    <ArrowDownwardRounded sx={{ color: '#DC2626' }} />
                  )}
                  <Typography variant="h5" sx={{ fontWeight: 900, color: (data?.comparison?.diff?.sales || 0) >= 0 ? '#059669' : '#DC2626' }}>
                    {formatPeso(data?.comparison?.diff?.sales)}
                  </Typography>
                  <Chip
                    label={`${data?.comparison?.diff?.percent || 0}%`}
                    size="small"
                    sx={{
                      fontWeight: 800,
                      bgcolor: (data?.comparison?.diff?.sales || 0) >= 0 ? '#10B981' : '#EF4444',
                      color: '#fff',
                    }}
                  />
                </Box>
                <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mt: 0.5 }}>
                  Net sales variance from previous hour
                </Typography>
              </Box>

              <Box sx={{ mt: 2.5, display: 'flex', flexDirection: 'column', gap: 1.5 }}>
                <Box sx={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem' }}>
                  <Typography variant="body2" color="text.secondary">Current Hour Net Sales:</Typography>
                  <Typography variant="body2" sx={{ fontWeight: 700 }}>{formatPeso(data?.comparison?.current?.net_sales)}</Typography>
                </Box>
                <Box sx={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem' }}>
                  <Typography variant="body2" color="text.secondary">Previous Hour Net Sales:</Typography>
                  <Typography variant="body2" sx={{ fontWeight: 700 }}>{formatPeso(data?.comparison?.previous?.net_sales)}</Typography>
                </Box>
                <Box sx={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem' }}>
                  <Typography variant="body2" color="text.secondary">Order Volume Delta:</Typography>
                  <Typography variant="body2" sx={{ fontWeight: 700 }}>{data?.comparison?.diff?.orders} Orders</Typography>
                </Box>
              </Box>
            </Box>

            <Box sx={{ pt: 2, borderTop: '1px solid', borderColor: 'divider' }}>
              <Typography variant="caption" color="text.secondary">
                Next scheduled background run at <strong>{data?.automation?.next_report_at}</strong>
              </Typography>
            </Box>
          </Paper>
        </Grid>
      </Grid>

      {/* 4. Independent Platform Quick Launchers */}
      <Grid container spacing={3}>
        {/* Shopee Card */}
        <Grid item xs={12} md={6}>
          <Card sx={{ borderTop: '4px solid #EE4D2D', position: 'relative' }}>
            <CardContent sx={{ p: 3 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                  <Box sx={{ width: 38, height: 38, borderRadius: 2.5, bgcolor: '#EE4D2D', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 900 }}>
                    S
                  </Box>
                  <Box>
                    <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>Shopee Marketplace</Typography>
                    <Typography variant="caption" color="text.secondary">Independent Reporting Pipeline</Typography>
                  </Box>
                </Box>
                <Chip label="Active" size="small" color="success" sx={{ height: 22, fontWeight: 700 }} />
              </Box>

              <Grid container spacing={1.5} sx={{ mb: 2 }}>
                <Grid item xs={4}>
                  <Paper sx={{ p: 1.5, textAlign: 'center', bgcolor: 'rgba(238, 77, 45, 0.05)' }}>
                    <Typography variant="caption" color="text.secondary">Stores</Typography>
                    <Typography variant="subtitle2" sx={{ fontWeight: 800, mt: 0.25 }}>4 Official</Typography>
                  </Paper>
                </Grid>
                <Grid item xs={4}>
                  <Paper sx={{ p: 1.5, textAlign: 'center', bgcolor: 'rgba(238, 77, 45, 0.05)' }}>
                    <Typography variant="caption" color="text.secondary">Net Sales</Typography>
                    <Typography variant="subtitle2" sx={{ fontWeight: 800, color: '#EE4D2D', mt: 0.25 }}>
                      {formatPeso(data?.platforms?.find((p) => p.code === 'shopee')?.net_sales)}
                    </Typography>
                  </Paper>
                </Grid>
                <Grid item xs={4}>
                  <Paper sx={{ p: 1.5, textAlign: 'center', bgcolor: 'rgba(238, 77, 45, 0.05)' }}>
                    <Typography variant="caption" color="text.secondary">Orders</Typography>
                    <Typography variant="subtitle2" sx={{ fontWeight: 800, mt: 0.25 }}>
                      {formatNumber(data?.platforms?.find((p) => p.code === 'shopee')?.orders)}
                    </Typography>
                  </Paper>
                </Grid>
              </Grid>

              <Button
                component={Link}
                to="/shopee"
                variant="contained"
                fullWidth
                endIcon={<LaunchRounded />}
                sx={{
                  bgcolor: '#EE4D2D',
                  '&:hover': { bgcolor: '#C7381B' },
                  fontWeight: 700,
                }}
              >
                Open Shopee Hourly Breakdown
              </Button>
            </CardContent>
          </Card>
        </Grid>

        {/* TikTok Card */}
        <Grid item xs={12} md={6}>
          <Card sx={{ borderTop: '4px solid #E11D48', position: 'relative' }}>
            <CardContent sx={{ p: 3 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                  <Box sx={{ width: 38, height: 38, borderRadius: 2.5, bgcolor: '#111827', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 900 }}>
                    TT
                  </Box>
                  <Box>
                    <Typography variant="subtitle1" sx={{ fontWeight: 800 }}>TikTok Shop</Typography>
                    <Typography variant="caption" color="text.secondary">Independent Reporting Pipeline</Typography>
                  </Box>
                </Box>
                <Chip label="Active" size="small" color="success" sx={{ height: 22, fontWeight: 700 }} />
              </Box>

              <Grid container spacing={1.5} sx={{ mb: 2 }}>
                <Grid item xs={4}>
                  <Paper sx={{ p: 1.5, textAlign: 'center', bgcolor: 'rgba(225, 29, 72, 0.05)' }}>
                    <Typography variant="caption" color="text.secondary">Stores</Typography>
                    <Typography variant="subtitle2" sx={{ fontWeight: 800, mt: 0.25 }}>6 Official</Typography>
                  </Paper>
                </Grid>
                <Grid item xs={4}>
                  <Paper sx={{ p: 1.5, textAlign: 'center', bgcolor: 'rgba(225, 29, 72, 0.05)' }}>
                    <Typography variant="caption" color="text.secondary">Net Sales</Typography>
                    <Typography variant="subtitle2" sx={{ fontWeight: 800, color: '#E11D48', mt: 0.25 }}>
                      {formatPeso(data?.platforms?.find((p) => p.code === 'tiktok')?.net_sales)}
                    </Typography>
                  </Paper>
                </Grid>
                <Grid item xs={4}>
                  <Paper sx={{ p: 1.5, textAlign: 'center', bgcolor: 'rgba(225, 29, 72, 0.05)' }}>
                    <Typography variant="caption" color="text.secondary">Orders</Typography>
                    <Typography variant="subtitle2" sx={{ fontWeight: 800, mt: 0.25 }}>
                      {formatNumber(data?.platforms?.find((p) => p.code === 'tiktok')?.orders)}
                    </Typography>
                  </Paper>
                </Grid>
              </Grid>

              <Button
                component={Link}
                to="/tiktok"
                variant="contained"
                fullWidth
                endIcon={<LaunchRounded />}
                sx={{
                  bgcolor: '#E11D48',
                  '&:hover': { bgcolor: '#BE123C' },
                  fontWeight: 700,
                }}
              >
                Open TikTok Hourly Breakdown
              </Button>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
};
