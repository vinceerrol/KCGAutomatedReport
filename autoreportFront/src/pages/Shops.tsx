import React, { useEffect, useState } from 'react';
import {
  Box,
  Paper,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  Tabs,
  Tab,
  CircularProgress,
  Button,
} from '@mui/material';
import { StoreRounded, LaunchRounded } from '@mui/icons-material';
import { Link } from 'react-router-dom';
import { ShopsService, formatPeso, formatNumber } from '../services/api';

export const Shops: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState('all');

  useEffect(() => {
    const fetchShops = async () => {
      setLoading(true);
      try {
        const res = await ShopsService.getShops();
        setData(res);
      } catch (err) {
        console.error('Failed to load shops:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchShops();
  }, []);

  const shopsList = data?.shops || [];
  const filteredShops = shopsList.filter((s: any) => {
    if (tab === 'shopee') return s.platform_code === 'shopee';
    if (tab === 'tiktok') return s.platform_code === 'tiktok';
    return true;
  });

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header Banner */}
      <Paper sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, alignItems: { sm: 'center' }, justifyContent: 'space-between', gap: 2 }}>
        <Box>
          <Typography variant="h5" sx={{ fontWeight: 800 }}>
            E-Commerce Storefronts Directory
          </Typography>
          <Typography variant="caption" color="text.secondary">
            Multi-brand online shops connected to the central hourly reporting engine
          </Typography>
        </Box>

        <Box sx={{ display: 'flex', gap: 1 }}>
          <Button
            component={Link}
            to="/shopee"
            variant="outlined"
            size="small"
            sx={{ color: '#EE4D2D', borderColor: '#EE4D2D', '&:hover': { bgcolor: '#FFF0EB' } }}
          >
            Shopee Breakdown
          </Button>
          <Button
            component={Link}
            to="/tiktok"
            variant="outlined"
            size="small"
            sx={{ color: '#E11D48', borderColor: '#E11D48', '&:hover': { bgcolor: '#FFF1F2' } }}
          >
            TikTok Breakdown
          </Button>
        </Box>
      </Paper>

      {/* 2. Filter Tabs */}
      <Tabs
        value={tab}
        onChange={(_, val) => setTab(val)}
        sx={{
          bgcolor: 'background.paper',
          borderRadius: 3,
          p: 0.5,
          border: '1px solid',
          borderColor: 'divider',
        }}
      >
        <Tab value="all" label={`All Storefronts (${shopsList.length})`} sx={{ fontWeight: 700 }} />
        <Tab
          value="shopee"
          label="Shopee Stores"
          sx={{ fontWeight: 700, '&.Mui-selected': { color: '#EE4D2D' } }}
        />
        <Tab
          value="tiktok"
          label="TikTok Stores"
          sx={{ fontWeight: 700, '&.Mui-selected': { color: '#E11D48' } }}
        />
      </Tabs>

      {/* 3. Shops Table */}
      <Paper sx={{ overflow: 'hidden' }}>
        <TableContainer sx={{ minHeight: 350 }}>
          {loading ? (
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', p: 8 }}>
              <CircularProgress size={36} />
            </Box>
          ) : (
            <Table size="small">
              <TableHead sx={{ bgcolor: 'background.default' }}>
                <TableRow>
                  <TableCell>Store Name</TableCell>
                  <TableCell>Shop Code</TableCell>
                  <TableCell>Platform</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell align="right">Orders</TableCell>
                  <TableCell align="right">Units Sold</TableCell>
                  <TableCell align="right">Gross Sales</TableCell>
                  <TableCell align="right">Discounts</TableCell>
                  <TableCell align="right">Net Sales</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {filteredShops.map((s: any) => (
                  <TableRow key={s.shop_id} hover>
                    <TableCell sx={{ fontWeight: 700 }}>{s.shop_name}</TableCell>
                    <TableCell sx={{ fontFamily: 'monospace', color: 'text.secondary' }}>{s.shop_code}</TableCell>
                    <TableCell>
                      <Chip
                        label={s.platform_name}
                        size="small"
                        sx={{
                          height: 22,
                          fontWeight: 800,
                          fontSize: '0.65rem',
                          bgcolor: s.platform_code === 'shopee' ? '#FFF0EB' : '#FFF1F2',
                          color: s.platform_code === 'shopee' ? '#EE4D2D' : '#E11D48',
                        }}
                      />
                    </TableCell>
                    <TableCell>
                      <Chip label="Active" size="small" color="success" sx={{ height: 20, fontWeight: 700, fontSize: '0.65rem' }} />
                    </TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace' }}>{formatNumber(s.orders)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace' }}>{formatNumber(s.units_sold)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace' }}>{formatPeso(s.gross_sales)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace', color: '#D97706' }}>{formatPeso(s.discounts)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace', fontWeight: 800, color: s.platform_code === 'shopee' ? '#EE4D2D' : '#E11D48' }}>
                      {formatPeso(s.net_sales)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </TableContainer>
      </Paper>
    </Box>
  );
};
