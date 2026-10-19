#!/bin/sh
# Menjalankan seluruh sistem lewat Docker Compose dengan kredensial dari
# environment (di CI: GitHub Secrets), lalu memastikan login lewat frontend
# berhasil sebelum image dipublikasikan.
set -eu

: "${APP_KEY:?APP_KEY belum diisi (GitHub Secret)}"
: "${DB_PASSWORD:?DB_PASSWORD belum diisi (GitHub Secret)}"
: "${SEED_USER_PASSWORD:?SEED_USER_PASSWORD belum diisi (GitHub Secret)}"

FRONTEND_PORT="${FRONTEND_PORT:-8081}"
BACKEND_PORT="${BACKEND_PORT:-8000}"
PROJECT="${SMOKE_PROJECT:-smoke}"
SEED_USER_EMAIL="${SEED_USER_EMAIL:-admin@example.com}"

ENV_FILE="$(mktemp)"
compose() { docker compose -p "$PROJECT" --env-file "$ENV_FILE" "$@"; }

cleanup() {
  compose down -v >/dev/null 2>&1 || true
  rm -f "$ENV_FILE"
}
trap cleanup EXIT

# File env sementara ini hanya ada di mesin yang menjalankan skrip.
cat > "$ENV_FILE" <<EOT
APP_KEY=$APP_KEY
DB_DATABASE=jadwalku
DB_USERNAME=jadwalku
DB_PASSWORD=$DB_PASSWORD
SEED_USER_EMAIL=$SEED_USER_EMAIL
SEED_USER_PASSWORD=$SEED_USER_PASSWORD
FRONTEND_PORT=$FRONTEND_PORT
BACKEND_PORT=$BACKEND_PORT
EOT

compose up -d --build

echo "Menunggu backend siap..."
ready=0
for _ in $(seq 1 60); do
  if curl -fsS "http://localhost:${BACKEND_PORT}/up" >/dev/null 2>&1; then
    ready=1
    break
  fi
  sleep 2
done

if [ "$ready" -ne 1 ]; then
  echo "Backend tidak siap dalam batas waktu." >&2
  compose logs >&2
  exit 1
fi

echo "Uji login lewat frontend (Nginx -> Laravel -> PostgreSQL)..."
if ! curl -fsS -X POST "http://localhost:${FRONTEND_PORT}/api/login" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d "{\"email\":\"${SEED_USER_EMAIL}\",\"password\":\"${SEED_USER_PASSWORD}\"}" \
  | grep -q '"token"'; then
  echo "Login gagal." >&2
  compose logs >&2
  exit 1
fi

echo "Smoke test lulus."
