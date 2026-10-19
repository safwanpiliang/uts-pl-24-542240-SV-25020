# ---- Tahap 1: build aplikasi dengan Node.js ----
FROM node:22.23.3-alpine3.24 AS build

WORKDIR /app

# Dependensi dipasang dulu supaya lapisan ini ter-cache.
COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Alamat API dibaca Vite saat build. Nilai default "/api" artinya
# permintaan dikirim ke host yang sama, lalu Nginx meneruskannya ke backend.
ARG VITE_API_URL=/api
ENV VITE_API_URL=$VITE_API_URL
RUN npm run build

# ---- Tahap 2: sajikan hasil build dengan Nginx ----
# Hanya folder dist yang disalin, jadi node_modules tidak ada di image akhir.
FROM nginx:1.27.5-alpine

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
