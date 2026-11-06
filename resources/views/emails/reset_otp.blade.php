<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kode OTP Reset Password</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1e293b">
  <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.04)">
    <div style="background:linear-gradient(135deg,#16120f 0%,#26201b 100%);padding:28px 24px;text-align:center">
      <h1 style="color:#ffffff;margin:0;font-size:22px;letter-spacing:-0.5px">RAPI-Sekolah</h1>
      <p style="color:#d4bda8;margin:6px 0 0;font-size:13px">Sistem Sarana &amp; Penjadwalan Terintegrasi</p>
    </div>
    <div style="padding:32px 28px">
      <h2 style="font-size:18px;margin:0 0 12px;color:#0f172a">Halo, {{ $user->fullname }}!</h2>
      <p style="font-size:14px;line-height:1.6;color:#475569;margin:0 0 24px">
        Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda (<b>{{ $user->username }}</b>). Gunakan kode verifikasi (OTP) berikut untuk melanjutkan:
      </p>

      <div style="text-align:center;background:#f1f5f9;border-radius:12px;padding:20px;margin:24px 0;border:1px dashed #cbd5e1">
        <span style="font-size:34px;font-weight:800;letter-spacing:8px;color:#1e293b;font-family:monospace">{{ $otp }}</span>
        <div style="font-size:12px;color:#64748b;margin-top:8px">Berlaku selama <b>15 menit</b></div>
      </div>

      <p style="font-size:13px;line-height:1.6;color:#64748b;margin:0 0 16px">
        Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini dan pastikan kata sandi akun Anda tetap aman.
      </p>
      <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0">
      <p style="font-size:11px;color:#94a3b8;margin:0;text-align:center">
        Email ini dikirim otomatis oleh sistem RAPI-Sekolah. Harap tidak membalas email ini.
      </p>
    </div>
  </div>
</body>
</html>

