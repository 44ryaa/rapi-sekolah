# Diagram Perancangan RAPI-Sekolah

Berisi: Use Case Diagram, DFD Level 0 (Diagram Konteks), ERD, dan tiga Flowchart alur utama.

## 1. Use Case Diagram

```mermaid
flowchart LR
  T(["Siswa / Tamu"])
  U(["Pengguna Login"])
  G(["Guru / Organisasi"])
  P(["PIC Ruang"])
  A(["Waka / Admin"])
  G -->|is-a| U
  P -->|is-a| U
  A -->|is-a| U

  subgraph SYS["Sistem RAPI-Sekolah"]
    direction TB
    UC1(["Lihat kalender & agenda harian (read-only)"])
    UC2(["Login"])
    UC3(["Logout"])
    UC4(["Lupa password"])
    UC5(["Kelola profil & ganti password"])
    UC6(["Ajukan peminjaman ruang & agenda"])
    UC7(["Validasi tabrakan waktu"])
    UC8(["Input pengaduan kerusakan + unggah foto"])
    UC9(["Terima notifikasi ruang dipindah"])
    UC10(["Kelola status perbaikan fasilitas"])
    UC11(["Kirim peringatan via WhatsApp"])
    UC12(["Pindah ruang terpinjam + alasan"])
    UC13(["Buat log notifikasi otomatis"])
    UC14(["Setujui / tolak pengajuan"])
    UC15(["Kelola master data: pengguna, ruang, penugasan PIC"])
    UC16(["Verifikasi hak PIC atas ruangan"])
  end

  T --- UC1
  U --- UC1
  U --- UC2
  U --- UC3
  U --- UC4
  U --- UC5
  G --- UC6
  G --- UC8
  G --- UC9
  P --- UC10
  A --- UC6
  A --- UC11
  A --- UC12
  A --- UC14
  A --- UC15

  UC6 -.->|include| UC7
  UC10 -.->|include| UC16
  UC12 -.->|include| UC7
  UC12 -.->|include| UC13
  UC11 -.->|extend: pengaduan Belum Diperbaiki melewati batas waktu| UC10
```

## 2. DFD Level 0 (Diagram Konteks)

```mermaid
flowchart LR
  A["Waka / Admin"]
  P["PIC Ruang"]
  G["Guru / Organisasi"]
  T["Siswa / Tamu"]
  W["WhatsApp (wa.me)"]
  S(("0<br/>Sistem<br/>RAPI-Sekolah"))

  A -->|"data master, persetujuan, perintah pindah ruang + alasan, perintah peringatan"| S
  S -->|"daftar agenda, daftar pengaduan, status perbaikan"| A

  P -->|"pembaruan status perbaikan + catatan"| S
  S -->|"pengaduan di ruang tanggung jawab, notifikasi pengaduan baru"| P

  G -->|"pengajuan agenda, pengaduan + foto bukti, login"| S
  S -->|"status pengajuan, notifikasi pindah ruang + alasan, kalender"| G

  T -->|"permintaan lihat kalender"| S
  S -->|"kalender agenda (read-only)"| T

  S -->|"tautan pesan peringatan (nomor PIC + teks)"| W
  W -->|"pesan terkirim ke PIC (aksi Waka)"| P
```

## 3. ERD (Entity Relationship Diagram)

```mermaid
erDiagram
  roles ||--o{ users : "memiliki"
  users ||--o{ rooms : "PIC bertanggung jawab"
  users ||--o{ events : "mengajukan (pic_id)"
  users |o--o{ events : "menyetujui (approved_by)"
  rooms ||--o{ events : "dipinjam untuk"
  rooms ||--o{ complaints : "dilaporkan rusak"
  users ||--o{ complaints : "melapor (reporter_id)"
  users |o--o{ complaints : "menangani (handler_id)"
  users ||--o{ notifications : "menerima"
  users ||--o{ password_resets : "meminta reset"

  roles {
    tinyint id PK
    varchar role_name UK
  }
  users {
    int id PK
    varchar username UK
    varchar password "bcrypt"
    varchar fullname
    varchar phone_number
    tinyint role_id FK
    timestamp created_at
  }
  rooms {
    int id PK
    varchar room_name UK
    smallint capacity
    varchar location
    int pic_id FK
    tinyint is_active
  }
  events {
    int id PK
    int room_id FK
    int pic_id FK
    varchar event_name
    date date
    time start_time
    time end_time
    enum status "Diajukan, Disetujui, Ditolak"
    int approved_by FK
    text move_reason
    timestamp created_at
  }
  complaints {
    int id PK
    int room_id FK
    int reporter_id FK
    text description
    varchar photo_path
    enum status_repair "Belum Diperbaiki, Menunggu Spare Part, Sudah Diperbaiki"
    int handler_id FK
    text repair_note
    datetime reported_at
    datetime updated_at
  }
  notifications {
    int id PK
    int user_id FK
    text message
    tinyint is_read
    timestamp created_at
  }
  password_resets {
    int id PK
    int user_id FK
    char token_hash UK
    datetime expires_at
  }
```

Tabel `notifications` dan `password_resets` adalah tambahan di luar 5 tabel wajib, untuk log notifikasi otomatis dan fitur lupa password.

## 4. Flowchart

### 4a. Alur masuk dan hak akses

```mermaid
flowchart TD
  S([Mulai]) --> K["Buka RAPI-Sekolah"]
  K --> L{"Sudah login?"}
  L -- Belum --> PUB["Kalender publik (read-only)"]
  PUB --> M{"Ingin masuk?"}
  M -- Tidak --> E([Selesai])
  M -- Ya --> LG["Halaman login"]
  LG --> F{"Lupa password?"}
  F -- Ya --> V["Isi username + nomor HP terdaftar"]
  V --> VC{"Data cocok?"}
  VC -- Tidak --> V
  VC -- Ya --> RS["Buat password baru (token 30 menit, sekali pakai)"] --> LG
  F -- Tidak --> CR["Isi username + password"]
  CR --> CV{"Kredensial valid dan belum terkunci?"}
  CV -- Tidak --> LG
  CV -- Ya --> R{"Role pengguna"}
  L -- Sudah --> R
  R -- "Waka/Admin" --> A1["Agenda, Pengaduan, Ruang, Pengguna"]
  R -- "PIC Ruang" --> P1["Pengaduan ruangan sendiri"]
  R -- "Guru/Organisasi" --> G1["Agenda saya, Laporkan kerusakan"]
  A1 --> LO["Logout (POST + CSRF)"]
  P1 --> LO
  G1 --> LO
  LO --> E
```

### 4b. Peminjaman ruang dan override Waka

```mermaid
flowchart TD
  S([Mulai]) --> F["Isi nama kegiatan, ruang, tanggal, jam mulai-selesai"]
  F --> UI["UI menampilkan jam terisi dan mengunci tombol bila bentrok"]
  UI --> V1{"Input valid? (tanggal >= hari ini, selesai > mulai)"}
  V1 -- Tidak --> F
  V1 -- Ya --> TX["Mulai transaksi dan kunci baris ruang (FOR UPDATE)"]
  TX --> C{"Ada agenda Diajukan/Disetujui di ruang + tanggal sama dengan mulai_baru < selesai_lama DAN selesai_baru > mulai_lama?"}
  C -- Ya --> X["Rollback dan tolak: tampilkan agenda yang bentrok"] --> F
  C -- Tidak --> W{"Peminjam adalah Waka?"}
  W -- Ya --> AP["Simpan status Disetujui"]
  W -- Tidak --> DJ["Simpan status Diajukan (slot tetap terkunci)"]
  DJ --> RV{"Waka meninjau"}
  RV -- Setuju --> AP2["Status Disetujui + notifikasi ke peminjam"]
  RV -- Tolak --> TL["Status Ditolak, slot dilepas + notifikasi"]
  AP --> CM["Commit dan tampil di kalender"]
  AP2 --> CM
  CM --> OV{"Waka perlu memindahkan ruang?"}
  OV -- Tidak --> E([Selesai])
  OV -- Ya --> MV["Pilih ruang tujuan + isi alasan (wajib)"]
  MV --> CC{"Ruang tujuan bebas pada jam tersebut?"}
  CC -- Tidak --> MV
  CC -- Ya --> UP["Update room_id dan isi move_reason otomatis"]
  UP --> NT["Buat log notifikasi: ruang baru + alasan"]
  NT --> PP["Pop-up muncul saat peminjam membuka aplikasi"] --> E
  TL --> E
```

### 4c. Pengaduan kerusakan dan eskalasi WhatsApp

```mermaid
flowchart TD
  S([Mulai]) --> I["Guru pilih ruang, jelaskan kerusakan, ambil foto dari kamera"]
  I --> V{"Foto JPG/PNG/WebP valid, maks 8 MB?"}
  V -- Tidak --> I
  V -- Ya --> K["Server memutar sesuai EXIF, memperkecil max 1280 px, kompres JPEG 75%, simpan"]
  K --> SV["Simpan pengaduan: status Belum Diperbaiki"]
  SV --> N1["Notifikasi ke PIC pemegang ruang"]
  N1 --> PIC["PIC melihat pengaduan di ruangannya saja"]
  PIC --> OK{"PIC memegang ruangan ini?"}
  OK -- Tidak --> DN["Ditolak 403"]
  OK -- Ya --> ST{"Perbarui status"}
  ST -- "Menunggu Spare Part" --> U1["Simpan status + catatan, notifikasi ke pelapor"]
  ST -- "Sudah Diperbaiki" --> U2["Simpan status + catatan, notifikasi ke pelapor"]
  ST -- "Belum Diperbaiki" --> T{"Lewat batas 24 jam?"}
  T -- Tidak --> WT["Tombol WhatsApp Waka nonaktif"]
  T -- Ya --> WA["Tombol hijau aktif: wa.me/nomor PIC?text=pesan"]
  WA --> KR["Waka menekan tombol, WhatsApp terbuka dengan pesan siap kirim"]
  KR --> PIC
  U1 --> E([Selesai])
  U2 --> E
  WT --> E
  DN --> E
```
