# E-Sebaneh V2 - Dokumentasi Proyek

---

## Deskripsi Proyek

E-Sebaneh V2 adalah sistem manajemen pondok pesantren berbasis web yang dibangun dengan:

-   **Backend**: Laravel 12, PHP 8.3
-   **Frontend**: Vue 3, Inertia.js, Nuxt UI v4
-   **Database**: MySQL
-   **Authentication**: Laravel Sanctum + Google OAuth

---

## Roles & Access Control

### 1. Super Admin

Akses penuh ke seluruh sistem, termasuk manajemen pondok dan CMS.

**Menu yang dapat diakses:**
| Menu | Route | Deskripsi |
|------|-------|-----------|
| Dashboard | `/dashboard` | Halaman utama dashboard |
| Pondok | `/dashboard/boarding-schools` | CRUD penuh pondok pesantren |
| Jenjang Sekolah | `/dashboard/schools` | Master data tingkat sekolah |
| Jabatan | `/dashboard/positions` | Master data jabatan pegawai |
| Tahun Ajaran | `/dashboard/school-years` | Kelola tahun ajaran (aktifkan) |
| CMS | `/dashboard/cms/*` | Kelola konten website |

**Sub-menu CMS:**

-   Berita (`/dashboard/cms/posts`)
-   Program (`/dashboard/cms/programs`)
-   Galeri (`/dashboard/cms/galleries`)
-   Testimoni (`/dashboard/cms/testimonials`)
-   FAQ (`/dashboard/cms/faqs`)
-   Web Setting (`/dashboard/cms/settings`)

---

### 2. Admin Pondok

Akses terbatas ke pondok yang ditugaskan saja (scoped access).

**Menu yang dapat diakses:**
| Menu | Route | Deskripsi |
|------|-------|-----------|
| Dashboard | `/dashboard` | Halaman utama |
| Data Santri | `/dashboard/students` | CRUD santri |
| RFID Santri | `/dashboard/student-rfid` | Daftarkan RFID |
| Data Kelas | `/dashboard/classrooms` | CRUD kelas |
| Data Kamar | `/dashboard/bed-rooms` | CRUD kamar |
| Data Pegawai | `/dashboard/teachers` | CRUD pegawai |
| Pengaturan Surat | `/dashboard/settings/letter` | Setting kop surat |
| Batas Penarikan | `/dashboard/finance/student-withdraw-limit` | Limit withdraw |
| Manajemen Saldo | `/dashboard/finance/student-balance` | Saldo, penarikan RFID, riwayat |
| Tagihan Santri | `/dashboard/finance/student-invoices` | Kelola tagihan SPP, dll |
| Pendaftaran (PSB) | `/dashboard/student-registrations` | Manajemen pendaftaran santri baru |

---

## Fitur Detail per Modul

### 1. Manajemen Santri

**Route**: `/dashboard/students`

**Fitur:**

-   CRUD santri dengan stepper form (3 step: Biodata, Orang Tua, Akademik)
-   Filter: Nama/NIS, RFID, Status, Gender, Kelas
-   Export/Import Excel
-   Upload foto
-   Auto-generate email dari NIS

**Sub-fitur di halaman Detail Santri:**
| Tab | Deskripsi |
|-----|-----------|
| Biodata | Data diri santri |
| Orang Tua | Data ayah & ibu |
| Akademik | Riwayat kelas & sekolah |
| Kamar | Riwayat kamar/asrama |
| Rekam Medis | Riwayat penyakit |
| Hafalan | Catatan hafalan Quran |
| Izin | Riwayat izin/pulang |
| Pelanggaran | Catatan pelanggaran |

---

### 2. Pendaftaran RFID Santri

**Route**: `/dashboard/student-rfid`

**Fitur:**

-   List santri dengan status RFID
-   Filter: Nama/NIS, Kelas, Status RFID (default: belum terdaftar)
-   Modal untuk scan/input RFID
-   Validasi RFID unik

---

### 3. Batas Penarikan Santri

**Route**: `/dashboard/finance/student-withdraw-limit`

**Fitur:**

-   Set batas penarikan harian per pondok
-   Update limit dengan updateOrCreate pattern

---

### 4. Manajemen Keuangan Santri (Finance)

**Route**: `/dashboard/finance/student-balance`

**Konsep**: Halaman terpadu dengan 3 tab untuk manajemen keuangan santri yang lebih efisien.

#### Tab 1: Saldo Santri

**Fitur**:

-   Summary cards (Total Saldo, Rata-rata, Jumlah Santri)
-   Tabel daftar santri dengan saldo
-   Filter: Nama, NIS, RFID, Kelas
-   Quick Actions inline:
    -   Topup Saldo (modal)
    -   Tarik Saldo (modal with PIN)
    -   Ubah PIN (modal)
    -   Lihat Riwayat

#### Tab 2: Penarikan Saldo (RFID Scan)

**Fitur**:

-   Input RFID (auto-focus untuk scan)
-   Tampilan info santri otomatis setelah scan
-   Form penarikan dengan validasi:
    -   Cek saldo mencukupi
    -   Cek limit harian
    -   Validasi PIN
-   Info batas penarikan harian

#### Tab 3: Riwayat Transaksi

**Fitur**:

-   Filter: Santri, Tanggal, Tipe Transaksi
-   Summary (Total Topup, Total Withdraw)
-   Tabel riwayat dengan pagination
-   Export Excel sesuai filter

**Database**:

-   `student_withdraw_history` (topup/withdraw history)
-   `student_withdraw_limits` (daily limit per pondok)

---

---

### 5. Manajemen Tagihan Santri

**Route**: `/dashboard/finance/student-invoices`

**Fitur:**

-   **CRUD Tagihan**: Membuat, mengedit, menghapus tagihan santri.
-   **Target Fleksibel**:
    -   Semua Santri (aktif)
    -   Berdasarkan Kelas
    -   Berdasarkan Gender (Putra/Putri)
    -   Santri Tertentu (Pencarian spesifik)
-   **Status Pembayaran Real-time**:
    -   Monitoring status lunas/belum lunas per santri.
    -   Indikator visual (badge) untuk status pembayaran.
-   **Pembayaran Offline**:
    -   Pencatatan pembayaran manual oleh admin.
    -   Konfirmasi pembayaran satu klik.
-   **Laporan**:
    -   Detail tagihan menampilkan total target dan total yang sudah membayar.

---

### 6. Manajemen Pegawai/Ustadz

**Route**: `/dashboard/teachers`

**Fitur:**

-   CRUD pegawai/ustadz
-   Filter & search
-   Export/Import Excel
-   Stepper form

---

### 7. Manajemen Kelas

**Route**: `/dashboard/classrooms`

**Fitur:**

-   CRUD kelas per tahun ajaran
-   Assign guru wali kelas
-   Scoped per pondok

---

### 8. Manajemen Kamar

**Route**: `/dashboard/bed-rooms`

**Fitur:**

-   CRUD kamar/asrama
-   Assign santri ke kamar
-   Scoped per pondok

---

### 9. CMS Website

**Route Prefix**: `/dashboard/cms`

| Modul       | Route               | Deskripsi                            |
| ----------- | ------------------- | ------------------------------------ |
| Berita      | `/cms/posts`        | Artikel/berita dengan editor WYSIWYG |
| Program     | `/cms/programs`     | Program unggulan pondok              |
| Galeri      | `/cms/galleries`    | Foto-foto kegiatan                   |
| Testimoni   | `/cms/testimonials` | Testimoni santri/wali                |
| FAQ         | `/cms/faqs`         | Pertanyaan umum                      |
| Web Setting | `/cms/settings`     | Logo, nama site, dll                 |

---

### 8. Pengaturan Surat

**Route**: `/dashboard/settings/letter`

**Fitur:**

-   Setting kop surat (nomor surat, tanggal)
-   Per pondok pesantren

---

### 11. Pendaftaran Santri Baru (PSB Online)

**Route**:

-   Public: `/register-santri`
-   Admin: `/dashboard/student-registrations`

**Fitur:**

-   **Public Form**:
    -   Form pendaftaran online dengan stepper (Biodata, Ortu/Wali, Akademik).
    -   Support data Wali (opsional) dan Sekolah Sebelumnya.
    -   Download otomatis: Surat Pernyataan & Formulir Pendaftaran (.docx).
-   **Admin Dashboard**:
    -   List pendaftar dengan filter status.
    -   Detail pendaftar lengkap.
    -   **Terima Santri**: Konversi data pendaftaran menjadi User & Student aktif.
    -   Cetak ulang Formulir Pendaftaran (Word).

---

## Middleware & Scoping

### Role Middleware

-   `role:super-admin` - Akses super admin only
-   `role:admin-pondok` - Akses admin pondok only

### Scope Middleware

-   `scope.boarding_school` - Batasi akses ke pondok yang ditugaskan
-   `scope.student` - Batasi akses ke santri di pondok sendiri
-   `scope.teacher` - Batasi akses ke pegawai di pondok sendiri

---

## Database Schema (Tabel Utama)

### Core Tables

| Tabel                    | Deskripsi             |
| ------------------------ | --------------------- |
| `users`                  | Semua user di sistem  |
| `boarding_schools`       | Data pondok pesantren |
| `admin_boarding_schools` | Pivot user-pondok     |
| `school_years`           | Tahun ajaran          |

### Student Tables

| Tabel                        | Deskripsi               |
| ---------------------------- | ----------------------- |
| `students`                   | Data santri             |
| `student_classrooms`         | Pivot santri-kelas      |
| `student_bed_rooms`          | Pivot santri-kamar      |
| `student_permissions`        | Riwayat izin            |
| `student_violations`         | Riwayat pelanggaran     |
| `student_memorizes`          | Catatan hafalan         |
| `diseases`                   | Rekam medis             |
| `student_withdraw_histories` | Riwayat transaksi saldo |
| `student_registrations`      | Data pendaftaran santri |

### Academic Tables

| Tabel           | Deskripsi        |
| --------------- | ---------------- |
| `classrooms`    | Data kelas       |
| `bed_rooms`     | Data kamar       |
| `teachers`      | Data pegawai     |
| `schools`       | Jenjang sekolah  |
| `school_levels` | Level di sekolah |

### CMS Tables

| Tabel           | Deskripsi          |
| --------------- | ------------------ |
| `posts`         | Berita/artikel     |
| `programs`      | Program pondok     |
| `galleries`     | Galeri foto        |
| `testimonials`  | Testimoni          |
| `faqs`          | FAQ                |
| `site_settings` | Pengaturan website |

---

## Alur Umum Sistem

```
┌─────────────────────────────────────────────────────────────────┐
│                         LANDING PAGE                             │
│                    (Public - No Auth)                            │
│  - Homepage                                                       │
│  - Berita/News                                                   │
│  - Detail Berita                                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼ LOGIN
┌─────────────────────────────────────────────────────────────────┐
│                           DASHBOARD                              │
├─────────────────────────────┬───────────────────────────────────┤
│       SUPER ADMIN           │          ADMIN PONDOK             │
├─────────────────────────────┼───────────────────────────────────┤
│ - Master Data               │ - Data Santri (CRUD + Detail)     │
│   - Jenjang Sekolah         │ - RFID Santri                     │
│   - Jabatan                 │ - Data Kelas                      │
│   - Tahun Ajaran            │ - Data Kamar                      │
│ - Pondok (Full CRUD)        │ - Data Pegawai                    │
│ - CMS                       │ - Pengaturan Surat                │
│   - Berita                  │ - Batas Penarikan                 │
│   - Program                 │                                   │
│   - Galeri                  │ *Semua data scoped ke pondok      │
│   - Testimoni               │  yang ditugaskan                  │
│   - FAQ                     │                                   │
│   - Web Setting             │                                   │
└─────────────────────────────┴───────────────────────────────────┘
```

---

## Fitur yang Belum Diimplementasikan

-   [ ] Finance: Tagihan (Invoice)
-   [ ] Finance: Riwayat Saldo (WithDraw History)
-   [ ] Finance: Tabungan (Savings)
-   [ ] PPOB Payment Integration
-   [ ] Letter/Surat Generation
-   [x] Student Registration (PSB Online)
-   [ ] Presensi/Absensi

---

## Konvensi Kode

### Backend

-   Controller: `StudentController.php`
-   Model: `Student.php`
-   Request: `StudentRequest.php`
-   Resource: `StudentResource.php`

### Frontend

-   Pages: `resources/js/Pages/Dashboard/Student/*.vue`
-   Components: `resources/js/Components/Student/*.vue`
-   Layouts: `resources/js/Layouts/*.vue`

### Filter Values

-   Gunakan `'all'` untuk opsi "Semua", bukan string kosong `''`
-   Default status santri: `'active'`

---

> ⚠️ **WAJIB: UPDATE DOKUMENTASI INI SETIAP ADA FITUR BARU!**
>
> Setiap kali ada fitur baru yang ditambahkan ke sistem, dokumentasi ini **HARUS** diperbarui dengan:
>
> 1. Menambahkan fitur ke section yang relevan
> 2. Menambahkan route baru ke tabel menu
> 3. Menambahkan tabel database baru (jika ada)
> 4. Menghapus item dari "Fitur yang Belum Diimplementasikan" jika sudah selesai
