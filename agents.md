# AGENTS.md — Pedoman & Aturan Permanen Pengembangan HRIS

Dokumen ini adalah acuan instruksi dan batasan desain wajib bagi seluruh AI Agent dan developer yang bekerja pada repositori **HRIS Corporate**. Seluruh aturan di bawah ini dipelajari dari koreksi dan preferensi pengguna agar konsisten dan tidak terjadi kesalahan berulang.

---

## 1. Aturan Tampilan & Desain Antarmuka (UI/UX)

### A. Dilarang Menggunakan `truncate` pada Judul & Subtitle
- **ATURAN**: DILARANG KERAS menyematkan class `truncate` pada elemen `<p class="caption">` atau `@yield('page-subtitle')` dan `@yield('page-title')`.
- **Rasional**: `truncate` memotong teks subtitle penjelasan secara kaku menjadi elipsis `...`, merusak keterbacaan instruksi di berbagai ukuran layar.
- **Penerapan**: Biarkan judul dan subtitle mengalir secara alami (*natural wrap*):
  ```blade
  {{-- BENAR --}}
  <h2 class="page-title">@yield('page-title', 'Dashboard')</h2>
  @hasSection('page-subtitle')
      <p class="caption">@yield('page-subtitle')</p>
  @endif
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

---

## 2. Aturan Backend, Database & Testing

### A. Sanitasi Nama Shift (Anti-UUID / Anti-Hash)
- Nama shift kerja harus selalu ramah dibaca manusia (contoh: *Shift Pagi*, *Shift Siang*, *Shift Malam*, *Shift Fleksibel*).
- Model `App\Models\Shift` memiliki mutator/accessor otomatis untuk menghapus kode hash/hex acak jika ada. Dilarang keras menampilkan akhiran hash/uuid teknis (seperti `6ad5d742b707c`) kepada pengguna.

### B. Idempotensi Pengujian & Anti-Polusi Database
- Pengujian otomatis (Unit & Feature Tests di `tests/`) **DILARANG** meninggalkan data dummy permanen di database lokal/produksi.
- Pengujian berbasis waktu wajib menggunakan mock waktu `Carbon::setTestNow(...)` yang dibungkus blok `try ... finally { Carbon::setTestNow(); }`, agar tidak perlu membuat shift tiruan (seperti "Shift Pagi Test") yang merusak data master shift.
