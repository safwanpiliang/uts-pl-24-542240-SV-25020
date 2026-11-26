# PRD: Pengingat Jadwal

## Masalah

Jadwal di Jadwalku hanya punya waktu mulai dan selesai. Pengguna sering melewatkan kegiatan karena tidak ada cara menandai kapan ia ingin diingatkan sebelum kegiatan dimulai.

## Penggunanya siapa

Pengguna terdaftar Jadwalku yang sudah login dan mengelola jadwal pribadinya. Hanya pemilik jadwal yang boleh melihat, menambah, dan menghapus pengingat jadwal itu.

## Out of scope

- Pengiriman pengingat sungguhan (email, push, WhatsApp) beserta penjadwal atau antreannya. Fitur ini hanya **mencatat** kapan pengguna ingin diingatkan.
- Pengingat berulang, dan pengingat untuk jadwal orang lain atau jadwal bersama.
- Mengubah pengingat. Untuk mengganti, hapus lalu buat lagi.

## Istilah

- **Pengingat** = `minutes_before`, jumlah menit sebelum jadwal mulai.
- **remind_at** = `start_at` dikurangi `minutes_before`. Nilainya dihitung, tidak disimpan.

## Acceptance criteria

Setiap kriteria bisa gagal dan akan diuji otomatis.

1. Pemilik jadwal menambah pengingat `minutes_before: 30` untuk jadwal yang mulai besok. Hasilnya **201**, dan `remind_at` tepat 30 menit sebelum `start_at`.
2. Satu jadwal maksimal **3 pengingat**. Permintaan ke-4 ditolak **422** dan jumlah pengingat tetap 3.
3. Dua pengingat dengan `minutes_before` sama pada satu jadwal ditolak **422**. Database juga menolaknya (unique), bukan hanya aplikasi.
4. Pengingat yang `remind_at`-nya sudah lewat ditolak **422**. Contoh: jadwal mulai 10 menit lagi dan `minutes_before: 30`.
5. `minutes_before` harus bilangan bulat 1 sampai 10080 (7 hari). Nilai 0, negatif, desimal, atau lebih dari 10080 ditolak **422**.
6. Pengguna lain tidak dapat menambah, melihat, atau menghapus pengingat pada jadwal yang bukan miliknya. Hasilnya **404**.
7. Menghapus jadwal ikut menghapus semua pengingatnya. Tidak boleh ada pengingat tanpa jadwal.
