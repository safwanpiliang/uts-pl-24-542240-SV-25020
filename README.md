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

## Pemantauan (Prometheus + Grafana)

`docker compose up -d --build` juga menjalankan empat layanan pemantau:

| Layanan | Fungsi | Alamat |
| --- | --- | --- |
| prometheus | mengumpulkan metrik, mengevaluasi alarm | http://localhost:9090 (`/targets`, `/alerts`) |
| grafana | dashboard | http://localhost:3000 |
| cadvisor | metrik CPU dan memori per container | internal |
| node-exporter | metrik mesin (host) | internal |

- Isi `GRAFANA_ADMIN_PASSWORD` di `.env` sebelum menjalankan. Password `admin` bawaan tidak dipakai.
- Dashboard "Pemantauan Jadwalku" dimuat otomatis dari `monitoring/grafana/dashboards/`.
- Aturan alarm ada di `monitoring/alerts.yml`; konfigurasi Prometheus di `monitoring/prometheus.yml`.
- Prometheus dan Grafana hanya dibuka ke `127.0.0.1`.
