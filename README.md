# Backend Notify System

<p align="center">Sistem backend untuk aplikasi manajemen karyawan internal.</p>

## 📝 Gambaran Umum

Proyek ini adalah backend berbasis Laravel yang menyediakan REST API untuk aplikasi mobile. Tujuannya adalah memfasilitasi manajemen operasional harian karyawan, termasuk pemantauan kehadiran, penugasan pekerjaan lapangan, pengisian checklist, dan komunikasi internal melalui notifikasi.

## 🛠️ Teknologi yang Digunakan

- **Framework**: Laravel 10.x
- **Database**: MySQL / PostgreSQL
- **Authentication**: Laravel Sanctum (API Tokens)
- **Notifications**: Firebase Cloud Messaging (FCM) untuk push notifications
- **File Storage**: Laravel Storage (local/public disk)
- **Geolocation**: Haversine formula untuk perhitungan jarak
- **Frontend Assets**: Vite, Tailwind CSS (untuk admin panel)

## ✨ Fitur Utama

### 1. Manajemen Presensi & Kehadiran
- **Check-in & Check-out**: Karyawan melakukan absensi masuk dan pulang berbasis lokasi (GPS).
- **Validasi Geolocation**: Sistem secara otomatis menghitung jarak karyawan dari kantor dan memberlakukan aturan radius yang dapat dikonfigurasi.
- **Persetujuan Otomatis**: Kehadiran dapat disetujui secara otomatis jika memenuhi syarat (berada dalam radius, tidak terlambat, dan bukan hari libur). Jika tidak, statusnya akan `pending` untuk ditinjau admin.
- **Penanganan Keterlambatan**: Terdapat toleransi keterlambatan yang dapat diatur.
- **Pengajuan Izin/Cuti**: Karyawan dapat mengajukan izin, sakit, atau cuti melalui aplikasi dengan menyertakan lampiran (dokumen/foto).
- **Riwayat Kehadiran**: API untuk melihat riwayat absensi bulanan.

### 2. Manajemen Tugas (Job Management)
- **Pembuatan & Penugasan**: Peran `cs` atau `kepala` dapat membuat tugas baru dan menugaskannya ke teknisi (`karyawan`).
- **Siklus Hidup Tugas**: Tugas memiliki status yang jelas: `pending` (menunggu diterima), `process` (sedang dikerjakan), dan `completed` (selesai).
- **Pelacakan Progres Multi-Tahap**: Setiap tugas memiliki 4 tahap progres. Teknisi wajib melaporkan setiap tahapan yang diselesaikan.
- **Bukti Kerja**: Teknisi dapat mengunggah foto dan/atau video sebagai bukti penyelesaian setiap tahap. Persyaratan ini dapat diatur per divisi.
- **Sistem Komentar**: Semua pengguna terkait dapat berdiskusi pada halaman detail tugas melalui kolom komentar.
- **Riwayat Tugas**: Semua tugas yang telah selesai dapat dilihat kembali di halaman riwayat.

### 3. Checklist Harian
- **Template Dinamis**: Admin dapat membuat template checklist yang berbeda untuk setiap divisi.
- **Pengisian Harian**: Karyawan mengisi checklist sesuai dengan template divisi mereka.
- **Tampilan Kalender**: Riwayat pengisian checklist ditampilkan dalam format kalender untuk memudahkan pemantauan.

### 4. Notifikasi Real-time
- **Notifikasi Internal**: Notifikasi disimpan di database dan dapat diakses di dalam aplikasi (misalnya, tugas baru, persetujuan presensi).
- **Push Notification**: Terintegrasi dengan Firebase Cloud Messaging (FCM) untuk mengirim notifikasi push ke perangkat mobile, memastikan informasi penting tersampaikan dengan cepat.

### 5. Manajemen Hari Libur
- **Libur Otomatis & Manual**: Sistem secara otomatis menandai hari Jumat sebagai libur mingguan. Admin juga dapat menambahkan hari libur nasional atau cuti bersama secara manual.
- **Integrasi dengan Presensi**: Sistem absensi secara otomatis memblokir check-in pada hari libur.

## ⚙️ Arsitektur & Peran Pengguna

### Model Utama
- **User**: Model pengguna dengan role (`kepala`, `cs`, `karyawan`)
- **Presence**: Model untuk data absensi (check-in/check-out)
- **Job**: Model untuk tugas dengan tracker progres
- **JobTracker**: Model untuk menyimpan progres setiap tahap tugas
- **Checklist**: Model untuk template dan data pengisian checklist harian
- **Notification**: Model untuk notifikasi internal
- **Holiday**: Model untuk hari libur
- **Division**: Model untuk divisi karyawan

### Peran Pengguna
Sistem ini memiliki beberapa peran pengguna utama:
- **Kepala**: Memiliki akses penuh untuk memantau semua data, mengelola tugas, dan melihat riwayat.
- **CS (Customer Service)**: Dapat membuat tugas dan menugaskannya kepada teknisi.
- **Karyawan/Teknisi**: Pengguna utama aplikasi mobile yang melakukan absensi, mengerjakan tugas, dan mengisi checklist.

## 📡 Endpoints API Utama

Semua endpoint memerlukan authentication via Bearer Token (Laravel Sanctum).

### Presensi (`/api/presence`)
- `POST /check-in`: Melakukan absensi masuk.
  - **Body**: `latitude`, `longitude`
  - **Response**: Status approval otomatis atau pending
- `POST /check-out`: Melakukan absensi pulang.
  - **Body**: `latitude`, `longitude`
- `POST /permission`: Mengajukan izin, sakit, atau cuti.
  - **Body**: `type`, `reason`, `start_date`, `end_date`, `attachment` (file)
- `GET /today-status`: Mendapatkan status kehadiran hari ini.
- `GET /history?month=MM&year=YYYY`: Melihat riwayat kehadiran bulanan.

### Tugas (`/api/jobs`)
- `GET /active`: Mendapatkan daftar tugas aktif (`pending` atau `process`).
- `GET /history`: Mendapatkan riwayat tugas selesai.
- `GET /technicians`: Mendapatkan daftar teknisi (untuk assignment).
- `POST /`: Membuat tugas baru (role: CS/Kepala).
  - **Body**: `title`, `technician_id`, `client_name`, `location`, `latitude`, `longitude`, `description`
- `GET /{id}`: Melihat detail tugas.
- `POST /{id}/accept`: Menerima tugas (role: teknisi).
- `POST /{id}/update-progress`: Update progres tahap.
  - **Body**: `description_value`, `photo` (file), `video` (file)
- `POST /{id}/comment`: Menambahkan komentar.
  - **Body**: `comment`

### Notifikasi (`/api/notifications`)
- `GET /`: Mendapatkan semua notifikasi user.
- `POST /mark-read`: Tandai semua notifikasi sebagai dibaca.
- `GET /unread-count`: Jumlah notifikasi belum dibaca.

### Lainnya
- `GET /api/holidays?year=YYYY`: Daftar hari libur dalam setahun.
- `GET /api/checklists?month=MM&year=YYYY`: Data checklist user (untuk kalender).

## 🚀 Instalasi & Setup

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd backend-notify
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   - Salin file `.env.example` menjadi `.env`.
   - Konfigurasi koneksi database (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD).
   - Konfigurasi FCM untuk push notifications (FCM_SERVER_KEY).
   - Jalankan `php artisan key:generate`.

4. **Database Migration**
   ```bash
   php artisan migrate --seed
   ```

5. **Storage Link**
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Server**
   ```bash
   php artisan serve
   ```

## 🧪 Testing

Jalankan unit dan feature tests:
```bash
php artisan test
```

Atau dengan coverage:
```bash
php artisan test --coverage
```

## 💡 Logika Kompleks

### Auto-Approve Presensi

Logika persetujuan otomatis untuk check-in (`checkAutoApproveCheckIn`) mempertimbangkan beberapa faktor secara berurutan:

1. **Konfigurasi Kantor**: Memastikan pengaturan kantor (lokasi, radius) sudah ada.
2. **Hari Libur**: Memeriksa apakah hari ini adalah hari Jumat atau hari libur yang terdaftar. Jika ya, persetujuan otomatis dibatalkan.
3. **Validasi Radius**: Menghitung jarak pengguna dari kantor menggunakan formula Haversine.
   - Jika `radius_enforced` aktif dan pengguna di luar radius, check-in akan **diblokir**.
   - Jika `radius_enforced` nonaktif dan pengguna di luar radius, check-in memerlukan persetujuan manual.
4. **Validasi Waktu**: Jika pengguna berada di dalam radius (dan `radius_enforced` aktif):
   - Memeriksa apakah check-in dilakukan terlalu awal (lebih dari 2 jam sebelum jam masuk).
   - Memeriksa apakah check-in terlambat (melebihi jam masuk + toleransi).
   - Jika semua kondisi terpenuhi, check-in akan **disetujui secara otomatis**.

Untuk check-out (`checkAutoApproveCheckOut`), logikanya lebih sederhana: jika `radius_enforced` aktif, pengguna harus berada di dalam radius agar check-out disetujui otomatis.

### Logika Progres Tugas

Setiap tugas memiliki 4 tahap progres yang harus diselesaikan secara berurutan:

1. **Validasi Persyaratan**: Berdasarkan divisi teknisi, setiap tahap dapat memerlukan:
   - Deskripsi (teks)
   - Foto bukti
   - Video bukti

2. **Penyimpanan Tracker**: Setiap update progres membuat record baru di `job_trackers` dengan `step_number`.

3. **Completion Logic**: Ketika tahap 4 selesai:
   - Status tugas berubah ke `completed`
   - Hitung durasi aktual dari `accepted_at` sampai `completed_at`
   - Simpan alasan penyelesaian jika ada

4. **Validasi Duplikasi**: Sistem mencegah update duplikat untuk tahap yang sama.

### Logika Checklist

- **Template per Divisi**: Setiap divisi memiliki template checklist sendiri (JSON stored).
- **Pengisian Harian**: User hanya bisa isi sekali per hari per tipe form.
- **Kalender View**: Data dikelompokkan per tanggal untuk tampilan kalender.

### Sistem Notifikasi

- **Internal Notifications**: Dibuat otomatis untuk event seperti tugas baru, approval presensi.
- **FCM Push**: Dikirim ke device user jika ada FCM token.
- **Mark as Read**: Bulk update untuk semua notifikasi user.

## 📖 Contoh Penggunaan API

### Check-In
```bash
POST /api/presence/check-in
Authorization: Bearer {token}
Content-Type: application/json

{
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Response Sukses (Auto-Approved):**
```json
{
  "success": true,
  "message": "Check-in berhasil!",
  "data": {
    "status": "approved",
    "reason": "Dalam radius (150 m) & tepat waktu ✓",
    "distance": 150
  }
}
```

### Membuat Tugas Baru
```bash
POST /api/jobs
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Perbaikan AC Ruang Meeting",
  "technician_id": 5,
  "client_name": "PT ABC",
  "location": "Jl. Sudirman No. 123",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "description": "AC tidak dingin, perlu ganti freon"
}
```

### Update Progres Tugas
```bash
POST /api/jobs/1/update-progress
Authorization: Bearer {token}
Content-Type: multipart/form-data

description_value: "Sudah cek kondisi AC"
photo: [file]
```

## 🔧 Konfigurasi

### Pengaturan Kantor (Office Settings)
- **Latitude/Longitude**: Koordinat kantor
- **Radius**: Jarak maksimal untuk absensi (meter)
- **Radius Enforced**: Apakah radius wajib atau opsional
- **Check-in Time**: Jam masuk standar
- **Late Tolerance**: Toleransi keterlambatan (menit)

### Konfigurasi Divisi
- **Req Photo/Video/Desc per Step**: Persyaratan bukti untuk setiap tahap tugas

## 📦 Deployment

1. Setup server dengan PHP 8.1+, MySQL 8.0+
2. Clone repo dan install dependencies
3. Konfigurasi `.env` untuk production
4. Jalankan migrations dan seeders
5. Setup queue worker untuk notifications: `php artisan queue:work`
6. Configure web server (Nginx/Apache) untuk Laravel

## 🤝 Contributing

1. Fork repository
2. Buat branch fitur baru
3. Commit changes
4. Push ke branch
5. Buat Pull Request

## 📄 Lisensi

This project is licensed under the MIT License.
