#!/bin/sh
set -e

# Tunggu basis data siap, lalu jalankan migrasi. Migrasi gagal selama DB
# belum menerima koneksi, jadi diulang sampai berhasil.
tries=0
until php artisan migrate --force; do
  tries=$((tries + 1))
  if [ "$tries" -ge 30 ]; then
    echo "Basis data tidak dapat dihubungi setelah ${tries} percobaan." >&2
    exit 1
  fi
  echo "Menunggu basis data... (${tries}/30)"
  sleep 2
done

# Seeder bersifat idempoten (firstOrCreate), aman dijalankan tiap start.
php artisan db:seed --force

exec "$@"
