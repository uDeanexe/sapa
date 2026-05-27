# Laporan Fitur Sistem SAPA (API, Chat Grup, Absensi, Admin) + Rencana Perubahan Berikutnya

Tanggal: 24 Mei 2026 (Asia/Jakarta)  
Sumber audit: kode aplikasi `sapa` (Laravel) pada folder `routes/`, `app/Http/Controllers/`, `app/Models/`, `app/Events/`, `app/Notifications/`, `database/migrations/`.

## 1) Ringkasan Eksekutif

Sistem saat ini sudah mencakup:

- **API mobile (Sanctum)** untuk autentikasi, data user, agenda, pekerjaan (jobs), absensi/presensi (check-in/out), pengajuan izin/cuti/sakit, notifikasi, serta modul chat.
- **Chat Grup (internal)** dengan fitur: kirim pesan teks + lampiran (image/video/audio/voice/file), reply (parent), pin/unpin, edit, delete, penanda “seen by”, serta dukungan **upload video besar via chunk**.  
  Tambahan penting: terdapat konsep **“private tag”** berbasis tabel `chat_recipients` sehingga pesan tertentu hanya terlihat untuk pengirim + penerima (opsional), namun ini **belum menjadi chat personal 1:1 berbentuk ruang/room**.
- **Absensi/presensi** berbasis lokasi (geofence) + foto bukti, aturan jam, serta workflow persetujuan admin/kepala untuk kondisi tertentu (di luar radius, terlambat, hari libur, dsb).
- **Admin & Kepala** memiliki dashboard ringkas (ringkasan job, presensi hari ini, pending approval), modul approval presensi & perizinan, master data (divisi & user), serta fitur operasional lain (jobs, KPI, recruitment, checklist, client).

Kebutuhan next yang disiapkan:

- **Chat personal** (1:1) dan penguatan struktur “ruang percakapan”.
- **Perbaikan penanganan file chat** (validasi, limit, konsistensi storage, preview, dan kontrol akses).
- **Penambahan ruang lingkup kerja berbasis role/divisi** (siapa boleh lihat chat/job/approval tertentu) agar lebih aman dan sesuai struktur organisasi.

## 2) Arsitektur & Komponen Utama

### 2.1 Web vs API

- **Web (Blade)**: halaman dashboard, admin operasional, halaman chat web, halaman karyawan (dashboard, absen, agenda, profile, chat).
- **API**: endpoint di `routes/api.php` untuk mobile/app lain, dengan proteksi `auth:sanctum`.

### 2.2 Autentikasi

- **API**: login menghasilkan **Bearer token** (Laravel Sanctum) (`POST /api/login`), logout menghapus token aktif (`POST /api/logout`).
- **Web**: auth bawaan Laravel (`routes/auth.php`).

### 2.3 Real-time / Broadcast (Chat & Notifikasi)

- **Broadcast chat**: event `ChatCreated` menyiarkan ke channel:
  - `chat` (publik untuk internal) bila tidak ada `chat_recipients` / pesan bukan private.
  - `PrivateChannel('chat.user.{id}')` bila pesan memiliki penerima private (recipient) → hanya user terkait yang menerima event.
- **Notifikasi internal**: `InternalNotification` disimpan ke database; jika broadcasting tidak “log/null”, notifikasi bisa broadcast. Selain itu, bila user punya `fcm_token`, sistem akan mencoba kirim **push notification** via FCM.

## 3) Fitur API yang Sudah Ada (Ringkas per Modul)

Catatan: seluruh endpoint di bawah (kecuali login) berada dalam middleware `auth:sanctum`.

### 3.1 Autentikasi & Session API

- `POST /api/login` → login dan return `access_token` + profil user.
- `POST /api/logout` → revoke token.
- `POST /api/user/fcm-token` → simpan/hapus token FCM di profil user.
- `GET /api/test-fcm-direct` → endpoint uji kirim push (internal/testing).

### 3.2 User

- `GET /api/user` → profil user login (load relasi `division`).
- `GET /api/users` → list user + role + divisi.
- `PUT /api/user/change-password` → ganti password.

### 3.3 Agenda & Kalender

- `GET /api/agenda` → ringkasan agenda periode `today` atau `week`:
  - kalender kantor (hari libur),
  - pengajuan izin user (leave),
  - pekerjaan/job terkait (teknisi atau CS),
  - tugas checklist divisi (bila ada template checklist).
- `GET /api/holidays` → daftar hari libur.

### 3.4 Absensi/Presensi (Check-in/out + Perizinan)

- `POST /api/presence/check-in` → check-in (butuh lokasi + foto + catatan).
- `POST /api/presence/check-out` dan `POST /api/presence/checkout` → check-out.
- `POST /api/presence/permission` dan `POST /api/presence/permissions` → pengajuan izin/cuti/sakit (opsional lampiran dokumen/foto).
- `GET /api/presence/today-status` dan `GET /api/presence/today` → status presensi hari ini.
- `GET /api/presence/history` → riwayat presensi.
- `GET /api/attendance/config` → konfigurasi kantor (radius, jam, toleransi, enforcement).

### 3.5 Chat (Grup + Private Tag)

- `GET /api/chats` → list pesan chat yang visible untuk user.
- `POST /api/chats` → kirim pesan (text/media/file), dukung reply (`parent_id`).
- Upload video besar (chunk):
  - `POST /api/chats/chunks` → upload potongan file.
  - `POST /api/chats/chunks/complete` → gabungkan + proses video.
- `GET /api/chats/{chat}/media` → ambil media/file attachment dengan auth.
- Moderasi & UX:
  - `PUT /api/chats/{id}` → edit pesan.
  - `DELETE /api/chats/{id}` → hapus pesan.
  - `POST /api/chats/{id}/pin` dan `/unpin` → pin/unpin.
  - `POST /api/chats/{id}/seen` dan `GET /api/chats/{id}/seen` → seen/daftar siapa yang melihat.

### 3.6 Notifikasi

- `GET /api/notifications` dan `/list` → list notifikasi.
- `POST /api/notifications/mark-read` → tandai semua read.
- `GET /api/notifications/unread-count` dan `/count` → unread count + metadata notifikasi terakhir (pakai cache 5 detik).

### 3.7 Pekerjaan/Job (Operasional Lapangan)

- `GET /api/jobs/active` → daftar job aktif (belum completed).
- `GET /api/jobs/history` → job selesai.
- `GET /api/jobs/technicians` → list teknisi (berbasis user `role=karyawan`) + divisi.
- `POST /api/jobs` → buat job.
- `GET /api/jobs/{id}` → detail job.
- `POST /api/jobs/{id}/accept` → teknisi ambil job.
- `POST /api/jobs/{id}/progress` dan `/update-progress` → update progress per tahap (dukungan foto/video/desc; requirement mengikuti konfigurasi divisi).
- `POST /api/jobs/{id}/comments` dan `/comment` → komentar job.

### 3.8 Admin API (Dashboard, Job Admin, Approval Absensi)

Khusus role `admin` dan `kepala`:

- `GET /api/admin/dashboard` → ringkasan: jumlah job hari ini/kemarin, job aktif, presensi hari ini, pending approval, pending leave, jumlah karyawan.
- `GET /api/admin/jobs` dan `GET /api/admin/jobs/{id}` → daftar & detail job admin (support filter `status`, `q`, `limit`).
- `GET /api/admin/attendance/presences` → daftar presensi yang pending approval (check-in/out).
- `POST /api/admin/attendance/presences/{id}` → approve/reject presensi.
- `GET /api/admin/attendance/leaves` → daftar leave (filter `status`).
- `POST /api/admin/attendance/leaves/{id}` → approve/reject leave.

## 4) Chat Grup: Detail Fitur yang Sudah Ada

### 4.1 Karakteristik Chat Saat Ini

- Chat sekarang bersifat **“timeline/grup”**: semua pesan berada dalam satu aliran (ordered by `created_at`).
- Ada fitur **reply** ke pesan sebelumnya (`parent_id`) sehingga mendukung thread sederhana (bukan room terpisah).
- Ada konsep **“private tag”** berbasis tabel `chat_recipients`:
  - bila pesan memiliki recipients, maka pesan hanya “visible” untuk pengirim + daftar recipients (filter lewat `Chat::scopeVisibleTo()`),
  - event broadcast akan dikirim ke `chat.user.{id}` untuk user terkait.
- Secara bisnis, ini cocok untuk: *mention private / pesan khusus ke beberapa orang di dalam grup*.
- Namun ini **belum sama dengan chat personal 1:1** (yang biasanya punya daftar percakapan, daftar kontak, unread per room, dsb).

### 4.2 Tipe Pesan & Lampiran

Tipe didukung pada API:

- `text` (message wajib)
- `image`, `video`, `audio`, `voice`, `file` (file wajib)

Implementasi file chat yang sudah ada:

- Upload file standar (non-chunk) disimpan ke storage **disk `local`** pada folder `chat-uploads/`.
- Untuk video:
  - ada upaya **kompres** memakai ffmpeg (jika tersedia),
  - setelah kompres dilakukan **enkripsi** file untuk disimpan (disimpan ke `encrypted-videos/*.enc`).
- Untuk video besar:
  - mendukung **chunk upload** (assemble → compress → encrypt → simpan sebagai chat).
- Penyajian media:
  - endpoint `ChatMediaController@show` melayani file dari berbagai lokasi (local/public/public_path) untuk menjaga kompatibilitas deployment lama,
  - untuk video terenkripsi, server membuat temporary file decrypted lalu serve sebagai file response.

### 4.3 Fitur UX & Moderasi

- **Pin/unpin** pesan (untuk highlight informasi penting).
- **Edit** pesan (flag `is_edited`).
- **Delete** pesan.
- **Seen by**:
  - tabel `chat_seens` menyimpan siapa saja yang sudah melihat pesan,
  - API menyediakan endpoint mark seen & list seen,
  - di web karyawan terdapat auto-insert `seen` untuk pesan yang belum dibaca saat membuka halaman chat.

## 5) Absensi/Presensi: Detail Fitur yang Sudah Ada

### 5.1 Check-in / Check-out Berbasis Lokasi

- Sistem membaca konfigurasi dari `OfficeSetting`:
  - titik kantor (`latitude/longitude`),
  - radius default,
  - `radius_enforced` (ketat vs fleksibel),
  - jam check-in/check-out,
  - toleransi keterlambatan (late tolerance),
  - aturan khusus hari libur (termasuk Jumat/hari libur yang diset di tabel `holidays`).
- Proses check-in/out melakukan:
  - hitung jarak user-kantor,
  - validasi radius (bisa “blok” jika radius enforced),
  - penentuan kondisi perlu approval (misalnya terlambat/di luar radius/hari libur).

### 5.2 Izin/Cuti/Sakit

- Endpoint pengajuan izin menerima:
  - jenis pengajuan (`izin/cuti/sakit`),
  - rentang tanggal,
  - alasan,
  - lampiran dokumen (pdf) / foto (jpg/png) (opsional).
- Setelah pengajuan dibuat, sistem mengirim notifikasi internal ke role `kepala` untuk approval.

### 5.3 Admin/Kepala Approval

Ada dua jalur:

- **API** untuk admin/kepala (mobile/admin dashboard):
  - list presensi pending + update status,
  - list leave + update status.
- **Web** khusus role `kepala`:
  - halaman approval presensi,
  - halaman perizinan,
  - history,
  - pengaturan jadwal & hari libur,
  - pengaturan jam & radius kantor.

## 6) Modul Admin (Web) yang Sudah Ada

### 6.1 Dashboard Admin/Kepala

- Ringkasan job (hari ini/kemarin/aktif) dan presensi (hari ini), termasuk pending approvals.

### 6.2 Master Data & Operasional

- **Divisions**: CRUD `divisions` (termasuk konfigurasi requirement foto/video/desc per tahap job dan step name).
- **Users Management**: CRUD user + reset password.
- **Jobs**:
  - list job, create job, history, timeline,
  - feedback & komentar job (dengan aturan hapus komentar tertentu).
- **Checklist**:
  - template checklist (admin) dan pengisian checklist (operasional).
- **KPI**:
  - formulir indikator, lock, jadwal evaluasi, finalisasi evaluasi.
- **Recruitment**:
  - profil, manajemen, lowongan, kandidat.
- **Clients (admin)**: list/store/update/destroy.
- **Office/Attendance Setting** (khusus kepala): settings jam & radius + jadwal libur.

## 7) Matrix Role & Ruang Lingkup Akses (Kondisi Saat Ini)

Role yang eksplisit muncul di codebase:

- `kepala`
- `admin`
- `karyawan` (digunakan sebagai “teknisi” untuk job; juga pengguna web karyawan)

### 7.1 Akses API

- Semua endpoint (kecuali `/api/login`) membutuhkan token `auth:sanctum`.
- Endpoint admin/kepala membatasi role dengan `in_array(['admin','kepala'])`.
- Absensi, chat, job (sebagian), agenda, notifikasi tersedia untuk user login (tanpa pembatasan role tambahan selain rules spesifik per aksi, misalnya `acceptJob` harus job tersebut milik teknisi terkait).

### 7.2 Akses Web

- Dashboard:
  - `kepala`/`admin` → dashboard admin.
  - `karyawan` → redirect ke `karyawan.dashboard`.
  - selain itu → redirect ke `technician.dashboard` (route ada, tapi secara role di kode dominan memakai `karyawan`).
- Middleware `role:*` saat ini **hanya mendukung satu role per route** (exact match).
  - `role:karyawan` → area karyawan.
  - `role:kepala` → halaman approval & setting absensi.

## 8) Rencana Perubahan Berikutnya (Prioritas & Rekomendasi)

Bagian ini menargetkan kebutuhan: chat personal, perbaikan file chat, dan pembatasan ruang lingkup kerja berbasis role/divisi.

### 8.1 Implementasi Chat Personal (1:1) Berbasis “Ruang Percakapan”

Masalah saat ini:

- Chat masih 1 timeline (group). Private tag sudah membantu, tapi belum menjadi pengalaman chat personal (daftar percakapan, unread per kontak, mute, arsip, dsb).

Rencana solusi:

1. Tambah struktur data “conversation/room”:
   - tabel `conversations` (type: `group` / `direct`),
   - tabel `conversation_participants` (user_id, role di room, last_read_at),
   - tabel `messages` (conversation_id, sender_id, type, content, file_path, reply_to).
2. Endpoint utama:
   - list conversations (urut last activity),
   - create/get direct conversation (2 peserta),
   - kirim message per conversation,
   - unread count per conversation,
   - read receipt per conversation.
3. Migrasi bertahap:
   - fase 1: chat personal berjalan paralel dengan chat grup yang ada,
   - fase 2: (opsional) unify UI agar user paham perbedaan “Grup” vs “Personal”.

Output bisnis:

- komunikasi 1:1 lebih rapi,
- tracking unread lebih jelas,
- kontrol akses lebih aman (pesan berada di conversation yang hanya dimiliki peserta).

### 8.2 Perbaikan & Penambahan untuk File Chat (Attachment)

Kondisi saat ini sudah baik di beberapa area (chunk video, media endpoint auth, enkripsi video), namun masih perlu standardisasi.

Rencana perbaikan:

- Standarkan validasi upload untuk semua tipe:
  - allowlist MIME per tipe,
  - limit ukuran konsisten (web + API),
  - sanitasi nama file untuk tampilan.
- Konsistensi penyimpanan:
  - tetapkan “satu sumber kebenaran” storage (misalnya tetap `local` + endpoint media auth),
  - rapikan path legacy (tetap didukung tapi tidak jadi default).
- Penguatan keamanan:
  - blok file executable/script,
  - (opsional) scanning antivirus untuk file tertentu sebelum disajikan,
  - audit pemakaian `verify=false` pada HTTP (khusus untuk environment lokal agar tidak terbawa ke production).
- UX:
  - metadata file (nama, ukuran, durasi) agar UI bisa tampilkan preview lebih informatif,
  - progress bar upload chunk untuk semua platform.

### 8.3 Ruang Lingkup Kerja Berbasis Role/Divisi (Scope Visibility)

Target:

- data & aktivitas yang terlihat user sesuai jabatan (kepala/admin/karyawan) dan divisi.

Usulan aturan scope (contoh implementasi bertahap):

- **Chat grup**:
  - opsi “ruang chat per divisi” (mis. `chat_division_id`), sehingga karyawan hanya melihat grup divisinya,
  - kepala/admin bisa melihat semua (atau memilih divisi).
- **Job**:
  - teknisi hanya melihat job miliknya,
  - CS/admin melihat job yang dibuat/ditangani,
  - kepala melihat semua + approval/monitoring.
- **Absensi**:
  - karyawan hanya melihat data dirinya,
  - kepala/admin bisa melihat semua atau filter per divisi,
  - approval khusus kepala (sudah ada), tapi bisa dibuat delegasi admin bila dibutuhkan.

Implementasi teknis yang disarankan:

- gunakan policy/authorization (Gate/Policy) agar aturan akses konsisten di API & Web,
- perluas middleware role agar bisa multi-role (`role:admin,kepala`) jika diperlukan.

### 8.4 Risiko & Checklist Kesiapan

- Pastikan tabel `chat_recipients` sudah termigrasi di semua environment (karena fitur private tag bergantung tabel ini).
- Pastikan kapasitas storage untuk media (video/audio) mencukupi (terutama temp decrypted video).
- Pastikan broadcast channel & konfigurasi realtime dipakai sesuai environment (log vs pusher/ws).
- Pastikan notifikasi FCM berjalan stabil (service account tersedia dan aman di server).

## 9) Lampiran: Halaman Web Utama (Indikasi dari Routing)

- `/dashboard` (admin/kepala) → ringkasan operasional.
- `/messages` (web chat) + `/messages/poll` (polling).
- Area karyawan:
  - `/karyawan/dashboard`
  - `/karyawan/attendance/*`
  - `/karyawan/agenda`
  - `/karyawan/chat`
- Area kepala (approval absensi):
  - `/admin/attendance/approval`
  - `/admin/attendance/perizinan`
  - `/admin/attendance/history`
  - `/admin/attendance/settings`

