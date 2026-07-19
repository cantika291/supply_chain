# 🌐 Global Supply Chain Risk Intelligence Platform

Platform monitoring risiko rantai pasok global berbasis multi-API dan analitik data.

## Teknologi

- **Backend**: Laravel 13, PHP 8.3, MySQL
- **Frontend**: Bootstrap 5, JavaScript ES6, AJAX
- **Visualisasi**: Chart.js, Leaflet.js
- **API**: Open-Meteo, World Bank, ExchangeRate-API, GNews, countries.dev, World Port Index

## Fitur Utama

1. Country Dashboard — GDP, Inflasi, Populasi, Kurs, Cuaca per negara
2. Risk Scoring Engine — Weighted Risk Model (Weather 30% + Inflation 20% + Currency 10% + News 40%)
3. Weather Monitoring — Peta cuaca global interaktif dengan Leaflet.js
4. Currency Dashboard — Kurs real-time + grafik tren Chart.js
5. News Intelligence — Berita logistik/ekonomi + Lexicon-Based Sentiment Analysis
6. Port Dashboard — 3.889 pelabuhan dari World Port Index dengan marker interaktif
7. Data Visualization — GDP Trend, Inflation Trend, Currency Trend, Risk Trend
8. Country Comparison — Bandingkan 2 negara berdasarkan 7 indikator
9. Favorite Monitoring — Watchlist negara pilihan per user
10. Admin Dashboard — Kelola user, pelabuhan, artikel analisis
11. REST API — Endpoint: /api/countries, /api/risk, /api/ports, /api/news, /api/currency

## Instalasi

### Requirements

- PHP >= 8.3
- MySQL >= 8.0
- Composer
- Laragon (Windows)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone [URL_REPO]
cd supply-chain-management

# 2. Install dependencies
composer install

# 3. Copy environment file
copy .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Isi konfigurasi database & API key di .env

# 6. Jalankan migration & seeder
php artisan migrate
php artisan db:seed

# 7. Sinkronisasi data dari API
php artisan sync:countries
php artisan risk:calculate
php artisan sentiment:analyze

# 8. Jalankan server
php artisan serve
```

### Akun Default

- **Admin**: admin@supplychain.test / password123
- **User**: daftar melalui /register

## API Endpoints

| Method | Endpoint                 | Deskripsi                |
| ------ | ------------------------ | ------------------------ |
| GET    | /api/countries           | Daftar semua negara      |
| GET    | /api/v1/countries/{cca3} | Detail 1 negara          |
| GET    | /api/risk                | Risk score semua negara  |
| GET    | /api/v1/risk/{cca3}      | Risk score 1 negara      |
| GET    | /api/ports               | Data pelabuhan           |
| GET    | /api/news                | Berita terbaru           |
| GET    | /api/v1/news/sentiment   | Ringkasan sentimen       |
| GET    | /api/currency            | Kurs semua mata uang     |
| GET    | /api/v1/currency/{code}  | Histori kurs 1 mata uang |

## Dibuat Oleh

selfia cantika — Universitas Malikussaleh
