# PROMPT: Sistem Keuangan Multi-Kas & Keluar Masuk Barang

Bertindaklah sebagai Senior Laravel Developer. Bangun aplikasi **Sistem Keuangan & Inventori** yang siap production dengan spesifikasi di bawah ini. Berikan kode lengkap (bukan pseudocode), sertakan semua import, dan cantumkan command artisan yang harus dijalankan.

---

## 1. Tech Stack

- **Backend:** Laravel 13, PHP 8.4, MySQL 8
- **Arsitektur:** API-first. Semua operasi data lewat REST API (`routes/api.php`), autentikasi **Laravel Sanctum** (SPA cookie-based untuk frontend Blade, token untuk akses eksternal/mobile).
- **Frontend:** Blade + TailwindCSS + Alpine.js. Blade hanya merender shell/layout; data diambil dan dikirim via `fetch()` ke API (gunakan Alpine `x-data` + komponen reusable).
- **Standar kode:** `declare(strict_types=1)`, type hint di semua property/parameter/return, PSR-12, Form Request untuk validasi, API Resource untuk response, Service class untuk business logic (controller tipis), Enum PHP 8 untuk tipe transaksi/mutasi.

## 2. Tema & Design System

Tema: **soft / lembut, nuansa keuangan profesional**. Mode **light dan dark** (Tailwind `darkMode: 'class'`, toggle disimpan di `localStorage`, default ikut `prefers-color-scheme`).

Definisikan palet sebagai CSS variables di `app.css` dan mapping ke Tailwind theme:

```css
:root {
  --color-primary:        #0F766E; /* hijau tua (teal-emerald) — aksi utama */
  --color-primary-soft:   #A7F3D0; /* hijau muda — badge, highlight, hover */
  --color-accent:         #1E3A5F; /* biru navy — heading, sidebar, angka saldo */
  --color-success:        #10B981; /* kas masuk */
  --color-danger:         #EF4444; /* kas keluar */
  --color-bg:             #F6FBF9; /* background lembut kehijauan */
  --color-surface:        #FFFFFF;
  --color-border:         #E2EFE9;
  --color-text:           #1E3A5F;
  --color-text-muted:     #64748B;
}
.dark {
  --color-primary:        #2DD4BF;
  --color-primary-soft:   #134E4A;
  --color-accent:         #93C5FD;
  --color-success:        #34D399;
  --color-danger:         #F87171;
  --color-bg:             #0B1726; /* navy gelap */
  --color-surface:        #122236;
  --color-border:         #1E3A5F;
  --color-text:           #E2E8F0;
  --color-text-muted:     #94A3B8;
}
```

Aturan UI:
- Mobile-first, prioritas tampilan portrait smartphone. Hindari tabel lebar di mobile — gunakan card layout, tabel hanya di breakpoint `md+`.
- **ATURAN WAJIB — CRUD via modal:** SEMUA operasi create, edit, delete/void, dan detail di SEMUA modul (kas, kategori, transaksi kas, transfer, barang, mutasi barang, tutup buku) menggunakan **modal Alpine.js** di halaman yang sama. **JANGAN membuat view/halaman Blade baru untuk form** — tidak ada halaman `create.blade.php` atau `edit.blade.php`, dan tidak ada redirect halaman. Alur: buka modal → submit via `fetch()` ke API → tutup modal → refresh data list tanpa reload halaman (state Alpine) → tampilkan toast sukses/gagal. Delete/void memakai modal konfirmasi (bukan `confirm()` bawaan browser). Buat satu komponen modal Blade reusable (`<x-modal>`) yang dipakai semua modul, dengan dukungan light/dark mode, animasi transisi, dan bisa ditutup via tombol close/backdrop/Escape.
- Komponen: sidebar/bottom-nav, card statistik (total saldo per kas), modal form (Alpine), toast notifikasi, skeleton loading saat fetch API.
- Sudut membulat (`rounded-2xl`), shadow lembut, transisi halus — kesan "soft finance app".

## 3. Modul Keuangan (Multi-Kas)

### Fitur
1. **Master Kas** — bisa membuat banyak kas/saldo (Kas Utama, Bank BCA, E-Wallet, dst). Field: kode, nama, tipe (`tunai|bank|ewallet`), saldo_awal, is_active.
2. **Kategori Transaksi** — jenis `masuk` / `keluar` (mis. Penjualan, Pembelian, Gaji, Listrik).
3. **Transaksi Kas** — masuk & keluar per kas, dengan tanggal, kategori, jumlah, keterangan.
4. **Transfer antar kas** — dibuat sebagai **2 baris transaksi berpasangan** (`transfer_keluar` di kas asal, `transfer_masuk` di kas tujuan) yang diikat `transfer_group` (UUID). Dibuat dan dibatalkan selalu berpasangan dalam satu DB transaction.
5. **Update saldo aman:** kolom `saldo_berjalan` di tabel `kas` adalah cache. Setiap insert/void transaksi dilakukan di dalam `DB::transaction()` dengan `lockForUpdate()` pada baris kas untuk mencegah race condition. Saldo tidak boleh diedit manual.
6. Penomoran otomatis: `TRX-YYYYMMDD-0001`.

### Laporan Cashflow (HTML)
Halaman laporan dengan filter **rentang tanggal** dan **pilihan kas** (satu kas atau semua kas):
- **Saldo Awal** dihitung via `LaporanService::saldoAwal(kasId, tanggalMulai)` dengan strategi snapshot (lihat modul 5):
  `saldo_akhir` snapshot periode terakhir **sebelum** bulan tanggal mulai + Σ(masuk − keluar) transaksi dari awal bulan tersebut sampai **sebelum** tanggal mulai. Jika belum ada snapshot, fallback ke `kas.saldo_awal` + Σ seluruh transaksi sebelum tanggal mulai.
- Daftar transaksi dalam rentang tanggal, dikelompokkan per tanggal, dengan kolom: tanggal, nomor, kategori, keterangan, masuk, keluar, **saldo berjalan**.
- **Total masuk, total keluar, dan Saldo Akhir** = saldo awal + (total masuk − total keluar).
- Jika "semua kas": tampilkan ringkasan per kas + gabungan.
- Tombol **Print** dengan stylesheet `@media print` (rapi di A4: header laporan, periode, tanpa sidebar/tombol).
- Endpoint API: `GET /api/laporan/cashflow?kas_id=&dari=&sampai=` mengembalikan JSON `{ saldo_awal, transaksi[], total_masuk, total_keluar, saldo_akhir }`.

## 4. Modul Keluar Masuk Barang — Pola Stock Ledger (WAJIB)

Gunakan pola **stock ledger / kartu stok**, bukan sekadar update kolom stok:

1. **Master Barang:** kode (SKU), nama, satuan, harga_beli, harga_jual, stok (cache), stok_minimum, is_active.
2. **Mutasi Barang** — setiap pergerakan dicatat sebagai baris ledger dengan tipe:
   - `masuk` — pembelian, retur penjualan, hasil produksi
   - `keluar` — penjualan, pemakaian, barang rusak
   - `penyesuaian` — stock opname (qty bisa menambah atau mengurangi)
   Field: nomor (`IN-YYYYMMDD-0001` / `OUT-...` / `ADJ-...`), barang_id, tanggal, tipe, qty (selalu positif, arah dari tipe), harga_satuan, **stok_setelah** (snapshot saldo stok setelah mutasi → audit trail), referensi (no. faktur/nota), keterangan, user_id.
3. **Aturan:** stok diupdate hanya lewat Service di dalam `DB::transaction()` + `lockForUpdate()` pada baris barang. Tolak `keluar` jika stok tidak cukup (validasi di service, bukan hanya di request). `harga_beli` barang diupdate ke harga beli terakhir saat mutasi masuk.
4. **Kartu Stok per barang** dengan filter tanggal — pola sama persis dengan cashflow: stok awal, daftar mutasi, stok akhir.
5. **Alert stok menipis:** daftar barang dengan `stok <= stok_minimum` di dashboard.
6. **Integrasi opsional ke kas:** saat mencatat barang masuk (pembelian) atau keluar (penjualan), user bisa memilih kas → sistem otomatis membuat transaksi kas keluar/masuk yang ter-link ke mutasi via relasi polymorphic (`sumber_type`, `sumber_id`). Void mutasi ikut mem-void transaksi kasnya dalam satu DB transaction.

## 5. Modul Snapshot Saldo Periode & Tutup Buku (WAJIB)

Untuk performa laporan jangka panjang dan kontrol akuntansi, terapkan **snapshot saldo per periode bulanan** untuk kas **dan** stok barang, plus fitur **tutup buku**:

### Tabel snapshot
- `saldo_periode` — satu baris per kas per bulan: `kas_id`, `periode` (DATE, selalu tanggal 1), `saldo_akhir` (saldo penutupan akhir bulan tsb), `total_masuk`, `total_keluar`, `is_closed`, `closed_at`, `closed_by`. Unique `(kas_id, periode)`.
- `stok_periode` — pola sama untuk barang: `barang_id`, `periode`, `stok_akhir`, `total_masuk`, `total_keluar`, `is_closed`. Unique `(barang_id, periode)`.

### Aturan perhitungan saldo awal (berlaku untuk cashflow & kartu stok)
```
saldoAwal(kasId, tanggal):
  snapshot = saldo_periode bulan terakhir SEBELUM bulan(tanggal) untuk kas tsb
  basis    = snapshot ? snapshot.saldo_akhir : kas.saldo_awal
  dariTgl  = snapshot ? awal bulan setelah snapshot.periode : awal data
  return basis + SUM(masuk - keluar) transaksi kas tsb
                 WHERE tanggal >= dariTgl AND tanggal < tanggal
```
Dengan ini, SUM maksimal hanya scan transaksi beberapa bulan terakhir yang belum di-snapshot — bukan seluruh riwayat. Implementasikan di `LaporanService` dan pakai method yang sama di cashflow maupun kartu stok (jangan duplikasi logika).

### Tutup buku & proteksi backdate
1. `POST /api/periode/tutup` body `{periode}` → menghitung & menyimpan/finalisasi snapshot **semua kas dan semua barang** untuk bulan tsb, set `is_closed = true`. Hanya boleh menutup bulan jika bulan-bulan sebelumnya sudah ditutup (berurutan).
2. **Setiap create/void/edit transaksi kas dan mutasi barang WAJIB ditolak (HTTP 409) jika `tanggal` jatuh pada periode yang sudah `is_closed`.** Validasi ini di Service, bukan hanya di Form Request.
3. `POST /api/periode/buka` → buka kembali periode terakhir yang ditutup (hanya role admin, hanya bisa berurutan dari yang paling akhir).

### Recalc otomatis (menjaga konsistensi snapshot)
1. Buat job `RecalcSnapshotPeriode` (queued): menghitung ulang snapshot sebuah kas/barang mulai dari periode tertentu sampai periode terbaru yang ada, berurutan (saldo akhir bulan N jadi basis bulan N+1).
2. Job ini **otomatis di-dispatch** setiap kali ada create/void transaksi atau mutasi yang tanggalnya jatuh pada bulan yang sudah punya snapshot tapi belum `is_closed` (snapshot bulan berjalan/belum dikunci).
3. Buat command artisan `php artisan periode:rebuild {--kas=} {--barang=} {--dari=YYYY-MM}` untuk membangun ulang seluruh snapshot dari ledger (recovery tool — *source of truth* tetap tabel transaksi/mutasi, snapshot selalu bisa diregenerasi).
4. Scheduler: setiap tanggal 1 jam 00:05, generate snapshot draft (belum closed) untuk bulan yang baru lewat.

## 6. Struktur Database

```
kas:                id, kode(unique), nama, tipe, saldo_awal, saldo_berjalan,
                    is_active, timestamps
kategori_transaksi: id, nama, jenis(masuk|keluar), is_active, timestamps
transaksi_kas:      id, nomor(unique), kas_id(FK), kategori_id(FK null),
                    tanggal, tipe(masuk|keluar|transfer_masuk|transfer_keluar),
                    jumlah, keterangan, transfer_group(uuid null),
                    sumber_type, sumber_id (nullableMorphs),
                    user_id(FK null), timestamps
barang:             id, kode(unique), nama, satuan, harga_beli, harga_jual,
                    stok, stok_minimum, is_active, timestamps
mutasi_barang:      id, nomor(unique), barang_id(FK), tanggal,
                    tipe(masuk|keluar|penyesuaian), qty, harga_satuan,
                    stok_setelah, referensi, keterangan, user_id(FK null),
                    timestamps
saldo_periode:      id, kas_id(FK), periode(date, tgl 1), saldo_akhir,
                    total_masuk, total_keluar, is_closed, closed_at,
                    closed_by(FK users null), timestamps
                    unique(kas_id, periode)
stok_periode:       id, barang_id(FK), periode(date, tgl 1), stok_akhir,
                    total_masuk, total_keluar, is_closed, timestamps
                    unique(barang_id, periode)
```

Wajib: foreign key (`restrictOnDelete` untuk kas_id/barang_id), tipe uang `decimal(18,2)`, qty `decimal(14,2)`, dan index komposit untuk laporan: `(kas_id, tanggal)`, `(barang_id, tanggal)`, `(tanggal, tipe)`, serta `(kas_id, periode)` / `(barang_id, periode)` pada tabel snapshot.

## 7. Endpoint API (RESTful)

```
POST   /api/login, /api/logout (Sanctum)
GET|POST|PUT|DELETE /api/kas
GET|POST|PUT|DELETE /api/kategori-transaksi
GET|POST            /api/transaksi-kas        (DELETE = void, bukan hard delete fisik tanpa jejak)
POST                /api/transaksi-kas/transfer
GET                 /api/laporan/cashflow?kas_id=&dari=&sampai=
GET|POST|PUT|DELETE /api/barang
GET|POST            /api/mutasi-barang
GET                 /api/laporan/kartu-stok?barang_id=&dari=&sampai=
GET                 /api/periode               (daftar periode + status closed)
POST                /api/periode/tutup         (admin)
POST                /api/periode/buka          (admin, hanya periode terakhir)
GET                 /api/dashboard            (total saldo per kas, stok menipis, ringkasan bulan ini)
```

Gunakan HTTP status code yang tepat (201 create, 422 validasi, 409 untuk stok tidak cukup **dan untuk transaksi pada periode yang sudah ditutup**), pagination untuk semua list, eager loading untuk relasi (hindari N+1).

## 8. Halaman Blade

1. Login
2. Dashboard (card saldo per kas, ringkasan masuk/keluar bulan ini, alert stok menipis)
3. Master Kas, Kategori, Barang (CRUD via modal)
4. Transaksi Kas (list + modal form masuk/keluar + modal transfer antar kas)
5. Mutasi Barang (list + modal form masuk/keluar/penyesuaian, opsi link ke kas)
6. Laporan Cashflow (filter tanggal & kas, printable)
7. Kartu Stok (filter tanggal & barang, printable)
8. Tutup Buku (daftar periode per bulan dengan status terbuka/tertutup, tombol tutup/buka periode dengan konfirmasi, ringkasan saldo akhir semua kas per periode)

Semua halaman mendukung light/dark mode dan responsif mobile. Satu halaman per modul — semua form CRUD muncul sebagai modal di halaman list-nya, tanpa view baru dan tanpa reload halaman. Pada form transaksi/mutasi, tampilkan peringatan jika tanggal yang dipilih jatuh pada periode tertutup (cek via API) sebelum user submit.

## 9. Keamanan & Performa

- Validasi semua input via Form Request; authorization via middleware `auth:sanctum`.
- Tidak ada raw SQL kecuali benar-benar perlu; gunakan query builder/Eloquent.
- Semua perubahan saldo & stok dalam `DB::transaction()` + `lockForUpdate()`.
- Rate limiting pada API, CSRF untuk SPA cookie flow.
- Seeder: 1 user admin, 3 kas contoh, kategori dasar, 5 barang contoh.

## 10. Urutan Pengerjaan & Deliverables

1. Migrations + Seeders
2. Enums (`TipeTransaksi`, `TipeMutasi`) + Models (relasi lengkap, casts)
3. Services (`KasService`, `TransferService`, `BarangService`, `LaporanService`, `PeriodeService`)
4. Job `RecalcSnapshotPeriode` + command `periode:rebuild` + scheduler
5. Form Requests + API Resources
6. Controllers + Routes (api.php & web.php)
7. Layout Blade (dark mode toggle) + semua halaman
8. Daftar command artisan untuk setup (`migrate`, `db:seed`, `queue:work`, dll.)

Kriteria selesai: transfer antar kas selalu berpasangan dan saldo kedua kas konsisten; cashflow & kartu stok menampilkan saldo awal/akhir yang benar sesuai rentang tanggal **dan hasilnya identik antara perhitungan via snapshot maupun via SUM penuh dari ledger** (buat test untuk membuktikan ini); transaksi/mutasi pada periode tertutup ditolak dengan 409; transaksi backdate pada bulan ber-snapshot yang belum ditutup memicu recalc otomatis; `periode:rebuild` mampu meregenerasi seluruh snapshot dari ledger; mutasi keluar ditolak saat stok kurang; kartu stok cocok dengan kolom `stok_setelah`; UI berfungsi di light & dark mode.
