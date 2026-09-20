import axios from 'axios';

export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api';

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 30000,
});

// Formatters for Philippine Peso (PHP ₱)
export function formatPeso(amount: number | null | undefined): string {
  if (amount === null || amount === undefined || isNaN(amount)) return '₱0.00';
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
}

export function formatNumber(val: number | null | undefined): string {
  if (val === null || val === undefined || isNaN(val)) return '0';
  return new Intl.NumberFormat('en-US').format(val);
}

// Utility to trigger browser file download from Blob
export function downloadBlob(blob: Blob, filename: string) {
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  link.parentNode?.removeChild(link);
  window.URL.revokeObjectURL(url);
}

// ==================== API SERVICES ====================

// 1. Dashboard API
export interface DashboardData {
  kpis: {
    total_orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
  };
  platforms: Array<{
    platform_id: number;
    name: string;
    code: string;
    status: string;
    shops_count: number;
    orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
  }>;
  shops: Array<{
    shop_id: number;
    shop_name: string;
    shop_code: string;
    platform_id: number;
    platform_name: string;
    platform_code: string;
    status: string;
    orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
  }>;
  hourlyPerformance: Array<{
    hour: number;
    orders: number;
    units_sold: number;
    gross_sales: number;
    discounts: number;
    refunds: number;
    net_sales: number;
  }>;
  comparison: {
    current: { hour: number; orders: number; units: number; gross_sales: number; net_sales: number };
    previous: { hour: number; orders: number; units: number; gross_sales: number; net_sales: number };
    diff: { orders: number; units: number; sales: number; percent: number };
  };
  automation: {
    status: string;
    schedule: string;
    cron: string;
    last_report_at: string | null;
    last_report_id: number | null;
    next_report_at: string;
  };
  meta: {
    current_date: string;
    current_time: string;
    selected_date: string;
    today_date: string;
    yesterday_date: string;
  };
}

export const DashboardService = {
  getDashboard: async (date?: string): Promise<DashboardData> => {
    const res = await apiClient.get<DashboardData>('/dashboard', { params: { date } });
    return res.data;
  },
};

// 2. Matrix Breakdown (Shopee & TikTok) Interfaces
export interface ShopMetrics {
  ad_spend: Record<number, number | null>;
  orders: Record<number, number | null>;
  sales: Record<number, number | null>;
  roas: Record<number, number | null>;
}

export interface ShopItem {
  shop_id: number;
  shop_code: string;
  shop_name: string;
  metrics: ShopMetrics;
}

export interface PlatformReportData {
  report: {
    meta: {
      title: string;
      platform?: string;
      report_date: string;
      formatted_date: string;
      daily_target: number;
      latest_hour: string | null;
      source: string;
    };
    hours: Record<number, string>;
    kpis: {
      total_gmv: number;
      total_ad_spend: number;
      blended_roas: number;
      total_orders: number;
      daily_target: number;
      target_progress: number;
    };
    shops: ShopItem[];
    summary: {
      total_sales: Record<number, number | null>;
      sales_increment: Record<number, number | null>;
      vs_daily_target: Record<number, number | null>;
    };
  };
  logs: Array<{
    id: number;
    job: string;
    status: string;
    message: string;
    created_at: string;
  }>;
  todayDate: string;
  serverTime: string;
}

// 3. Shopee Service
export const ShopeeService = {
  getBreakdown: async (date?: string, target?: number): Promise<PlatformReportData> => {
    const res = await apiClient.get<PlatformReportData>('/shopee', { params: { date, target } });
    return res.data;
  },

  exportExcel: async (date?: string, target?: number) => {
    const res = await apiClient.get('/shopee/export/excel', {
      params: { date, target },
      responseType: 'blob',
    });
    downloadBlob(res.data, `SHOPEE_Hourly_Breakdown_${date || 'latest'}.xlsx`);
  },

  exportCsv: async (date?: string, target?: number) => {
    const res = await apiClient.get('/shopee/export/csv', {
      params: { date, target },
      responseType: 'blob',
    });
    downloadBlob(res.data, `SHOPEE_Hourly_Breakdown_${date || 'latest'}.csv`);
  },

  simulateMidnight: async (date?: string) => {
    const res = await apiClient.post('/shopee/simulate-midnight', { date });
    return res.data;
  },

  sync: async (date?: string, hour?: number, shopId?: number) => {
    const res = await apiClient.post('/shopee/sync', { date, hour, shop_id: shopId });
    return res.data;
  },
};

// 4. TikTok Service
export const TikTokService = {
  getBreakdown: async (date?: string, target?: number): Promise<PlatformReportData> => {
    const res = await apiClient.get<PlatformReportData>('/tiktok', { params: { date, target } });
    return res.data;
  },

  exportExcel: async (date?: string, target?: number) => {
    const res = await apiClient.get('/tiktok/export/excel', {
      params: { date, target },
      responseType: 'blob',
    });
    downloadBlob(res.data, `TIKTOK_Hourly_Breakdown_${date || 'latest'}.xlsx`);
  },

  exportCsv: async (date?: string, target?: number) => {
    const res = await apiClient.get('/tiktok/export/csv', {
      params: { date, target },
      responseType: 'blob',
    });
    downloadBlob(res.data, `TIKTOK_Hourly_Breakdown_${date || 'latest'}.csv`);
  },

  simulateMidnight: async (date?: string) => {
    const res = await apiClient.post('/tiktok/simulate-midnight', { date });
    return res.data;
  },

  sync: async (date?: string, hour?: number, shopId?: number) => {
    const res = await apiClient.post('/tiktok/sync', { date, hour, shop_id: shopId });
    return res.data;
  },
};

// 5. Reports Service
export interface GeneratedReportItem {
  id: number;
  report_date: string;
  report_hour: number;
  report_time?: string;
  total_orders: number;
  total_units: number;
  gross_sales: number;
  discounts: number;
  refunds: number;
  net_sales: number;
  status: string;
  generated_at: string;
  report_data?: {
    meta?: any;
    summary?: any;
    platforms?: any[];
    shops?: any[];
  };
}

export interface PaginatedReports {
  current_page: number;
  data: GeneratedReportItem[];
  total: number;
  per_page: number;
  last_page: number;
}

export const ReportsService = {
  getReports: async (date?: string, page = 1, perPage = 15): Promise<PaginatedReports> => {
    const res = await apiClient.get<PaginatedReports>('/reports', {
      params: { date, page, per_page: perPage },
    });
    return res.data;
  },

  getReport: async (id: number): Promise<GeneratedReportItem> => {
    const res = await apiClient.get<GeneratedReportItem>(`/reports/${id}`);
    return res.data;
  },

  generateReport: async (date?: string, hour?: number, time?: string) => {
    const res = await apiClient.post('/reports/generate', { date, hour, time });
    return res.data;
  },

  exportSingleExcel: async (id: number) => {
    const res = await apiClient.get(`/reports/${id}/export.xlsx`, { responseType: 'blob' });
    downloadBlob(res.data, `hourly_report_${id}.xlsx`);
  },

  exportSingleCsv: async (id: number) => {
    const res = await apiClient.get(`/reports/${id}/export.csv`, { responseType: 'blob' });
    downloadBlob(res.data, `hourly_report_${id}.csv`);
  },

  exportBatchSummaryExcel: async (date?: string) => {
    const res = await apiClient.get('/reports/export/summary.xlsx', {
      params: { date },
      responseType: 'blob',
    });
    downloadBlob(res.data, `hourly_reports_summary_${date || 'all'}.xlsx`);
  },

  exportBatchDetailedExcel: async (date?: string) => {
    const res = await apiClient.get('/reports/export/detailed_stores.xlsx', {
      params: { date },
      responseType: 'blob',
    });
    downloadBlob(res.data, `hourly_reports_detailed_stores_${date || 'all'}.xlsx`);
  },
};

// 6. Shops & Platforms Service
export const ShopsService = {
  getShops: async () => {
    const res = await apiClient.get('/shops');
    return res.data;
  },
};

export interface ConnectorPlatformInfo {
  platform: string;
  api_version: string;
  mode: string;
  base_url: string;
  configured: boolean;
  partner_id?: string | null;
  app_key?: string | null;
  timeout_sec: number;
  shops_count: number;
  shops_configured: number;
  auth_type: string;
  orders_endpoint: string;
  order_detail_endpoint: string;
  refresh_endpoint: string;
}

export interface ConnectorsStatusData {
  shopee: ConnectorPlatformInfo;
  tiktok: ConnectorPlatformInfo;
  server_time_pht: string;
}

export const PlatformsService = {
  getPlatforms: async () => {
    const res = await apiClient.get('/platforms');
    return res.data;
  },

  getConnectorsStatus: async (): Promise<ConnectorsStatusData> => {
    const res = await apiClient.get<ConnectorsStatusData>('/platforms/connectors-status');
    return res.data;
  },
};

// 7. Automation Service
export interface AutomationData {
  status: string;
  schedule: string;
  cronExpression: string;
  lastRun: string;
  nextRun: string;
  totalReportsCount: number;
  logs: Array<{
    id: number;
    job: string;
    status: string;
    message: string;
    created_at: string;
  }>;
}

export const AutomationService = {
  getAutomation: async (): Promise<AutomationData> => {
    const res = await apiClient.get<AutomationData>('/automation');
    return res.data;
  },

  runConsolidated: async () => {
    const res = await apiClient.post('/automation/run');
    return res.data;
  },

  runShopee: async (date?: string) => {
    const res = await apiClient.post('/automation/run-shopee', { date });
    return res.data;
  },

  runTikTok: async (date?: string) => {
    const res = await apiClient.post('/automation/run-tiktok', { date });
    return res.data;
  },
};
