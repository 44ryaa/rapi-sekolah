# Rapi Sekolah — Mobile App (Android)

Aplikasi mobile Android untuk sistem informasi pengelolaan sarana dan prasarana sekolah **Rapi Sekolah**, dibangun menggunakan **Flutter** dan terhubung ke backend **Laravel REST API (Sanctum)**.

---

## 📱 Fitur Utama Aplikasi

1. **Autentikasi & Akun**
   - Login dengan username & password akun sekolah (Admin, Waka Sarpras, PIC Ruang, Guru).
   - Token Bearer Sanctum otomatis tersimpan lokal secara aman.
   - Konfigurasi alamat IP host backend langsung dari layar login / profil tanpa perlu recompile code.

2. **Pengaduan Kerusakan Fasilitas (Dengan Kamera HP)**
   - Form pelaporan kerusakan sarana prasarana.
   - Ambil foto bukti kerusakan langsung menggunakan **kamera fisik HP** atau memilih dari **galeri**.
   - Pratinjau foto sebelum dikirim.
   - Upload multipart aman ke server backend Laravel.

3. **Peminjaman & Jadwal Ruangan**
   - Daftar seluruh ruangan sekolah lengkap dengan kapasitas, lokasi, dan PIC.
   - Cek ketersediaan sesi secara real-time (Sesi 1–5 atau Sesi Penuh).
   - Form booking ruangan dengan validasi tanggal dan bentrok jadwal.

4. **Penanganan Aduan untuk PIC Ruang**
   - Update status perbaikan (*Belum Diperbaiki*, *Menunggu Spare Part*, *Sudah Diperbaiki*).
   - Input catatan penanganan perbaikan.

5. **Rekap & Laporan Statistik**
   - Rekap harian, mingguan, dan bulanan untuk data kegiatan dan pengaduan sarana.

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Menjalankan Backend Laravel
Pastikan server backend Laravel berjalan dan bisa diakses oleh HP:
```bash
cd c:\Users\acer\Downloads\rapi-sekolah
php artisan serve --host=0.0.0.0 --port=8000
```
> **Catatan IP:** 
> - Jika menggunakan **Android Emulator**: Alamat server adalah `http://10.0.2.2:8000`
> - Jika menggunakan **HP Fisik Android**: Pastikan HP dan laptop terhubung ke Wi-Fi yang sama, lalu gunakan IP laptop Anda (misal `http://192.168.1.15:8000`).

### 2. Menjalankan di Android
Hubungkan HP Android dengan kabel USB (aktifkan *USB Debugging* di menu Developer Options HP), atau jalankan Android Emulator:
```bash
cd mobile_app
flutter run
```

### 3. Build File APK Siap Pasang
Untuk menghasilkan file installer `.apk` yang bisa langsung dikirim ke HP:
```bash
cd mobile_app
flutter build apk --release
```
File APK akan berada di folder:
`mobile_app/build/app/outputs/flutter-apk/app-release.apk`
