# AGENTS.md — Pedoman & Aturan Permanen Pengembangan HRIS

Dokumen ini adalah acuan instruksi dan batasan desain wajib bagi seluruh AI Agent dan developer yang bekerja pada repositori **HRIS Corporate**. Seluruh aturan di bawah ini dipelajari dari koreksi dan preferensi pengguna agar konsisten dan tidak terjadi kesalahan berulang.

---

## 1. Aturan Tampilan & Desain Antarmuka (UI/UX)

### A. Larangan Menggunakan Subtitle / Caption Redundant di Bawah Judul Halaman
- **ATURAN**: DILARANG menampilkan subtitle / caption (`<p class="caption">` atau `@yield('page-subtitle')`) di bawah header judul halaman (`page-title`). Cukup tampilkan judul halaman yang bersih dan jelas tanpa caption tambahan yang berulang di semua halaman admin.
- **Rasional**: Judul halaman (`@yield('page-title')`) sudah mendeskripsikan konteks halaman secara ringkas dan lugas. Teks subtitle membuat area header terkesan bertumpuk dan tidak minimalis.
- **Larangan `truncate` pada Judul**: Pada seluruh elemen judul (`h1`, `h2`, `page-title`), DILARANG KERAS menyematkan class `truncate` agar judul tidak terpotong kaku menjadi elipsis `...`. Biarkan judul mengalir secara alami (*natural wrap*).
- **Penerapan Header Bersih**:
  ```blade
  {{-- BENAR: Bersih, tanpa subtitle/caption redundant --}}
  <div class="min-w-0">
      <h2 class="page-title">@yield('page-title', 'Dashboard')</h2>
  </div>
  ```

### B. Larangan Tipografi Angka "Warna-Warni" pada KPI & Kartu Metrik
- **ATURAN**: Seluruh angka nilai utama pada kartu KPI/Statistik (di Dashboard Monitoring, Rekap Presensi, dll.) **WAJIB menggunakan 1 warna seragam yang tenang dan elegan**: `text-hris-text`.
- **DILARANG**: Memberi warna angka KPI dengan `text-hris-success` (hijau), `text-hris-warning` (oranye), `text-hris-danger` (merah), atau `text-hris-info` (biru muda). Warna-warni teks angka membuat dashboard tampak ramai dan tidak berstandar enterprise.
- **Penggunaan Warna Semantik**: Warna hijau, merah, kuning, dsb. **HANYA** boleh digunakan pada pill badge status kecil (`pill-success`, `pill-danger`, dsb.), bukan pada tipografi angka KPI besar.
- **Struktur Kartu KPI iOS Style yang Konsisten**:
  ```blade
  {{-- Standar Kartu KPI Bergaya Apple iOS / visionOS --}}
  <div class="glass-card p-5 flex flex-col justify-between gap-3">
      <div class="flex items-center justify-between">
          <span class="kpi-label">Label Metrik</span>
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
              <x-icon name="clipboard-check" class="w-4 h-4" />
          </div>
      </div>
      <div class="flex flex-col gap-1">
          <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $metricValue }}</span>
          <span class="text-xs text-hris-text-secondary">Keterangan subteks pendukung</span>
      </div>
  </div>
  ```

### C. Konsistensi Penggunaan Icon di Seluruh Halaman
- **ATURAN**: Setiap kartu metrik, tombol aksi (*filter*, *reset*, *cari*, *simpan*, *batal*, *detail*, *edit*, *hapus*), dan tab navigasi **WAJIB menyertakan `<x-icon>`** yang relevan.
- Halaman **Rekap Presensi** dan halaman monitoring lainnya harus memiliki hierarki visual, container icon, dan tombol yang 100% konsisten dengan Dashboard Monitoring.

### D. Larangan Placeholder Foto Profil / Avatar Bulat di Tabel Data
- **ATURAN**: DILARANG menampilkan lingkaran inisial avatar (`<div class="avatar ...">`) pada baris tabel (Data Pengguna, Jadwal Shift, Rekap Presensi, Approval Cuti, Dashboard).
- **Rasional**: Lingkaran inisial menyerupai foto profil rusak/kosong dan membuat tabel data terlihat penuh sesak (*cluttered*).
- **Penerapan**: Tampilkan nama secara langsung dengan tipografi tebal yang bersih:
  ```blade
  {{-- BENAR --}}
  <td>
      <span class="font-bold text-hris-text">{{ $user->name }}</span>
  </td>
  ```

### E. Larangan Dialog Native Browser (`confirm` / `alert`)
- **ATURAN**: DILARANG menggunakan `window.confirm()`, `window.alert()`, atau `onclick="return confirm(...)"`.
- **Penerapan**: Selalu gunakan Custom Glassmorphism Confirmation Modal dengan deklaratif HTML data attributes:
  ```blade
  <form method="POST" action="..."
        data-confirm="Pesan konfirmasi?"
        data-confirm-title="Judul Dialog"
        data-confirm-message="Keterangan konsekuensi tindakan..."
        data-confirm-btn="Ya, Lanjutkan"
        data-confirm-variant="danger" {{-- atau 'primary' --}}
        data-confirm-icon="trash">
  ```

### F. Standardisasi Kapsul (Pill Badge) untuk Kolom Shift di Seluruh Tabel
- **ATURAN**: Penulisan nama shift kerja (seperti *Shift Pagi*, *Shift Siang*, *Shift Malam*) pada kolom tabel (Dashboard Admin, Rekap Presensi, Jadwal Shift, Master Shift) **WAJIB menggunakan kapsul (`pill-primary`) yang seragam**, berukuran proporsional dan identik dengan kapsul status (`pill-success`, `pill-warning`, dsb.).
- **DILARANG**: Menampilkan nama shift dengan teks polos tebal berukuran besar atau menumpuk teks jam kerja secara tidak seragam di dalam baris tabel data sehingga membuat tinggi baris tabel jomplang/tidak konsisten.
- **Penerapan**:
  ```blade
  {{-- BENAR: Kapsul konsisten dan seukuran kapsul status --}}
  <td>
      @if ($schedule)
          <span class="pill-primary" title="{{ $schedule->shift->time_range }}">{{ $schedule->shift->name }}</span>
      @else
          <span class="pill-muted">Tanpa Shift</span>
      @endif
  </td>
  ```

---

## 2. Aturan Backend, Database & Testing

### A. Sanitasi Nama Shift (Anti-UUID / Anti-Hash)
- Nama shift kerja harus selalu ramah dibaca manusia (contoh: *Shift Pagi*, *Shift Siang*, *Shift Malam*, *Shift Fleksibel*).
- Model `App\Models\Shift` memiliki mutator/accessor otomatis untuk menghapus kode hash/hex acak jika ada. Dilarang keras menampilkan akhiran hash/uuid teknis (seperti `6ad5d742b707c`) kepada pengguna.

### B. Idempotensi Pengujian & Anti-Polusi Database
- Pengujian otomatis (Unit & Feature Tests di `tests/`) **DILARANG** meninggalkan data dummy permanen di database lokal/produksi.
- Pengujian berbasis waktu wajib menggunakan mock waktu `Carbon::setTestNow(...)` yang dibungkus blok `try ... finally { Carbon::setTestNow(); }`, agar tidak perlu membuat shift tiruan (seperti "Shift Pagi Test") yang merusak data master shift.
