# Sistem Penjadwalan Pribadi

Aplikasi penjadwalan pribadi: backend RESTful API (Laravel + Sanctum), frontend Vue.js, dijalankan dengan Docker Compose dan dikirim lewat GitHub Actions ke GitHub Container Registry.

## Struktur

```
backend/    Laravel API
frontend/   Vue.js
postman/    Postman Collection
.github/    GitHub Actions workflow
```

## Menjalankan

```bash
cp .env.example .env
docker compose up -d --build
```
