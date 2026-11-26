# Plan: Pengingat Jadwal

Lima langkah. Tiap langkah menjadi **satu commit** yang pesannya diakhiri `(langkah N)`, dan bisa dinyatakan selesai atau gagal hanya dari satu pemeriksaan, tanpa membuka langkah lain. Dokumen (`PRD.md`, `erd.md`, `openapi.yaml`, `plan.md`) di-commit lebih dulu.

## Langkah 1: Tabel `reminders` dan model

- **Isi:** migrasi `reminders` (FK `schedule_id` cascade, `minutes_before`, UNIQUE `schedule_id` + `minutes_before`), model `Reminder`, factory, relasi `Schedule::reminders()`.
- **Selesai bila:** `php artisan migrate:fresh` sukses dan `php artisan test --filter=ReminderTableTest` hijau. Tes itu membuktikan database menolak pengingat ganda (AC 3) dan menghapus jadwal menghapus pengingatnya (AC 7).
- **Gagal bila:** migrasi error, atau salah satu tes itu merah.

## Langkah 2: Tes acceptance criteria endpoint tambah pengingat (MERAH)

- **Isi:** `ReminderStoreTest` untuk AC 1 sampai 6, ditulis **sebelum** ada route atau controller-nya.
- **Selesai bila:** `php artisan test --filter=ReminderStoreTest` menunjukkan **semua tes merah** karena endpoint belum ada (respons 404), bukan karena error sintaks.
- **Gagal bila:** ada tes yang hijau (berarti tesnya tidak menguji apa-apa), atau ada error PHP.

## Langkah 3: Endpoint tambah pengingat (HIJAU)

- **Isi:** route `POST /api/schedules/{schedule}/reminders`, `ReminderController@store`, validasi (rentang, unik, `remind_at` di masa depan), batas 3 di dalam transaksi dengan mengunci baris jadwal, `ReminderResource`.
- **Selesai bila:** `php artisan test --filter=ReminderStoreTest` hijau **dan** `git diff` pada berkas tes dari langkah 2 kosong (tes tidak diubah supaya hijau).
- **Gagal bila:** ada tes merah, atau berkas tes langkah 2 berubah.

## Langkah 4: Daftar dan hapus pengingat

- **Isi:** `GET` dan `DELETE` pengingat beserta tesnya, termasuk 404 untuk jadwal milik orang lain.
- **Selesai bila:** `php artisan test` (seluruh suite, termasuk tes lama) hijau.
- **Gagal bila:** ada satu tes pun yang merah.

## Langkah 5: Panel pengingat di frontend

- **Isi:** di halaman jadwal (Vue) tombol Pengingat per jadwal membuka modal berisi daftar pengingat, form menit sebelum, dan tombol hapus. Pesan galat 422 dari server ditampilkan.
- **Selesai bila:** `npm run build` di `frontend/` sukses, dan lewat `docker compose up -d --build` di `http://localhost:8081` pengingat bisa ditambah, pengingat ke-4 ditolak dengan pesan, dan pengingat bisa dihapus.
- **Gagal bila:** build gagal, atau salah satu perilaku di atas tidak terjadi di browser.

## Pemetaan ke commit

| Langkah | Pesan commit berakhiran |
| --- | --- |
| 1 | `(langkah 1)` migrasi dan model |
| 2 | `(langkah 2)` tes merah |
| 3 | `(langkah 3)` endpoint tambah |
| 4 | `(langkah 4)` daftar dan hapus |
| 5 | `(langkah 5)` frontend |
