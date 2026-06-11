# Dokumentasi API Bincofarm

Referensi lengkap REST API Bincofarm (aplikasi keuangan/kas & inventori barang) untuk pengembangan aplikasi mobile **Flutter**.

> Dokumen ini dihasilkan langsung dari kode sumber (`routes/api.php`, controller, FormRequest, Resource, model, dan migration) per **2026-06-11**. Jika kode berubah, dokumen ini harus diperbarui.

---

## Daftar Isi

1. [Informasi Umum](#1-informasi-umum)
2. [Autentikasi](#2-autentikasi)
3. [Format Error](#3-format-error)
4. [Format Pagination](#4-format-pagination)
5. [Konvensi Format Data](#5-konvensi-format-data)
6. [Endpoint — Auth](#6-auth)
7. [Endpoint — Dashboard](#7-dashboard)
8. [Endpoint — Kas](#8-kas)
9. [Endpoint — Kategori Transaksi](#9-kategori-transaksi)
10. [Endpoint — Transaksi Kas](#10-transaksi-kas)
11. [Endpoint — Barang](#11-barang)
12. [Endpoint — Mutasi Barang](#12-mutasi-barang)
13. [Endpoint — Laporan](#13-laporan)
14. [Endpoint — Periode](#14-periode)
15. [Endpoint — User Profile](#15-user-profile)
16. [Endpoint — Activity Log](#16-activity-log)
17. [Schema Objek JSON (untuk model Dart)](#17-schema-objek-json-untuk-model-dart)
18. [Catatan Integrasi Flutter](#18-catatan-integrasi-flutter)

---

## 1. Informasi Umum

| Hal | Nilai |
|---|---|
| Base URL | `{{BASE_URL}}/api` (contoh dev: `http://10.0.2.2:8000/api` dari Android emulator) |
| Format | JSON (request & response) |
| Autentikasi | Laravel Sanctum — **Bearer token** untuk mobile |
| Timezone server | UTC |
| Locale validasi | `en` (pesan error bawaan validasi berbahasa Inggris; pesan bisnis berbahasa Indonesia) |

**Header wajib di setiap request:**

```
Accept: application/json
Content-Type: application/json        (untuk request dengan body)
Authorization: Bearer {token}         (untuk semua endpoint ber-auth)
```

> Backend memakai `statefulApi()` (Sanctum SPA mode) untuk web. Aplikasi Flutter **tidak** mengirim header `Origin`/`Referer` dari domain stateful, sehingga otomatis diperlakukan stateless dan memakai Bearer token. Jangan mengelola cookie/CSRF di Flutter.

---

## 2. Autentikasi

Alur untuk mobile (token-based):

1. `POST /api/login` dengan `login` (email atau no. HP), `password`, dan `device_name` → response berisi `user` dan `token`.
2. Simpan `token` secara aman (lihat [Catatan Integrasi Flutter](#18-catatan-integrasi-flutter)).
3. Kirim header `Authorization: Bearer {token}` di **semua** request berikutnya.
4. `POST /api/logout` → token yang sedang dipakai di-revoke (dihapus dari server).
5. Token **tidak memiliki masa kedaluwarsa** (`expiration: null` di config Sanctum), tetapi bisa di-revoke kapan saja — selalu tangani response 401.

Role user: `admin` | `kasir`. Endpoint tutup/buka periode hanya untuk `admin`; selebihnya bisa diakses semua user ber-auth.

---

## 3. Format Error

Semua error dikembalikan sebagai JSON (request ke `api/*` selalu dirender JSON oleh exception handler).

| Status | Kapan terjadi | Bentuk body |
|---|---|---|
| `401` | Token tidak ada / tidak valid / sudah di-revoke | `{"message": "Unauthenticated."}` |
| `403` | Akses ditolak (mis. non-admin menutup periode) | `{"message": "Hanya admin yang bisa menutup periode."}` |
| `404` | Resource tidak ditemukan (route model binding) | `{"message": "No query results for model [App\\Models\\Kas] 999."}` |
| `409` | Konflik aturan bisnis (periode ditutup, stok tidak cukup, sudah di-void) | `{"message": "Stok tidak cukup. Stok tersedia: 5.00 karung."}` |
| `422` | Validasi gagal | lihat di bawah |
| `500` | Error server | `{"message": "Server Error"}` |

**Bentuk error validasi (422):**

```json
{
  "message": "The email field is required. (and 1 more error)",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

Catatan: beberapa error bisnis dilempar sebagai `HttpException` 422 **tanpa** key `errors`, hanya `{"message": "..."}` (mis. "Kas asal dan tujuan tidak boleh sama."). Parser error di Flutter harus menganggap `errors` opsional.

---

## 4. Format Pagination

Endpoint list berikut memakai paginasi standar Laravel Resource: `GET /kas`, `GET /kategori-transaksi`, `GET /transaksi-kas`, `GET /barang`, `GET /mutasi-barang`.

Query parameter: `page` (default 1) dan `per_page` (default berbeda per endpoint, lihat masing-masing).

```json
{
  "data": [ { "...": "..." } ],
  "links": {
    "first": "{{BASE_URL}}/api/kas?page=1",
    "last": "{{BASE_URL}}/api/kas?page=3",
    "prev": null,
    "next": "{{BASE_URL}}/api/kas?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "links": [
      { "url": null, "label": "pagination.previous", "page": null, "active": false },
      { "url": "{{BASE_URL}}/api/kas?page=1", "label": "1", "page": 1, "active": true },
      { "url": "{{BASE_URL}}/api/kas?page=2", "label": "pagination.next", "page": 2, "active": false }
    ],
    "path": "{{BASE_URL}}/api/kas",
    "per_page": 50,
    "to": 50,
    "total": 120
  }
}
```

**Pengecualian:** `GET /activity-log` memakai format paginasi kustom yang lebih sederhana (lihat [bagian 16](#16-activity-log)).

---

## 5. Konvensi Format Data

| Jenis data | Format JSON | Contoh | Catatan |
|---|---|---|---|
| ID | integer | `1` | auto-increment |
| Uang / saldo / harga / jumlah | **number** (float) | `1500000.5` | DB `decimal(18,2)`; Resource meng-cast ke float. Gunakan `double` di Dart |
| Qty / stok | **number** (float) | `25.5` | DB `decimal(14,2)`; mendukung pecahan |
| Tanggal transaksi (`tanggal`) | string `"Y-m-d"` | `"2026-06-11"` | |
| Timestamp (`created_at`, `void_at`, `closed_at`, dll) | string ISO 8601 UTC | `"2026-06-11T08:30:00.000000Z"` | atau `null` |
| Periode | string `"Y-m"` | `"2026-06"` | |
| Boolean | `true` / `false` | | |
| Field kosong | `null` | | field nullable ditandai di tiap schema |

**Perilaku key relasi (penting):** field relasi (`kas`, `kategori`, `barang`, `user`) memakai `whenLoaded` —

- relasi **tidak dimuat** oleh endpoint → **key tidak ada sama sekali** di JSON;
- relasi dimuat tapi kosong → nilai `null` (untuk `kategori`) atau `{}`;

sehingga di Dart semua field relasi harus nullable dan dibaca dengan aman.

**Perilaku wrapper `data` (penting):**

- `GET` list (paginated) → dibungkus `{"data": [...], "links": ..., "meta": ...}`
- `GET` detail dan `PUT` update → dibungkus `{"data": {...}}`
- `POST` create (201) → objek **langsung tanpa wrapper**

---

## 6. Auth

### POST `/login` — Login

Auth: **tidak perlu**.

Request body:

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `login` | string | ya* | email **atau** no. HP. Format HP fleksibel: `0812 3456 7890`, `0812-3456-7890`, dan `+62812...` dianggap sama |
| `email` | string | ya* | alias lama dari `login`, tetap didukung untuk kompatibilitas |
| `password` | string | ya | |
| `device_name` | string | tidak | max 100; nama token, mis. `"Pixel 7 - Android"`. Default `"mobile"` |

\* Kirim salah satu dari `login` atau `email`.

Response `200`:

```json
{
  "user": {
    "id": 1,
    "name": "Admin Binco",
    "email": "admin@bincofarm.test",
    "phone": "081234567890",
    "avatar": null,
    "email_verified_at": null,
    "role": "admin",
    "created_at": "2026-06-10T15:19:56.000000Z",
    "updated_at": "2026-06-10T15:19:56.000000Z"
  },
  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789abcd"
}
```

> Key `token` hanya ada pada request stateless (mobile). Jika request datang dari web SPA (stateful, ber-session), response hanya berisi `user`.

Error `422` (kredensial salah):

```json
{
  "message": "Email/No. HP atau password salah.",
  "errors": { "login": ["Email/No. HP atau password salah."] }
}
```

### POST `/logout` — Logout

Auth: **ya**. Tanpa body. Me-revoke token yang sedang dipakai.

Response `200`: `{"message": "Berhasil logout."}`

### GET `/me` — User yang sedang login

Auth: **ya**.

Response `200`: `{"user": { ...objek User... }}`

---

## 7. Dashboard

### GET `/dashboard` — Ringkasan dashboard

Auth: **ya**. Tanpa parameter. Ringkasan **bulan berjalan** untuk semua kas aktif + daftar barang yang stoknya menipis.

Response `200`:

```json
{
  "total_saldo": 15750000,
  "kas": [
    {
      "kas": {
        "id": 1,
        "kode": "KAS-001",
        "nama": "Kas Tunai Toko",
        "tipe": "tunai",
        "tipe_label": "Tunai",
        "saldo_awal": 1000000,
        "saldo_berjalan": 2500000,
        "is_active": true,
        "created_at": "2026-06-10T15:19:56.000000Z"
      },
      "total_masuk": 5000000,
      "total_keluar": 3500000
    }
  ],
  "stok_menipis": [
    {
      "id": 3,
      "kode": "BRG-003",
      "nama": "Vitamin Ayam B-Kompleks",
      "satuan": "botol",
      "harga_beli": 25000,
      "harga_jual": 35000,
      "stok": 2,
      "stok_minimum": 5,
      "stok_menipis": true,
      "is_active": true,
      "created_at": "2026-06-10T15:19:56.000000Z"
    }
  ],
  "periode_info": { "bulan": "2026-06" }
}
```

- `total_saldo` = jumlah `saldo_berjalan` semua kas aktif.
- `total_masuk` / `total_keluar` = agregat transaksi non-void bulan ini per kas (termasuk transfer).
- `stok_menipis` = barang aktif dengan `stok <= stok_minimum`.

---

## 8. Kas

Objek: [Kas](#objek-kas). Enum `tipe`: `tunai` | `bank` | `ewallet` (label: `Tunai` / `Bank` / `E-Wallet`).

### GET `/kas` — Daftar kas

Auth: **ya**. Paginated ([format standar](#4-format-pagination)), urut `nama` ASC.

Query parameter:

| Param | Tipe | Keterangan |
|---|---|---|
| `search` | string | cari di `nama` atau `kode` (LIKE) |
| `per_page` | int | default **50** |
| `page` | int | default 1 |

### POST `/kas` — Tambah kas

Auth: **ya**.

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `kode` | string | ya | max 50, unique |
| `nama` | string | ya | max 100 |
| `tipe` | string | ya | `tunai` \| `bank` \| `ewallet` |
| `saldo_awal` | number | ya | min 0 |
| `is_active` | boolean | tidak | default `true` |

`saldo_berjalan` otomatis diset sama dengan `saldo_awal`.

Response `201` — objek Kas **tanpa wrapper**:

```json
{
  "id": 2,
  "kode": "BANK-BCA",
  "nama": "Rekening BCA",
  "tipe": "bank",
  "tipe_label": "Bank",
  "saldo_awal": 5000000,
  "saldo_berjalan": 5000000,
  "is_active": true,
  "created_at": "2026-06-11T02:15:30.000000Z"
}
```

### GET `/kas/{id}` — Detail kas

Auth: **ya**. Response `200` — **dibungkus `data`**:

```json
{ "data": { "id": 2, "kode": "BANK-BCA", "...": "..." } }
```

Error `404` jika id tidak ada.

### PUT `/kas/{id}` — Update kas

Auth: **ya**. Body sama dengan POST (semua field wajib tetap wajib dikirim, termasuk `saldo_awal`), **tetapi `saldo_awal` diabaikan** — saldo tidak berubah lewat endpoint ini. `kode` unique mengabaikan record sendiri.

Response `200` — dibungkus `data`.

### DELETE `/kas/{id}` — Hapus kas

Auth: **ya**. Hard delete.

Response `200`: `{"message": "Kas dihapus."}`

> Peringatan: jika kas sudah punya transaksi, penghapusan ditolak di level database (foreign key `restrictOnDelete`) dan menghasilkan error `500`. Sebaiknya nonaktifkan (`is_active: false`) alih-alih menghapus.

---

## 9. Kategori Transaksi

Objek: [KategoriTransaksi](#objek-kategoritransaksi). Enum `jenis`: `masuk` | `keluar` (label: `Masuk` / `Keluar`).

### GET `/kategori-transaksi` — Daftar kategori

Auth: **ya**. Paginated, urut `jenis` lalu `nama`.

| Param | Tipe | Keterangan |
|---|---|---|
| `search` | string | cari di `nama` (LIKE) |
| `jenis` | string | filter `masuk` \| `keluar` |
| `per_page` | int | default **100** |

### POST `/kategori-transaksi` — Tambah kategori

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `nama` | string | ya | max 100 |
| `jenis` | string | ya | `masuk` \| `keluar` |
| `is_active` | boolean | tidak | default `true` |

Response `201` — objek **tanpa wrapper**:

```json
{
  "id": 1,
  "nama": "Penjualan Ayam",
  "jenis": "masuk",
  "jenis_label": "Masuk",
  "is_active": true
}
```

### GET `/kategori-transaksi/{id}` — Detail

Response `200`: `{"data": { ... }}`

### PUT `/kategori-transaksi/{id}` — Update

Body sama dengan POST. Response `200`: `{"data": { ... }}`

### DELETE `/kategori-transaksi/{id}` — Hapus

Response `200`: `{"message": "Kategori dihapus."}`

> Transaksi yang memakai kategori terhapus tetap ada; `kategori_id`-nya menjadi `null` (foreign key `nullOnDelete`).

---

## 10. Transaksi Kas

Objek: [TransaksiKas](#objek-transaksikas). Enum `tipe`: `masuk` | `keluar` | `transfer_masuk` | `transfer_keluar` (label: `Masuk` / `Keluar` / `Transfer Masuk` / `Transfer Keluar`).

Format `nomor` otomatis: `TRX-YYYYMMDD-0001` (urut per hari).

### GET `/transaksi-kas` — Daftar transaksi

Auth: **ya**. Paginated, urut `tanggal` DESC lalu `id` DESC. Relasi `kas`, `kategori`, `user` **dimuat**.

| Param | Tipe | Keterangan |
|---|---|---|
| `kas_id` | int | filter per kas |
| `dari` | string `Y-m-d` | `tanggal >= dari` |
| `sampai` | string `Y-m-d` | `tanggal <= sampai` |
| `search` | string | cari di `nomor` atau `keterangan` (LIKE) |
| `include_void` | boolean | **default: transaksi void ikut tampil.** Kirim `include_void=0` untuk menyembunyikan yang void |
| `per_page` | int | default **20** |

Contoh elemen `data`:

```json
{
  "id": 10,
  "nomor": "TRX-20260611-0001",
  "kas_id": 1,
  "kas": { "id": 1, "kode": "KAS-001", "nama": "Kas Tunai Toko", "tipe": "tunai", "tipe_label": "Tunai", "saldo_awal": 1000000, "saldo_berjalan": 2500000, "is_active": true, "created_at": "2026-06-10T15:19:56.000000Z" },
  "kategori_id": 1,
  "kategori": { "id": 1, "nama": "Penjualan Ayam", "jenis": "masuk", "jenis_label": "Masuk", "is_active": true },
  "tanggal": "2026-06-11",
  "tipe": "masuk",
  "tipe_label": "Masuk",
  "jumlah": 500000,
  "keterangan": "Penjualan ayam 10 ekor",
  "transfer_group": null,
  "is_void": false,
  "void_at": null,
  "user_id": 1,
  "user": { "id": 1, "name": "Admin Binco" },
  "created_at": "2026-06-11T08:30:00.000000Z"
}
```

> `kategori` bernilai `null` jika transaksi tidak punya kategori. `transfer_group` (string UUID) terisi hanya untuk transaksi hasil transfer.

### POST `/transaksi-kas` — Buat transaksi masuk/keluar

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `kas_id` | int | ya | harus ada di tabel kas |
| `kategori_id` | int | tidak | nullable; harus ada di tabel kategori |
| `tanggal` | string | ya | format tanggal valid (`Y-m-d`) |
| `tipe` | string | ya | hanya `masuk` \| `keluar` (transfer lewat endpoint sendiri) |
| `jumlah` | number | ya | min 0.01 |
| `keterangan` | string | tidak | max 255 |

Efek: `saldo_berjalan` kas bertambah (masuk) / berkurang (keluar). **Tidak ada validasi saldo cukup — saldo kas bisa menjadi negatif.**

Response `201` — objek TransaksiKas **tanpa wrapper**; relasi `kas` dan `kategori` dimuat, **key `user` tidak ada**.

Error:

- `422` — validasi gagal.
- `409` — `{"message": "Periode 2026-05 sudah ditutup."}` jika tanggal jatuh di periode yang sudah ditutup untuk kas tsb.

### GET `/transaksi-kas/{id}` — Detail transaksi

Response `200`: `{"data": { ... }}` — relasi `kas` dan `kategori` dimuat, key `user` tidak ada.

### DELETE `/transaksi-kas/{id}` — Void transaksi

**Bukan hard delete** — transaksi ditandai `is_void: true` dan efek saldonya dibalik. Jika transaksi adalah bagian transfer (`transfer_group` terisi), **kedua sisi transfer** ikut di-void.

Response `200`: `{"message": "Transaksi di-void."}`

Error `409`:

- `{"message": "Transaksi sudah di-void."}`
- `{"message": "Transfer sudah di-void."}`
- `{"message": "Periode 2026-05 sudah ditutup."}`

### POST `/transaksi-kas/transfer` — Transfer antar-kas

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `kas_asal_id` | int | ya | exists |
| `kas_tujuan_id` | int | ya | exists, harus berbeda dari `kas_asal_id` |
| `tanggal` | string | ya | tanggal valid |
| `jumlah` | number | ya | min 0.01 |
| `keterangan` | string | tidak | max 255; default otomatis "Transfer ke/dari {nama kas}" |

Membuat **dua** transaksi (tipe `transfer_keluar` di kas asal, `transfer_masuk` di kas tujuan) yang terhubung lewat `transfer_group` (UUID) dan nomor TRX berurutan.

Response `201`:

```json
{
  "message": "Transfer berhasil.",
  "trx_keluar": {
    "id": 11,
    "nomor": "TRX-20260611-0002",
    "kas_id": 1,
    "kategori_id": null,
    "tanggal": "2026-06-11",
    "tipe": "transfer_keluar",
    "tipe_label": "Transfer Keluar",
    "jumlah": 1000000,
    "keterangan": "Transfer ke Rekening BCA",
    "transfer_group": "9c3f6a1e-2b4d-4f5a-8e7c-1d2e3f4a5b6c",
    "is_void": false,
    "void_at": null,
    "user_id": 1,
    "created_at": "2026-06-11T09:00:00.000000Z"
  },
  "trx_masuk": { "id": 12, "nomor": "TRX-20260611-0003", "kas_id": 2, "tipe": "transfer_masuk", "tipe_label": "Transfer Masuk", "...": "..." }
}
```

> Pada `trx_keluar`/`trx_masuk`, key `kas`, `kategori`, dan `user` **tidak ada** (relasi tidak dimuat).

Error:

- `422` — validasi gagal (termasuk `kas_tujuan_id` sama dengan `kas_asal_id`).
- `409` — periode ditutup untuk kas asal atau tujuan.

---

## 11. Barang

Objek: [Barang](#objek-barang).

### GET `/barang` — Daftar barang

Auth: **ya**. Paginated, urut `nama` ASC.

| Param | Tipe | Keterangan |
|---|---|---|
| `search` | string | cari di `nama` atau `kode` (LIKE) |
| `per_page` | int | default **15** |

### POST `/barang` — Tambah barang

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `kode` | string | ya | max 50, unique |
| `nama` | string | ya | max 150 |
| `satuan` | string | ya | max 30 (mis. "karung", "ekor", "botol") |
| `harga_beli` | number | ya | min 0 |
| `harga_jual` | number | ya | min 0 |
| `stok_minimum` | number | ya | min 0 |
| `is_active` | boolean | tidak | default `true` |

> **`stok` tidak bisa dikirim** — barang baru selalu mulai dengan stok 0. Stok hanya berubah lewat [Mutasi Barang](#12-mutasi-barang).

Response `201` — objek Barang **tanpa wrapper**:

```json
{
  "id": 1,
  "kode": "BRG-001",
  "nama": "Pakan Ayam Broiler 50kg",
  "satuan": "karung",
  "harga_beli": 350000,
  "harga_jual": 400000,
  "stok": 0,
  "stok_minimum": 5,
  "stok_menipis": true,
  "is_active": true,
  "created_at": "2026-06-11T02:20:00.000000Z"
}
```

`stok_menipis` = computed `stok <= stok_minimum`.

### GET `/barang/{id}` — Detail

Response `200`: `{"data": { ... }}`

### PUT `/barang/{id}` — Update

Body sama dengan POST; field `stok` diabaikan jika dikirim. Response `200`: `{"data": { ... }}`

### DELETE `/barang/{id}` — Hapus

Response `200`: `{"message": "Barang dihapus."}`

> Sama seperti kas: jika barang sudah punya mutasi, penghapusan ditolak database (`restrictOnDelete`) → error `500`. Gunakan `is_active: false`.

---

## 12. Mutasi Barang

Objek: [MutasiBarang](#objek-mutasibarang). Enum `tipe`: `masuk` | `keluar` | `penyesuaian` (label: `Masuk` / `Keluar` / `Penyesuaian`).

Format `nomor` otomatis per tipe: `IN-YYYYMMDD-0001` / `OUT-YYYYMMDD-0001` / `ADJ-YYYYMMDD-0001`.

### GET `/mutasi-barang` — Daftar mutasi

Auth: **ya**. Paginated, urut `tanggal` DESC lalu `id` DESC. Relasi `barang` dan `user` **dimuat**.

| Param | Tipe | Keterangan |
|---|---|---|
| `barang_id` | int | filter per barang |
| `dari` | string `Y-m-d` | `tanggal >= dari` |
| `sampai` | string `Y-m-d` | `tanggal <= sampai` |
| `search` | string | cari di `nomor`, `keterangan`, atau `referensi` (LIKE) |
| `per_page` | int | default **20** |

### POST `/mutasi-barang` — Buat mutasi (barang masuk / keluar / penyesuaian stok)

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `barang_id` | int | ya | exists |
| `tanggal` | string | ya | tanggal valid |
| `tipe` | string | ya | `masuk` \| `keluar` \| `penyesuaian` |
| `qty` | number | ya | min 0.01 |
| `harga_satuan` | number | tidak | min 0; default 0 |
| `referensi` | string | tidak | max 100 (mis. nomor PO/nota) |
| `keterangan` | string | tidak | max 255 |
| `kas_id` | int | tidak | exists; jika diisi, otomatis dibuat transaksi kas terkait |
| `kategori_id` | int | tidak | exists; kategori untuk transaksi kas terkait |

Perilaku per `tipe`:

- **`masuk`** — `stok += qty`. Jika `harga_satuan` dikirim, `harga_beli` barang ikut diperbarui.
- **`keluar`** — `stok -= qty`. Ditolak jika stok kurang.
- **`penyesuaian`** — `stok` di-SET menjadi `qty` (nilai absolut, bukan delta).

Jika `kas_id` diisi, sistem otomatis membuat **transaksi kas tertaut** senilai `qty × harga_satuan`:

- mutasi `masuk` (pembelian) → transaksi kas `keluar`;
- mutasi `keluar` (penjualan) → transaksi kas `masuk`.

Response `201` — objek MutasiBarang **tanpa wrapper**; relasi `barang` dan `user` dimuat:

```json
{
  "id": 5,
  "nomor": "IN-20260611-0001",
  "barang_id": 1,
  "barang": { "id": 1, "kode": "BRG-001", "nama": "Pakan Ayam Broiler 50kg", "satuan": "karung", "harga_beli": 350000, "harga_jual": 400000, "stok": 35, "stok_minimum": 5, "stok_menipis": false, "is_active": true, "created_at": "2026-06-11T02:20:00.000000Z" },
  "tanggal": "2026-06-11",
  "tipe": "masuk",
  "tipe_label": "Masuk",
  "qty": 10,
  "harga_satuan": 350000,
  "stok_setelah": 35,
  "referensi": "PO-2026-0042",
  "keterangan": "Pembelian pakan dari supplier",
  "is_void": false,
  "void_at": null,
  "user_id": 1,
  "user": { "id": 1, "name": "Admin Binco" },
  "created_at": "2026-06-11T09:15:00.000000Z"
}
```

Error:

- `422` — validasi gagal.
- `409` — `{"message": "Stok tidak cukup. Stok tersedia: 5.00 karung."}` (tipe `keluar`, qty > stok).
- `409` — `{"message": "Periode 2026-05 untuk barang ini sudah ditutup."}`
- `409` — `{"message": "Periode 2026-05 sudah ditutup."}` (periode KAS ditutup, saat `kas_id` diisi).

### GET `/mutasi-barang/{id}` — Detail mutasi

Response `200`: `{"data": { ... }}` — relasi `barang` dan `user` dimuat.

### DELETE `/mutasi-barang/{id}` — Void mutasi

**Bukan hard delete.** Stok dikembalikan (masuk → dikurangi lagi; keluar → ditambah lagi; penyesuaian → stok tidak berubah). Transaksi kas tertaut (jika ada) ikut di-void.

Response `200`: `{"message": "Mutasi di-void."}`

Error `409`: `{"message": "Mutasi sudah di-void."}` / periode ditutup.

---

## 13. Laporan

### GET `/laporan/cashflow` — Laporan arus kas

Auth: **ya**.

Query parameter (dikirim sebagai query string):

| Param | Tipe | Wajib | Aturan |
|---|---|---|---|
| `dari` | string `Y-m-d` | ya | tanggal valid |
| `sampai` | string `Y-m-d` | ya | `>= dari` |
| `kas_id` | int | tidak | exists; jika kosong, semua kas aktif |

Response `200` — **array di level teratas**, satu elemen per kas (hanya transaksi non-void):

```json
[
  {
    "kas": { "id": 1, "nama": "Kas Tunai Toko", "kode": "KAS-001" },
    "saldo_awal": 1000000,
    "transaksi": [
      {
        "id": 10,
        "tanggal": "2026-06-11",
        "nomor": "TRX-20260611-0001",
        "kategori": "Penjualan Ayam",
        "tipe": "masuk",
        "keterangan": "Penjualan ayam 10 ekor",
        "masuk": 500000,
        "keluar": 0,
        "saldo_berjalan": 1500000
      }
    ],
    "total_masuk": 500000,
    "total_keluar": 0,
    "saldo_akhir": 1500000
  }
]
```

- `kategori` = **string nama kategori** (bukan objek), `null` jika tanpa kategori.
- `saldo_awal` dihitung dari snapshot periode sebelumnya + transaksi sebelum `dari`.
- `tipe` bisa keempat nilai enum (termasuk transfer).

### GET `/laporan/kartu-stok` — Kartu stok barang

| Param | Tipe | Wajib | Aturan |
|---|---|---|---|
| `barang_id` | int | ya | exists |
| `dari` | string `Y-m-d` | ya | tanggal valid |
| `sampai` | string `Y-m-d` | ya | `>= dari` |

Response `200` — objek (hanya mutasi non-void):

```json
{
  "barang": { "id": 1, "nama": "Pakan Ayam Broiler 50kg", "kode": "BRG-001", "satuan": "karung" },
  "stok_awal": 25,
  "mutasi": [
    {
      "id": 5,
      "tanggal": "2026-06-11",
      "nomor": "IN-20260611-0001",
      "tipe": "masuk",
      "keterangan": "Pembelian pakan dari supplier",
      "referensi": "PO-2026-0042",
      "masuk": 10,
      "keluar": 0,
      "stok_berjalan": 35,
      "harga_satuan": 350000
    }
  ],
  "total_masuk": 10,
  "total_keluar": 0,
  "stok_akhir": 35
}
```

> Catatan: pada kartu stok, mutasi tipe `penyesuaian` dihitung di kolom `masuk` sebesar qty penyesuaian.

---

## 14. Periode

Sistem tutup buku bulanan. Saat periode ditutup, transaksi kas dan mutasi barang pada bulan tsb tidak bisa ditambah/di-void (error `409`).

### GET `/periode` — Daftar periode

Auth: **ya**. Response `200` — **array di level teratas**, urut periode terbaru dulu:

```json
[
  {
    "periode": "2026-05",
    "is_closed": true,
    "closed_at": "2026-06-01T09:00:00.000000Z",
    "kas": [
      { "kas_id": 1, "kas_nama": "Kas Tunai Toko", "saldo_akhir": 2500000, "is_closed": true },
      { "kas_id": 2, "kas_nama": "Rekening BCA", "saldo_akhir": 7500000, "is_closed": true }
    ]
  }
]
```

- `is_closed` level periode = `true` hanya jika **semua** kas pada periode itu tertutup.
- `closed_at` nullable; `kas_nama` nullable (jika kas terhapus).
- Array kosong `[]` jika belum ada snapshot periode sama sekali.

### POST `/periode/tutup` — Tutup periode (admin)

Auth: **ya, role `admin`**.

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `periode` | string | ya | format `Y-m`, mis. `"2026-05"` |

Menutup periode untuk **semua** kas dan barang aktif, menghitung snapshot saldo & stok akhir. Periode harus ditutup berurutan.

Response `200`: `{"message": "Periode berhasil ditutup."}`

Error:

- `403` — `{"message": "Hanya admin yang bisa menutup periode."}`
- `422` — format `periode` salah.
- `409` — `{"message": "Periode 2026-04 belum ditutup. Tutup berurutan."}`

### POST `/periode/buka` — Buka kembali periode terakhir (admin)

Auth: **ya, role `admin`**. Tanpa body. Membuka kembali periode tertutup **terakhir** (kas dan stok sekaligus).

Response `200`: `{"message": "Periode terakhir berhasil dibuka."}`

Error:

- `403` — `{"message": "Hanya admin yang bisa membuka periode."}`
- `409` — `{"message": "Tidak ada periode yang tertutup."}`

### GET `/periode/check` — Cek apakah tanggal jatuh di periode tertutup

Auth: **ya**.

| Param | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `tanggal` | string `Y-m-d` | ya | tanggal yang dicek |
| `kas_id` | int | tidak | cek untuk kas tertentu saja |

Response `200`: `{"is_closed": false}`

> Berguna untuk menonaktifkan form input transaksi di UI sebelum user submit.

---

## 15. User Profile

### GET `/user/profile` — Profil user login

Auth: **ya**. Response `200`: `{"user": { ...objek User... }}`

### PUT `/user/profile` — Update profil

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `name` | string | ya | max 255 |
| `phone` | string | tidak | nullable, max 20 |

Response `200`:

```json
{
  "user": { "...objek User..." : "..." },
  "message": "Profil berhasil diperbarui."
}
```

> Email dan role tidak bisa diubah lewat endpoint ini.

### PUT `/user/password` — Ganti password

| Field | Tipe | Wajib | Aturan |
|---|---|---|---|
| `current_password` | string | ya | harus cocok dengan password sekarang |
| `password` | string | ya | min 6, harus dikonfirmasi |
| `password_confirmation` | string | ya | harus sama dengan `password` |

Response `200`: `{"message": "Password berhasil diubah."}`

Error `422` (password lama salah):

```json
{
  "message": "Password lama tidak sesuai.",
  "errors": { "current_password": ["Password lama tidak sesuai."] }
}
```

> Mengganti password **tidak** me-revoke token yang sudah ada.

---

## 16. Activity Log

### GET `/activity-log` — Daftar log aktivitas

Auth: **ya**. Urut `created_at` DESC. **Format paginasi kustom** (bukan format standar):

| Param | Tipe | Keterangan |
|---|---|---|
| `user_id` | int | filter per user |
| `action` | string | filter exact, lihat daftar nilai di bawah |
| `dari` | string `Y-m-d` | tanggal `created_at >= dari` |
| `sampai` | string `Y-m-d` | tanggal `created_at <= sampai` |
| `search` | string | cari di `description`, `action`, atau nama user (LIKE) |
| `per_page` | int | default **50** |
| `page` | int | default 1 |

Nilai `action` yang dipakai sistem: `login`, `logout`, `create`, `update`, `delete`, `void`, `transfer`, `update_profile`, `change_password`.

Response `200`:

```json
{
  "data": [
    {
      "id": 42,
      "user_id": 1,
      "action": "create",
      "subject_type": "App\\Models\\TransaksiKas",
      "subject_id": 10,
      "description": "Buat transaksi kas TRX-20260611-0001 (masuk) Rp 500.000",
      "properties": null,
      "ip_address": "127.0.0.1",
      "user_agent": "Dart/3.5 (dart:io)",
      "created_at": "2026-06-11T08:30:00.000000Z",
      "user": {
        "id": 1,
        "name": "Admin Binco",
        "email": "admin@bincofarm.test",
        "phone": "081234567890",
        "avatar": null,
        "email_verified_at": null,
        "role": "admin",
        "created_at": "2026-06-10T15:19:56.000000Z",
        "updated_at": "2026-06-10T15:19:56.000000Z"
      }
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 50, "total": 120 }
}
```

- Elemen `data` adalah **model mentah** (bukan Resource): `user` berupa objek User lengkap atau `null`.
- `properties` berupa objek/array bebas atau `null`.
- `subject_type`/`subject_id` nullable.
- Tidak ada key `links`; `meta` hanya berisi 4 field di atas.

---

## 17. Schema Objek JSON (untuk model Dart)

Tipe Dart yang disarankan per field. Semua field uang/qty dikirim sebagai JSON number — parse dengan `(json['x'] as num).toDouble()` agar aman terhadap nilai bulat.

### Objek User

| Field | Tipe Dart | Nullable | Keterangan |
|---|---|---|---|
| `id` | `int` | — | |
| `name` | `String` | — | |
| `email` | `String` | — | |
| `phone` | `String?` | ya | |
| `avatar` | `String?` | ya | belum dipakai (selalu null saat ini) |
| `email_verified_at` | `DateTime?` | ya | ISO 8601 |
| `role` | `String` | — | `admin` \| `kasir` |
| `created_at` | `DateTime` | — | ISO 8601 |
| `updated_at` | `DateTime` | — | ISO 8601 |

### Objek Kas

| Field | Tipe Dart | Nullable | Keterangan |
|---|---|---|---|
| `id` | `int` | — | |
| `kode` | `String` | — | unique |
| `nama` | `String` | — | |
| `tipe` | `String` | — | `tunai` \| `bank` \| `ewallet` |
| `tipe_label` | `String` | — | `Tunai` \| `Bank` \| `E-Wallet` |
| `saldo_awal` | `double` | — | |
| `saldo_berjalan` | `double` | — | bisa negatif |
| `is_active` | `bool` | — | |
| `created_at` | `DateTime` | — | ISO 8601 |

### Objek KategoriTransaksi

| Field | Tipe Dart | Nullable | Keterangan |
|---|---|---|---|
| `id` | `int` | — | |
| `nama` | `String` | — | |
| `jenis` | `String` | — | `masuk` \| `keluar` |
| `jenis_label` | `String` | — | |
| `is_active` | `bool` | — | |

### Objek TransaksiKas

| Field | Tipe Dart | Nullable | Keterangan |
|---|---|---|---|
| `id` | `int` | — | |
| `nomor` | `String` | — | `TRX-YYYYMMDD-####` |
| `kas_id` | `int` | — | |
| `kas` | `Kas?` | ya | **key bisa tidak ada** (tergantung endpoint) |
| `kategori_id` | `int?` | ya | |
| `kategori` | `KategoriTransaksi?` | ya | key bisa tidak ada; `null` jika tanpa kategori |
| `tanggal` | `String` / `DateTime` | — | `"Y-m-d"` |
| `tipe` | `String` | — | `masuk` \| `keluar` \| `transfer_masuk` \| `transfer_keluar` |
| `tipe_label` | `String` | — | |
| `jumlah` | `double` | — | |
| `keterangan` | `String?` | ya | |
| `transfer_group` | `String?` | ya | UUID; terisi hanya untuk transfer |
| `is_void` | `bool` | — | |
| `void_at` | `DateTime?` | ya | |
| `user_id` | `int?` | ya | |
| `user` | `UserMini?` | ya | key hanya ada di index; bentuk `{id, name}` |
| `created_at` | `DateTime` | — | |

`UserMini`: `{ "id": int, "name": String }`.

### Objek Barang

| Field | Tipe Dart | Nullable | Keterangan |
|---|---|---|---|
| `id` | `int` | — | |
| `kode` | `String` | — | unique |
| `nama` | `String` | — | |
| `satuan` | `String` | — | |
| `harga_beli` | `double` | — | |
| `harga_jual` | `double` | — | |
| `stok` | `double` | — | |
| `stok_minimum` | `double` | — | |
| `stok_menipis` | `bool` | — | computed: `stok <= stok_minimum` |
| `is_active` | `bool` | — | |
| `created_at` | `DateTime` | — | |

### Objek MutasiBarang

| Field | Tipe Dart | Nullable | Keterangan |
|---|---|---|---|
| `id` | `int` | — | |
| `nomor` | `String` | — | `IN-`/`OUT-`/`ADJ-YYYYMMDD-####` |
| `barang_id` | `int` | — | |
| `barang` | `Barang?` | ya | key bisa tidak ada |
| `tanggal` | `String` / `DateTime` | — | `"Y-m-d"` |
| `tipe` | `String` | — | `masuk` \| `keluar` \| `penyesuaian` |
| `tipe_label` | `String` | — | |
| `qty` | `double` | — | |
| `harga_satuan` | `double` | — | 0 jika tidak diisi |
| `stok_setelah` | `double` | — | stok barang setelah mutasi ini |
| `referensi` | `String?` | ya | |
| `keterangan` | `String?` | ya | |
| `is_void` | `bool` | — | |
| `void_at` | `DateTime?` | ya | |
| `user_id` | `int?` | ya | |
| `user` | `UserMini?` | ya | `{id, name}` |
| `created_at` | `DateTime` | — | |

### Objek ActivityLog

| Field | Tipe Dart | Nullable | Keterangan |
|---|---|---|---|
| `id` | `int` | — | |
| `user_id` | `int?` | ya | |
| `action` | `String` | — | |
| `subject_type` | `String?` | ya | nama class model, mis. `App\Models\Kas` |
| `subject_id` | `int?` | ya | |
| `description` | `String` | — | |
| `properties` | `Map<String, dynamic>?` | ya | |
| `ip_address` | `String?` | ya | |
| `user_agent` | `String?` | ya | |
| `created_at` | `DateTime` | — | |
| `user` | `User?` | ya | objek User lengkap |

### Objek Dashboard

```
DashboardResponse {
  total_saldo: double,
  kas: List<DashboardKasItem>,
  stok_menipis: List<Barang>,
  periode_info: { bulan: String }   // "Y-m"
}
DashboardKasItem { kas: Kas, total_masuk: double, total_keluar: double }
```

### Objek Laporan Cashflow

```
CashflowKas {
  kas: { id: int, nama: String, kode: String },
  saldo_awal: double,
  transaksi: List<CashflowRow>,
  total_masuk: double,
  total_keluar: double,
  saldo_akhir: double
}
CashflowRow {
  id: int, tanggal: String, nomor: String,
  kategori: String?,            // NAMA kategori, bukan objek
  tipe: String, keterangan: String?,
  masuk: double, keluar: double, saldo_berjalan: double
}
```

### Objek Kartu Stok

```
KartuStok {
  barang: { id: int, nama: String, kode: String, satuan: String },
  stok_awal: double,
  mutasi: List<KartuStokRow>,
  total_masuk: double,
  total_keluar: double,
  stok_akhir: double
}
KartuStokRow {
  id: int, tanggal: String, nomor: String, tipe: String,
  keterangan: String?, referensi: String?,
  masuk: double, keluar: double, stok_berjalan: double, harga_satuan: double
}
```

### Objek Periode

```
PeriodeGroup {
  periode: String,        // "Y-m"
  is_closed: bool,
  closed_at: DateTime?,
  kas: List<PeriodeKas>
}
PeriodeKas { kas_id: int, kas_nama: String?, saldo_akhir: double, is_closed: bool }
```

### Objek Pagination (list standar)

```
PaginationLinks { first: String?, last: String?, prev: String?, next: String? }
PaginationMeta {
  current_page: int, from: int?, last_page: int,
  links: List<{url: String?, label: String, page: int?, active: bool}>,
  path: String, per_page: int, to: int?, total: int
}
```

> `meta` di `/activity-log` hanya berisi `current_page`, `last_page`, `per_page`, `total`.

---

## 18. Catatan Integrasi Flutter

### Penyimpanan token

Gunakan [`flutter_secure_storage`](https://pub.dev/packages/flutter_secure_storage) — jangan `shared_preferences` (tidak terenkripsi):

```dart
const storage = FlutterSecureStorage();
await storage.write(key: 'token', value: token);   // setelah login
final token = await storage.read(key: 'token');    // saat startup
await storage.delete(key: 'token');                // saat logout / 401
```

### HTTP client (contoh dengan dio)

```dart
final dio = Dio(BaseOptions(
  baseUrl: 'https://app.bincofarm.example/api',
  headers: {'Accept': 'application/json'},
));

dio.interceptors.add(InterceptorsWrapper(
  onRequest: (options, handler) async {
    final token = await storage.read(key: 'token');
    if (token != null) options.headers['Authorization'] = 'Bearer $token';
    handler.next(options);
  },
  onError: (e, handler) async {
    if (e.response?.statusCode == 401) {
      await storage.delete(key: 'token');
      // arahkan user ke halaman login (auto-logout)
    }
    handler.next(e);
  },
));
```

### Penanganan 401 (auto-logout)

Token bisa di-revoke dari server kapan saja (logout di device lain, dsb). Setiap response `401` berarti sesi tidak valid: hapus token lokal dan kembalikan user ke halaman login. Jangan retry request dengan token yang sama.

### Hal-hal yang sering menjebak

1. **Wrapper `data` tidak konsisten**: `GET` detail & `PUT` dibungkus `{"data": {...}}`, tetapi `POST` create (201) mengembalikan objek langsung tanpa wrapper. Buat helper unwrap:
   ```dart
   Map<String, dynamic> unwrap(dynamic body) =>
       (body is Map && body.containsKey('data') && body['data'] is Map)
           ? body['data'] as Map<String, dynamic>
           : body as Map<String, dynamic>;
   ```
2. **Key relasi bisa hilang**: `kas`, `kategori`, `barang`, `user` hanya ada jika endpoint memuat relasinya. Selalu nullable di model Dart.
3. **Angka uang**: dikirim sebagai JSON number; nilai bulat bisa ter-decode sebagai `int` di Dart. Selalu `(json['jumlah'] as num).toDouble()`.
4. **Tanggal**: field `tanggal` formatnya `"yyyy-MM-dd"`; field `*_at` formatnya ISO 8601 UTC — konversi ke lokal dengan `DateTime.parse(s).toLocal()`.
5. **PUT = full update**: kirim semua field wajib (bukan partial). `PUT /kas/{id}` tetap mewajibkan `saldo_awal` walau nilainya diabaikan.
6. **DELETE pada transaksi/mutasi = void**, bukan hapus — record tetap muncul di list dengan `is_void: true`. Gunakan `include_void=0` di `/transaksi-kas` untuk menyembunyikannya.
7. **`per_page`** bisa diatur untuk infinite scroll; cek `meta.current_page < meta.last_page` untuk menentukan masih ada halaman berikutnya.
8. **Error 422 vs 409**: tampilkan `errors` per field untuk 422 (jika ada), dan tampilkan `message` sebagai snackbar/dialog untuk 409 (pelanggaran aturan bisnis).
9. **Base URL dev**: Android emulator → `http://10.0.2.2:8000/api`; iOS simulator → `http://127.0.0.1:8000/api`; device fisik → IP LAN mesin dev (jalankan `php artisan serve --host=0.0.0.0`).
10. **HTTP plaintext saat dev**: Android perlu `android:usesCleartextTraffic="true"` (atau network security config) untuk mengakses `http://` non-TLS.
