# Arsitektur Website

## 1. Gambaran Umum
Website ini dibangun sebagai aplikasi **full‑stack** modern dengan pemisahan jelas antara **frontend**, **backend**, dan **lapisan data**.  Tujuan utama adalah mengimplementasikan semua kebutuhan fungsional yang tercantum dalam dokumen BRD, SRS, dan FSD.

## 2. Teknologi yang Digunakan
| Lapisan | Teknologi | Alasan |
|--------|-----------|--------|
| Frontend | **React 18** + **Vite** + **TypeScript** + **Tailwind CSS** | Pengembangan UI cepat, modular, dukungan tipe kuat, dan hot‑module replacement. |
| Backend | **Node.js 20** + **Express** + **TypeScript** | API RESTful yang ringan, mudah di‑integrasikan dengan frontend TypeScript. |
| Database | **PostgreSQL 15** (Docker) | Relasional, dukungan transaksi, skalabilitas. |
| Auth | **JWT** + **bcrypt** (di‑backend) | Otentikasi stateless, cocok untuk SPA. |
| Containerization | **Docker** + **docker‑compose** | Memudahkan pengembangan lintas platform dan deployment. |
| CI/CD | **GitHub Actions** (build, test, lint, deploy ke Vercel/Netlify) |
| Hosting | **Vercel** untuk frontend (static build) & **Render** atau **DigitalOcean App Platform** untuk backend API. |

## 3. Struktur Direktori Proyek
```
project-root/
├── README.md
├── .gitignore
├── docker-compose.yml          # Docker compose untuk backend + db
├── frontend/                   # React app
│   ├── package.json
│   ├── vite.config.ts
│   ├── tsconfig.json
│   ├── public/
│   └── src/
│       ├── assets/
│       ├── components/
│       ├── pages/
│       ├── routes/
│       ├── services/          # API wrapper (axios)
│       └── App.tsx
├── backend/                    # Express API
│   ├── package.json
│   ├── tsconfig.json
│   ├── src/
│   │   ├── config/           # konfigurasi DB, env, dll
│   │   ├── controllers/      # logika endpoint
│   │   ├── models/           # ORM (type‑orm) atau query builder
│   │   ├── routes/           # definisi route
│   │   ├── middleware/       # auth, error handling
│   │   └── server.ts
│   └── Dockerfile
├── db/                         # migration, seed, schema
│   └── init.sql
└── .github/
    └── workflows/
        └── ci.yml            # GitHub Actions workflow
```

## 4. Alur Data Utama
1. **Pengguna** mengakses aplikasi melalui browser → **Frontend (React)**.
2. Frontend memanggil **API** di backend menggunakan token JWT.
3. Backend memvalidasi token, mengeksekusi logika bisnis, dan berinteraksi dengan **PostgreSQL**.
4. Hasil dikembalikan ke frontend untuk dirender.

## 5. CI/CD Pipeline (GitHub Actions)
```
name: CI/CD
on: [push, pull_request]
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Set up Node
        uses: actions/setup-node@v3
        with:
          node-version: '20'
      - name: Install dependencies (frontend)
        working-directory: ./frontend
        run: npm ci
      - name: Lint & Test (frontend)
        working-directory: ./frontend
        run: npm run lint && npm test
      - name: Build frontend
        working-directory: ./frontend
        run: npm run build
      - name: Install dependencies (backend)
        working-directory: ./backend
        run: npm ci
      - name: Lint & Test (backend)
        working-directory: ./backend
        run: npm run lint && npm test
      - name: Deploy frontend to Vercel
        uses: amondnet/vercel-action@v20
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
          working-directory: ./frontend
      - name: Deploy backend to Render
        run: echo "Deploy backend via Render CLI or Docker Hub"  # placeholder
```

## 6. Keamanan & Skalabilitas
- **HTTPS** wajib pada semua endpoint.
- **Rate limiting** di middleware Express untuk mencegah abuse.
- **CORS** dikonfigurasi hanya mengizinkan origin frontend.
- **Docker** memungkinkan penskalaan horizontal dengan container replica.
- **Environment variables** dipisahkan antara development (`.env.development`) dan production (`.env.production`).

## 7. Dokumentasi & Pengujian
- **Swagger/OpenAPI** di `/api/docs` untuk dokumentasi endpoint.
- **Jest** + **React Testing Library** untuk unit & integration test pada frontend.
- **Supertest** untuk API testing pada backend.
- **Postman Collection** (atau **Insomnia**) untuk manual testing.

---
*Dokumen ini dapat dijadikan acuan awal. Silakan sesuaikan dengan kebutuhan spesifik yang tertera di dokumen BRD/SRS/FSD Anda.*

