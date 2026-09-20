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
  Button,
  TextField,
  Chip,
  Pagination,
  CircularProgress,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  IconButton,
  Tooltip,
} from '@mui/material';
import {
  FileDownloadRounded,
  CalendarMonthRounded,
  RefreshRounded,
  VisibilityRounded,
  CloseRounded,
  AssessmentRounded,
} from '@mui/icons-material';
import { ReportsService, PaginatedReports, GeneratedReportItem, formatPeso, formatNumber } from '../services/api';

export const Reports: React.FC = () => {
  const [reportsData, setReportsData] = useState<PaginatedReports | null>(null);
  const [loading, setLoading] = useState(true);
  const [dateFilter, setDateFilter] = useState('');
  const [page, setPage] = useState(1);
  const [selectedReport, setSelectedReport] = useState<GeneratedReportItem | null>(null);
  const [dialogOpen, setDialogOpen] = useState(false);

  const fetchReports = async (p = 1, d = dateFilter) => {
    setLoading(true);
    try {
      const res = await ReportsService.getReports(d || undefined, p, 15);
      setReportsData(res);
      setPage(res.current_page);
    } catch (err) {
      console.error('Failed to fetch reports:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchReports(1, '');
  }, []);

  const handlePageChange = (_: React.ChangeEvent<unknown>, val: number) => {
    fetchReports(val, dateFilter);
  };

  const handleDateFilterChange = (d: string) => {
    setDateFilter(d);
    fetchReports(1, d);
  };

  const handleViewDetails = (report: GeneratedReportItem) => {
    setSelectedReport(report);
    setDialogOpen(true);
  };

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
      {/* 1. Header Banner */}
      <Paper sx={{ p: 2.5, display: 'flex', flexDirection: { xs: 'column', md: 'row' }, alignItems: { md: 'center' }, justifyContent: 'space-between', gap: 2 }}>
        <Box>
          <Typography variant="h5" sx={{ fontWeight: 800 }}>
            Hourly Generated Snapshot Reports
          </Typography>
          <Typography variant="caption" color="text.secondary">
            Immutable historical record snapshots aggregated and logged every hour
          </Typography>
        </Box>

        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, flexWrap: 'wrap' }}>
          <TextField
            type="date"
            size="small"
            value={dateFilter}
            onChange={(e) => handleDateFilterChange(e.target.value)}
            InputProps={{
              startAdornment: <CalendarMonthRounded sx={{ mr: 1, color: 'text.secondary', fontSize: 18 }} />,
            }}
            sx={{ width: 170 }}
          />

          <Button
            variant="outlined"
            size="small"
            startIcon={<RefreshRounded />}
            onClick={() => fetchReports(1, dateFilter)}
          >
            Refresh
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={() => ReportsService.exportBatchSummaryExcel(dateFilter)}
            startIcon={<FileDownloadRounded />}
            sx={{ bgcolor: '#059669', '&:hover': { bgcolor: '#047857' }, fontWeight: 700 }}
          >
            Summary (.xlsx)
          </Button>

          <Button
            variant="contained"
            size="small"
            onClick={() => ReportsService.exportBatchDetailedExcel(dateFilter)}
            startIcon={<FileDownloadRounded />}
            sx={{ bgcolor: '#4F46E5', '&:hover': { bgcolor: '#4338CA' }, fontWeight: 700 }}
          >
            Detailed Stores (.xlsx)
          </Button>
        </Box>
      </Paper>

      {/* 2. Reports Table */}
      <Paper sx={{ overflow: 'hidden' }}>
        <TableContainer sx={{ minHeight: 400 }}>
          {loading ? (
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', p: 8 }}>
              <CircularProgress size={36} />
            </Box>
          ) : (
            <Table size="small">
              <TableHead sx={{ bgcolor: 'background.default' }}>
                <TableRow>
                  <TableCell>Report ID</TableCell>
                  <TableCell>Period Date</TableCell>
                  <TableCell>Time (PHT)</TableCell>
                  <TableCell align="right">Orders</TableCell>
                  <TableCell align="right">Units</TableCell>
                  <TableCell align="right">Gross Sales</TableCell>
                  <TableCell align="right">Discounts</TableCell>
                  <TableCell align="right">Refunds</TableCell>
                  <TableCell align="right">Net Sales</TableCell>
                  <TableCell align="center">Status</TableCell>
                  <TableCell align="center">Actions</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {reportsData?.data?.map((r) => (
                  <TableRow key={r.id} hover>
                    <TableCell sx={{ fontFamily: 'monospace', fontWeight: 700 }}>#{r.id}</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>{r.report_date}</TableCell>
                    <TableCell sx={{ fontFamily: 'monospace', fontWeight: 700 }}>
                      {r.report_time || `${String(r.report_hour).padStart(2, '0')}:00`}
                    </TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace' }}>{formatNumber(r.total_orders)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace' }}>{formatNumber(r.total_units)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace' }}>{formatPeso(r.gross_sales)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace', color: '#D97706' }}>{formatPeso(r.discounts)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace', color: '#DC2626' }}>{formatPeso(r.refunds)}</TableCell>
                    <TableCell align="right" sx={{ fontFamily: 'monospace', fontWeight: 800, color: 'primary.main' }}>
                      {formatPeso(r.net_sales)}
                    </TableCell>
                    <TableCell align="center">
                      <Chip label={r.status.toUpperCase()} size="small" color="success" sx={{ height: 20, fontSize: '0.65rem', fontWeight: 800 }} />
                    </TableCell>
                    <TableCell align="center">
                      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 0.5 }}>
                        <Tooltip title="View Snapshot Details">
                          <IconButton size="small" onClick={() => handleViewDetails(r)}>
                            <VisibilityRounded fontSize="small" />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Download Single Excel">
                          <IconButton size="small" color="success" onClick={() => ReportsService.exportSingleExcel(r.id)}>
                            <FileDownloadRounded fontSize="small" />
                          </IconButton>
                        </Tooltip>
                      </Box>
                    </TableCell>
                  </TableRow>
                ))}
                {reportsData?.data?.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={11} sx={{ textAlign: 'center', py: 6, color: 'text.secondary' }}>
                      No generated reports found matching query.
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          )}
        </TableContainer>

        {/* Pagination Footer */}
        {reportsData && reportsData.last_page > 1 && (
          <Box sx={{ p: 2, display: 'flex', justifyContent: 'center' }}>
            <Pagination
              count={reportsData.last_page}
              page={page}
              onChange={handlePageChange}
              color="primary"
              shape="rounded"
            />
          </Box>
        )}
      </Paper>

      {/* 3. Snapshot Details Modal */}
      <Dialog open={dialogOpen} onClose={() => setDialogOpen(false)} maxWidth="md" fullWidth>
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <AssessmentRounded sx={{ color: 'primary.main' }} />
            <Typography variant="h6" sx={{ fontWeight: 800 }}>
              Report Snapshot #{selectedReport?.id} Details
            </Typography>
          </Box>
          <IconButton onClick={() => setDialogOpen(false)} size="small">
            <CloseRounded />
          </IconButton>
        </DialogTitle>
        <DialogContent dividers>
          {selectedReport && (
            <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2.5 }}>
              <Box sx={{ display: 'flex', justifyContent: 'space-between', flexWrap: 'wrap', gap: 2 }}>
                <Typography variant="body2">
                  <strong>Reporting Period:</strong> {selectedReport.report_date} at {selectedReport.report_time || `${String(selectedReport.report_hour).padStart(2, '0')}:00`} (PHT)
                </Typography>
                <Typography variant="body2">
                  <strong>Generated At:</strong> {new Date(selectedReport.generated_at).toLocaleString()}
                </Typography>
              </Box>

              <Typography variant="subtitle2" sx={{ fontWeight: 800 }}>
                Storefronts Ingested In This Snapshot:
              </Typography>

              <TableContainer component={Paper} variant="outlined">
                <Table size="small">
                  <TableHead sx={{ bgcolor: 'background.default' }}>
                    <TableRow>
                      <TableCell>Store Name</TableCell>
                      <TableCell>Platform</TableCell>
                      <TableCell align="right">Orders</TableCell>
                      <TableCell align="right">Gross Sales</TableCell>
                      <TableCell align="right">Net Sales</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {selectedReport.report_data?.shops?.map((s: any, idx: number) => (
                      <TableRow key={idx}>
                        <TableCell sx={{ fontWeight: 600 }}>{s.shop_name}</TableCell>
                        <TableCell>
                          <Chip
                            label={s.platform_name}
                            size="small"
                            sx={{
                              height: 20,
                              fontWeight: 700,
                              fontSize: '0.65rem',
                              bgcolor: s.platform_code === 'shopee' ? '#FFF0EB' : '#FFF1F2',
                              color: s.platform_code === 'shopee' ? '#EE4D2D' : '#E11D48',
                            }}
                          />
                        </TableCell>
                        <TableCell align="right">{formatNumber(s.orders)}</TableCell>
                        <TableCell align="right">{formatPeso(s.gross_sales)}</TableCell>
                        <TableCell align="right" sx={{ fontWeight: 700 }}>{formatPeso(s.net_sales)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            </Box>
          )}
        </DialogContent>
        <DialogActions sx={{ p: 2 }}>
          <Button onClick={() => setDialogOpen(false)}>Close</Button>
          {selectedReport && (
            <Button
              variant="contained"
              color="primary"
              startIcon={<FileDownloadRounded />}
              onClick={() => ReportsService.exportSingleExcel(selectedReport.id)}
            >
              Export Excel Snapshot
            </Button>
          )}
        </DialogActions>
      </Dialog>
    </Box>
  );
};
