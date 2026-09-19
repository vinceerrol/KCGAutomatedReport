# Hourly Reporting Automation System — MVP Prototype

> **PROTOTYPE NOTICE:**  
> **"This is a prototype using fictional demo data. Actual company reporting requirements and platform integrations must be validated before production deployment."**  
> No external marketplace API keys (Shopee, TikTok Shop) are connected, and no live company credentials or actual store revenue numbers are used in this MVP.

---

## 1. Project Purpose

Currently, e-commerce operations teams frequently spend significant time each day manually checking multiple marketplace seller dashboards, downloading or copying hourly metrics, reconciling discounts and vouchers, manually calculating net performance in spreadsheets, and formatting text messages to send to internal chat groups.

The **Hourly Reporting Automation System** is a proof-of-concept prototype built to demonstrate to management:
1. **Centralized Visibility:** What unified hourly reporting looks like across multiple e-commerce platforms and storefronts.
2. **Multi-Shop Aggregation:** How data from various platforms (e.g., Shopee, TikTok Shop) and multiple shops per platform can be seamlessly consolidated.
3. **Automated Calculations:** How gross sales, discounts, refunds, and net sales can be calculated deterministically and snapped into immutable hourly snapshots.
4. **Historical Auditability:** How past hourly performance, trends, and growth can be reviewed at any time.
5. **Efficiency & Error Reduction:** How a scheduled background engine eliminates repetitive manual clerical work and human calculation errors.

---

## 2. MVP Limitations

- **Fictional Demo Data:** All sales, orders, units, shops, and platform metrics are simulated using realistic relational seed data.
- **No Direct Platform APIs Yet:** Official Shopee Open API and TikTok Shop Partner APIs have not yet been queried or integrated.
- **Extensible Service Layer:** An interface-based service architecture (`PlatformDataServiceInterface`) is implemented so production API services (`ShopeeDataService`, `TikTokDataService`) can be plugged in later without altering the dashboard or frontend.
- **No Complex Authentication:** To allow friction-free stakeholder review, authentication is omitted for this prototype.

---

## 3. Technology Stack

- **Backend Framework:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL (relational schema with proper decimal precision)
- **Frontend Framework:** Vue 3 (Composition API, `<script setup>`)
- **Type Safety:** TypeScript
- **Full-Stack Adapter:** Inertia.js (Single Page Application architecture with server-driven routing)
- **Styling:** Tailwind CSS
- **Data Visualization:** Chart.js + `vue-chartjs`
- **Asset Bundler:** Vite
- **Job Scheduling:** Laravel Console Scheduler (`0 * * * *`)
- **Version Control:** Git

---

## 4. System Architecture

```
PROPOSED PRODUCTION WORKFLOW:
┌───────────────────────────┐      ┌──────────────────────────┐
│   Shopee Open API v2      │      │  TikTok Shop Partner API │
└─────────────┬─────────────┘      └────────────┬─────────────┘
              │                                 │
              └───────────────┬─────────────────┘
                              ▼
        ┌──────────────────────────────────────────────┐
        │       PlatformDataServiceInterface           │
        ├──────────────────────────────────────────────┤
        │  [MVP: DemoPlatformDataService]              │
        │  [Future: AggregatedPlatformDataService]     │
        └─────────────────────┬────────────────────────┘
                              ▼
        ┌──────────────────────────────────────────────┐
        │            Database (MySQL)                  │
        │  platforms → shops → hourly_metrics          │
        └─────────────────────┬────────────────────────┘
                              ▼
        ┌──────────────────────────────────────────────┐
        │      ReportGeneratorService (Hourly)         │
        │  Gross Sales - Discounts - Refunds = Net     │
        └──────────────┬────────────────┬──────────────┘
                       │                │
                       ▼                ▼
        ┌─────────────────────┐  ┌─────────────────────┐
        │  generated_reports  │  │   automation_logs   │
        └──────────────┬──────┘  └─────────────────────┘
                       ▼
        ┌──────────────────────────────────────────────┐
        │       Vue 3 + Inertia Management UI          │
        │  • Live KPI Cards (PHP ₱)                    │
        │  • Hourly Line Chart (Chart.js)              │
        │  • Current vs Previous Hour Delta            │
        │  • Platform & Shop Breakdown                 │
        │  • Exportable Formatted Dispatch Message     │
        └──────────────────────────────────────────────┘
```

---

## 5. Installation & Prerequisites

Ensure the following are installed:
- PHP 8.2+ with PDO, MySQL, and cURL extensions enabled
- Composer 2.7+
- Node.js 18+ and npm
- MySQL Server (e.g., via XAMPP or native service)

### Clone and Setup Dependencies

```bash
# 1. Clone the repository
git clone <repo-url>
cd AUTOMATE-REPORT

# 2. Install PHP backend dependencies
composer install

# 3. Install Node.js frontend dependencies
npm install
```

---

## 6. Environment Configuration

Copy the example environment file and configure your database settings:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` to match your local MySQL configuration:

```env
APP_NAME="Hourly Reporting Automation"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hourly_reports
DB_USERNAME=root
DB_PASSWORD=
```

---

## 7. Database Setup & Migrations

Create the MySQL database (if not already existing):

```sql
CREATE DATABASE IF NOT EXISTS hourly_reports CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Execute migrations to establish the relational tables:

```bash
php artisan migrate
```

### Relational Schema Summary

| Table | Purpose | Key Columns |
|---|---|---|
| `platforms` | E-commerce marketplace channels | `id`, `name`, `code`, `status` |
| `shops` | Storefronts belonging to a platform (1:N) | `id`, `platform_id`, `name`, `code`, `status` |
| `hourly_metrics` | Raw hourly metrics per shop | `id`, `shop_id`, `report_date`, `hour`, `orders`, `units_sold`, `gross_sales`, `discounts`, `refunds`, `net_sales` |
| `generated_reports` | Immutable hourly snapshot records | `id`, `report_date`, `report_hour`, `total_orders`, `total_units`, `gross_sales`, `discounts`, `refunds`, `net_sales`, `report_data`, `generated_at`, `status` |
| `automation_logs` | Audit trail of background executions | `id`, `job`, `status`, `message`, `created_at` |

---

## 8. Seeder Usage (Demo Data)

Populate the database with realistic e-commerce demonstration data:

```bash
php artisan db:seed
```

This seeds:
- Platforms: **Shopee** and **TikTok Shop**
- Storefronts:
  - Shopee Shop A (Flagship)
  - Shopee Shop B (Beauty & Care)
  - Shopee Shop C (Electronics & Accessories)
  - TikTok Shop A (Live Stream Official)
  - TikTok Shop B (Creator Affiliate)
- Hourly metrics from 8:00 AM to 2:00 PM for today and yesterday
- Pre-generated snapshot reports for 9:00 AM, 10:00 AM, and 11:00 AM with audit log events

---

## 9. Running the Development Server

You can run the application locally in development mode:

### Option A: Standard Two-Process Mode

**Terminal 1 (Backend Server):**
```bash
php artisan serve
```
*Accessible at `http://127.0.0.1:8000`*

**Terminal 2 (Vite HMR Server):**
```bash
npm run dev
```

### Option B: Production Asset Build

```bash
npm run build
php artisan serve
```

---

## 10. Running the Report Generator (Artisan Command)

To trigger the automated calculation engine directly from the command line:

```bash
# Generate report for current hour
php artisan reports:generate

# Generate report for a specific date and hour
php artisan reports:generate --date=2026-09-18 --hour=14
```

### Sample Output

```text
==========================================
HOURLY SALES REPORT AUTOMATION
Target Period: 2026-09-18 at 14:00
Status: Processing demo platform data...

Report generated successfully! (Report #5)
+------------------+---------------------+
| Metric           | Aggregated Value    |
+------------------+---------------------+
| Reporting Date   | 2026-09-18          |
| Reporting Hour   | 14:00               |
| Total Orders     | 25                  |
| Total Units Sold | 32                  |
| Gross Sales      | PHP 8,550.00        |
| Discounts        | PHP 290.00          |
| Refunds          | PHP 0.00            |
| Net Sales        | PHP 8,260.00        |
| Status           | COMPLETED           |
| Generated At     | 2026-09-18 16:38:00 |
+------------------+---------------------+
Execution successfully logged to automation_logs.
==========================================
```

---

## 11. Running the Laravel Scheduler

The hourly report generation task is registered in `routes/console.php`:

```php
Schedule::command('reports:generate')->hourly()->withoutOverlapping();
```

To verify the scheduled job:
```bash
php artisan schedule:list
```

To run a single schedule evaluation check:
```bash
php artisan schedule:run
```

To run the scheduler worker locally as a daemon:
```bash
php artisan schedule:work
```

---

## 12. Future API Integration Plan

The application is architected according to clean architecture principles so live APIs can replace the demo service seamlessly:

```
                  ┌─────────────────────────────────────┐
                  │    PlatformDataServiceInterface     │
                  └──────────────────┬──────────────────┘
                                     │
           ┌─────────────────────────┴─────────────────────────┐
           │                                                   │
┌──────────▼───────────────┐                       ┌───────────▼─────────────────────┐
│  DemoPlatformDataService │                       │  AggregatedPlatformDataService  │
│  (Current MVP Driver)    │                       │  (Production Multi-API Driver)  │
└──────────────────────────┘                       └───────────┬─────────────────────┘
                                                               │
                                           ┌───────────────────┴───────────────────┐
                                           │                                       │
                               ┌───────────▼─────────────┐             ┌───────────▼─────────────┐
                               │    ShopeeDataService    │             │    TikTokDataService    │
                               │   (Shopee Open API v2)  │             │  (TikTok Partner API)   │
                               └─────────────────────────┘             └─────────────────────────┘
```

### Integration Roadmap

1. **Credentials & OAuth Layer:**
   - Register Developer accounts on Shopee Open Platform and TikTok Shop Partner Center.
   - Store OAuth credentials, refresh tokens, and shop authorizations encrypted in the database.
2. **Scheduled Collector Workers:**
   - Implement `ShopeeDataService` to query order endpoints (e.g., `/api/v2/order/get_order_list`).
   - Implement `TikTokDataService` to query TikTok Shop order APIs.
3. **Normalization Pipeline:**
   - Normalize marketplace order status, discount vouchers, buyer cancellation refunds, and item unit quantities into the `hourly_metrics` database table.
4. **Delivery Channel Connectors:**
   - Expand `ReportGeneratorService` to dispatch generated reports to company communication channels:
     - Slack Webhooks
     - Microsoft Teams Webhooks
     - Discord Webhooks
     - Email (Laravel Mailables)
     - Messenger API / Chatwork / Lark

---

## 13. Security Considerations

- **No Secrets in Git:** Sensitive configuration keys and passwords are excluded via `.gitignore`.
- **Environment Isolation:** All database and system configurations use `.env` files.
- **Decimal Accounting:** Financial values (`gross_sales`, `discounts`, `refunds`, `net_sales`) are strictly stored as `DECIMAL(12,2)` and calculated deterministically to prevent floating-point rounding errors.
