# University Academic Information System (SIA) - Comprehensive Design

## Overview

The University Academic Information System (SIA) is a comprehensive web-based platform designed to manage the complete academic lifecycle of students from initial registration through graduation. The system integrates with external platforms like PDDIKTI and provides comprehensive management tools for students, faculty, and administrative staff.

### Core Objectives
- Complete student lifecycle management
- Integrated academic record keeping
- Real-time reporting and analytics
- External system integration (PDDIKTI, DIKTI, etc.)
- Multi-role access control
- Comprehensive academic workflow automation

## Technology Stack & Dependencies

### Backend Framework
- **Framework**: Laravel 10+ (PHP)
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum/Passport
- **Queue System**: Redis
- **File Storage**: Laravel Storage (Local/S3)
- **API Documentation**: Swagger/OpenAPI

### Frontend Framework
- **Framework**: Vue.js 3 with Composition API
- **UI Library**: Quasar Framework
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **HTTP Client**: Axios
- **Form Validation**: VeeValidate

### Integration & External Services
- **PDDIKTI Integration**: REST API Client
- **Payment Gateway**: Midtrans/Xendit
- **Email Service**: Laravel Mail (SMTP)
- **SMS Gateway**: Twilio/Zenziva
- **PDF Generation**: DomPDF/mPDF
- **Excel Processing**: PhpSpreadsheet

## Architecture

### System Architecture Overview

```mermaid
graph TB
    subgraph "Client Layer"
        WEB[Web Application]
        MOBILE[Mobile App]
        API_CLIENT[External API Clients]
    end
    
    subgraph "Application Layer"
        GATEWAY[API Gateway]
        AUTH[Authentication Service]
        STUDENT[Student Service]
        ACADEMIC[Academic Service]
        FINANCE[Finance Service]
        REPORT[Reporting Service]
    end
    
    subgraph "Data Layer"
        DB[(MySQL Database)]
        REDIS[(Redis Cache)]
        FILES[File Storage]
    end
    
    subgraph "External Systems"
        PDDIKTI[PDDIKTI API]
        PAYMENT[Payment Gateway]
        EMAIL[Email Service]
        SMS[SMS Gateway]
    end
    
    WEB --> GATEWAY
    MOBILE --> GATEWAY
    API_CLIENT --> GATEWAY
    
    GATEWAY --> AUTH
    GATEWAY --> STUDENT
    GATEWAY --> ACADEMIC
    GATEWAY --> FINANCE
    GATEWAY --> REPORT
    
    STUDENT --> DB
    ACADEMIC --> DB
    FINANCE --> DB
    REPORT --> DB
    
    AUTH --> REDIS
    STUDENT --> REDIS
    
    STUDENT --> FILES
    ACADEMIC --> FILES
    
    REPORT --> PDDIKTI
    FINANCE --> PAYMENT
    STUDENT --> EMAIL
    STUDENT --> SMS
```

### Arsitektur Database

```mermaid
erDiagram
    PENGGUNA ||--o{ MAHASISWA : "memiliki"
    PENGGUNA ||--o{ DOSEN : "memiliki"
    PENGGUNA ||--o{ STAF : "memiliki"
    
    MAHASISWA ||--o{ PENGAMBILAN_MATA_KULIAH : "membuat"
    MAHASISWA ||--o{ PEMBAYARAN : "melakukan"
    MAHASISWA ||--o{ REKAM_AKADEMIK : "memiliki"
    
    DOSEN ||--o{ MATA_KULIAH : "mengajar"
    DOSEN ||--o{ JADWAL_KELAS : "ditugaskan_ke"
    
    MATA_KULIAH ||--o{ PENGAMBILAN_MATA_KULIAH : "diambil_dalam"
    MATA_KULIAH ||--o{ JADWAL_KELAS : "dijadwalkan_untuk"
    MATA_KULIAH ||--o{ NILAI : "dinilai_dalam"
    
    MAHASISWA ||--o{ NILAI : "menerima"
    
    PERIODE_AKADEMIK ||--o{ PENGAMBILAN_MATA_KULIAH : "berisi"
    PERIODE_AKADEMIK ||--o{ JADWAL_KELAS : "dijadwalkan_dalam"
    
    FAKULTAS ||--o{ PROGRAM_STUDI : "berisi"
    PROGRAM_STUDI ||--o{ MAHASISWA : "terdaftar_dalam"
    PROGRAM_STUDI ||--o{ MATA_KULIAH : "menawarkan"
    
    PENGGUNA {
        bigint id PK
        string nama_pengguna UK
        string email UK
        string kata_sandi
        enum peran
        timestamp email_terverifikasi_pada
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    MAHASISWA {
        bigint id PK
        bigint id_pengguna FK
        string nomor_mahasiswa UK
        string nim UK
        bigint id_program_studi FK
        enum status
        date tanggal_masuk
        json data_pribadi
        json data_kontak
        json data_akademik
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    DOSEN {
        bigint id PK
        bigint id_pengguna FK
        string nomor_dosen UK
        string nidn
        bigint id_fakultas FK
        json data_pribadi
        json kredensial_akademik
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    MATA_KULIAH {
        bigint id PK
        string kode_mata_kuliah UK
        string nama_mata_kuliah
        bigint id_program_studi FK
        integer sks
        enum jenis
        json prasyarat
        text deskripsi
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    PENGAMBILAN_MATA_KULIAH {
        bigint id PK
        bigint id_mahasiswa FK
        bigint id_mata_kuliah FK
        bigint id_periode_akademik FK
        enum status
        timestamp terdaftar_pada
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    NILAI {
        bigint id PK
        bigint id_mahasiswa FK
        bigint id_mata_kuliah FK
        bigint id_periode_akademik FK
        decimal nilai_angka
        string nilai_huruf
        enum status
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    FAKULTAS {
        bigint id PK
        string kode_fakultas UK
        string nama_fakultas
        text deskripsi
        enum status_aktif
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    PROGRAM_STUDI {
        bigint id PK
        string kode_program_studi UK
        string nama_program_studi
        bigint id_fakultas FK
        enum jenjang
        string akreditasi
        enum status_aktif
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    PERIODE_AKADEMIK {
        bigint id PK
        string kode_periode UK
        string nama_periode
        date tanggal_mulai
        date tanggal_selesai
        enum jenis_periode
        enum status
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    JADWAL_KELAS {
        bigint id PK
        bigint id_mata_kuliah FK
        bigint id_dosen FK
        bigint id_periode_akademik FK
        string nama_kelas
        string ruang_kelas
        json waktu_jadwal
        integer kapasitas_maksimal
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    PEMBAYARAN {
        bigint id PK
        bigint id_mahasiswa FK
        string nomor_pembayaran UK
        enum jenis_pembayaran
        decimal jumlah_bayar
        decimal jumlah_tagihan
        date tanggal_jatuh_tempo
        date tanggal_bayar
        enum status_pembayaran
        json detail_pembayaran
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
    
    STAF {
        bigint id PK
        bigint id_pengguna FK
        string nomor_staf UK
        string nip
        bigint id_fakultas FK
        json data_pribadi
        enum jabatan
        timestamp dibuat_pada
        timestamp diperbarui_pada
    }
```

## Arsitektur Komponen

### Hierarki Komponen Frontend

```mermaid
graph TD
    APP[App.vue]
    
    APP --> LAYOUT[TataLetakUtama.vue]
    APP --> AUTH[TataLetakAuth.vue]
    
    LAYOUT --> HEADER[KomponenHeader.vue]
    LAYOUT --> SIDEBAR[KomponenSidebar.vue]
    LAYOUT --> CONTENT[RouterView]
    
    CONTENT --> DASHBOARD[HalamanDasbor.vue]
    CONTENT --> MAHASISWA[ModulMahasiswa]
    CONTENT --> AKADEMIK[ModulAkademik]
    CONTENT --> KEUANGAN[ModulKeuangan]
    CONTENT --> LAPORAN[ModulLaporan]
    
    MAHASISWA --> DAFTAR_MAHASISWA[DaftarMahasiswa.vue]
    MAHASISWA --> FORM_MAHASISWA[FormMahasiswa.vue]
    MAHASISWA --> DETAIL_MAHASISWA[DetailMahasiswa.vue]
    
    AKADEMIK --> DAFTAR_MATA_KULIAH[DaftarMataKuliah.vue]
    AKADEMIK --> PENGAMBILAN[ManagerPengambilan.vue]
    AKADEMIK --> INPUT_NILAI[InputNilai.vue]
    AKADEMIK --> JADWAL[ManagerJadwal.vue]
    
    KEUANGAN --> DAFTAR_PEMBAYARAN[DaftarPembayaran.vue]
    KEUANGAN --> TAGIHAN[ManagerTagihan.vue]
    KEUANGAN --> BANTUAN_KEUANGAN[BantuanKeuangan.vue]
    
    LAPORAN --> TRANSKRIP[GeneratorTranskrip.vue]
    LAPORAN --> ANALITIK[DasborAnalitik.vue]
    LAPORAN --> SYNC_PDDIKTI[SinkronisasiPDDIKTI.vue]
```

### Spesifikasi Komponen

#### Komponen Inti

**TataLetakUtama.vue**
- Props: Tidak ada
- State: `sidebarTerbuka`, `notifikasi`, `profilPengguna`
- Methods: `toggleSidebar()`, `keluar()`, `muatNotifikasi()`
- Lifecycle: `mounted()` - Inisialisasi sesi pengguna

**FormMahasiswa.vue**
- Props: `idMahasiswa?: number`, `mode: 'buat' | 'edit'`
- State: `dataForm`, `errorValidasi`, `sedangSubmit`
- Methods: `validasiForm()`, `submitForm()`, `uploadDokumen()`
- Events: `@submit`, `@cancel`

**ManagerPengambilan.vue**
- Props: `idPeriodeAkademik: number`
- State: `mataKuliahTersedia`, `mataKuliahDiambil`, `batasanPengambilan`
- Methods: `ambilMataKuliah()`, `batalkanMataKuliah()`, `cekPrasyarat()`
- Computed: `mataKuliahLayak`, `totalSKS`

## API Endpoints Reference

### Endpoint Autentikasi

| Method | Endpoint | Deskripsi | Auth Diperlukan |
|--------|----------|-----------|----------------|
| POST | `/api/auth/masuk` | Autentikasi pengguna | Tidak |
| POST | `/api/auth/keluar` | Logout pengguna | Ya |
| POST | `/api/auth/refresh` | Refresh token | Ya |
| GET | `/api/auth/saya` | Ambil pengguna saat ini | Ya |
| POST | `/api/auth/lupa-kata-sandi` | Permintaan reset kata sandi | Tidak |
| POST | `/api/auth/reset-kata-sandi` | Reset kata sandi | Tidak |

### Endpoint Manajemen Mahasiswa

| Method | Endpoint | Deskripsi | Auth Diperlukan |
|--------|----------|-----------|----------------|
| GET | `/api/mahasiswa` | Daftar mahasiswa dengan paginasi | Ya |
| POST | `/api/mahasiswa` | Buat mahasiswa baru | Ya |
| GET | `/api/mahasiswa/{id}` | Ambil detail mahasiswa | Ya |
| PUT | `/api/mahasiswa/{id}` | Update informasi mahasiswa | Ya |
| DELETE | `/api/mahasiswa/{id}` | Soft delete mahasiswa | Ya |
| GET | `/api/mahasiswa/{id}/rekam-akademik` | Ambil rekam akademik mahasiswa | Ya |
| GET | `/api/mahasiswa/{id}/transkrip` | Generate transkrip | Ya |
| POST | `/api/mahasiswa/{id}/dokumen` | Upload dokumen mahasiswa | Ya |

### Endpoint Manajemen Akademik

| Method | Endpoint | Deskripsi | Auth Diperlukan |
|--------|----------|-----------|----------------|
| GET | `/api/mata-kuliah` | Daftar semua mata kuliah | Ya |
| POST | `/api/mata-kuliah` | Buat mata kuliah baru | Ya |
| GET | `/api/mata-kuliah/{id}` | Ambil detail mata kuliah | Ya |
| PUT | `/api/mata-kuliah/{id}` | Update mata kuliah | Ya |
| GET | `/api/pengambilan-mata-kuliah` | Daftar pengambilan mata kuliah | Ya |
| POST | `/api/pengambilan-mata-kuliah` | Buat pengambilan mata kuliah | Ya |
| PUT | `/api/pengambilan-mata-kuliah/{id}` | Update status pengambilan | Ya |
| GET | `/api/nilai` | Daftar nilai dengan filter | Ya |
| POST | `/api/nilai` | Submit nilai | Ya |
| PUT | `/api/nilai/{id}` | Update nilai | Ya |

### Contoh Skema Request/Response

**Request Pembuatan Mahasiswa**
```json
{
  "data_pribadi": {
    "nama_lengkap": "Ahmad Sutanto",
    "tanggal_lahir": "2000-01-15",
    "tempat_lahir": "Jakarta",
    "jenis_kelamin": "laki-laki",
    "agama": "islam",
    "kewarganegaraan": "indonesia"
  },
  "data_kontak": {
    "email": "ahmad.sutanto@example.com",
    "telepon": "+6281234567890",
    "alamat": {
      "jalan": "Jl. Contoh No. 123",
      "kota": "Jakarta",
      "provinsi": "DKI Jakarta",
      "kode_pos": "12345"
    }
  },
  "data_akademik": {
    "id_program_studi": 1,
    "tahun_masuk": 2024,
    "jenis_masuk": "reguler",
    "sekolah_menengah": {
      "nama": "SMA Contoh",
      "tahun_lulus": 2023
    }
  }
}
```

**Response Mahasiswa**
```json
{
  "data": {
    "id": 1,
    "nomor_mahasiswa": "2024010001",
    "nim": "24010001",
    "status": "aktif",
    "tanggal_masuk": "2024-08-01",
    "data_pribadi": { /* ... */ },
    "data_kontak": { /* ... */ },
    "data_akademik": { /* ... */ },
    "program_studi": {
      "id": 1,
      "nama": "Teknik Informatika",
      "fakultas": "Teknik"
    },
    "dibuat_pada": "2024-08-01T10:00:00Z",
    "diperbarui_pada": "2024-08-01T10:00:00Z"
  }
}
```

## Model Data & Mapping ORM

### Model Inti

**Model Mahasiswa**
```php
class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';
    
    protected $fillable = [
        'id_pengguna', 'nomor_mahasiswa', 'nim', 'id_program_studi',
        'status', 'tanggal_masuk', 'data_pribadi', 'data_kontak', 'data_akademik'
    ];
    
    protected $casts = [
        'data_pribadi' => 'array',
        'data_kontak' => 'array',
        'data_akademik' => 'array',
        'tanggal_masuk' => 'date'
    ];
    
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
    
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'id_program_studi');
    }
    
    public function pengambilanMataKuliah(): HasMany
    {
        return $this->hasMany(PengambilanMataKuliah::class, 'id_mahasiswa');
    }
    
    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'id_mahasiswa');
    }
    
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_mahasiswa');
    }
}
```

**Model Mata Kuliah**
```php
class MataKuliah extends Model
{
    protected $table = 'mata_kuliah';
    
    protected $fillable = [
        'kode_mata_kuliah', 'nama_mata_kuliah', 'id_program_studi', 
        'sks', 'jenis', 'prasyarat', 'deskripsi'
    ];
    
    protected $casts = [
        'prasyarat' => 'array'
    ];
    
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'id_program_studi');
    }
    
    public function pengambilanMataKuliah(): HasMany
    {
        return $this->hasMany(PengambilanMataKuliah::class, 'id_mata_kuliah');
    }
    
    public function jadwalKelas(): HasMany
    {
        return $this->hasMany(JadwalKelas::class, 'id_mata_kuliah');
    }
}
```

**Model Dosen**
```php
class Dosen extends Model
{
    protected $table = 'dosen';
    
    protected $fillable = [
        'id_pengguna', 'nomor_dosen', 'nidn', 'id_fakultas',
        'data_pribadi', 'kredensial_akademik'
    ];
    
    protected $casts = [
        'data_pribadi' => 'array',
        'kredensial_akademik' => 'array'
    ];
    
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
    
    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas');
    }
    
    public function mataKuliah(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'id_dosen');
    }
}
```

## Arsitektur Lapisan Logika Bisnis

### Modul Manajemen Mahasiswa

```mermaid
graph TD
    MM[Manajemen Mahasiswa] --> PENDAFTARAN[Proses Pendaftaran]
    MM --> PROFIL[Manajemen Profil]
    MM --> STATUS[Manajemen Status]
    MM --> DOKUMEN[Manajemen Dokumen]
    
    PENDAFTARAN --> VALIDASI[Validasi Data]
    PENDAFTARAN --> GEN_NIM[Generasi NIM]
    PENDAFTARAN --> BUAT_AKUN[Pembuatan Akun]
    PENDAFTARAN --> NOTIFIKASI[Notifikasi Selamat Datang]
    
    PROFIL --> UPDATE[Update Profil]
    PROFIL --> VERIFIKASI[Verifikasi Data]
    PROFIL --> RIWAYAT[Riwayat Perubahan]
    
    STATUS --> AKTIF[Status Aktif]
    STATUS --> TIDAK_AKTIF[Status Tidak Aktif]
    STATUS --> LULUS[Status Lulus]
    STATUS --> SKORSING[Status Skorsing]
    
    DOKUMEN --> UPLOAD[Upload Dokumen]
    DOKUMEN --> VALIDASI_DOK[Validasi Dokumen]
    DOKUMEN --> PENYIMPANAN[Penyimpanan Aman]
```

### Academic Process Flow

```mermaid
sequenceDiagram
    participant M as Mahasiswa
    participant SIS as Sistem
    participant D as Dosen
    participant A as Admin
    
    Note over M,A: Proses Pengambilan Mata Kuliah
    M->>SIS: Lihat Mata Kuliah Tersedia
    SIS->>M: Tampilkan Mata Kuliah dengan Prasyarat
    M->>SIS: Ajukan Pengambilan
    SIS->>SIS: Cek Prasyarat & Batasan
    SIS->>M: Konfirmasi Pengambilan
    
    Note over M,A: Siklus Periode Akademik
    A->>SIS: Buat Periode Akademik
    SIS->>SIS: Buka Periode Pengambilan
    M->>SIS: Ambil Mata Kuliah
    D->>SIS: Lakukan Perkuliahan
    D->>SIS: Submit Nilai
    SIS->>SIS: Hitung IPK
    SIS->>M: Rilis Transkrip
```

### Grade Management Workflow

```mermaid
flowchart TD
    MULAI[Periode Akademik Dimulai] --> DAFTAR[Pendaftaran Mahasiswa]
    DAFTAR --> HADIR[Kehadiran Kelas]
    HADIR --> TUGAS[Tugas & Ujian]
    TUGAS --> INPUT_NILAI[Input Nilai oleh Dosen]
    INPUT_NILAI --> VERIFIKASI[Verifikasi Nilai]
    VERIFIKASI --> SETUJU{Persetujuan Nilai}
    SETUJU -->|Disetujui| PUBLIKASI[Publikasi Nilai]
    SETUJU -->|Ditolak| INPUT_NILAI
    PUBLIKASI --> HITUNG_IPK[Hitung IPK]
    HITUNG_IPK --> TRANSKRIP[Update Transkrip]
    TRANSKRIP --> CEK_LULUS[Cek Syarat Kelulusan]
    CEK_LULUS -->|Lengkap| WISUDA[Wisuda Mahasiswa]
    CEK_LULUS -->|Belum Lengkap| PERIODE_BERIKUTNYA[Periode Akademik Berikutnya]
```

## Routing & Navigation

### Struktur Route Frontend

```javascript
const routes = [
  {
    path: '/',
    component: () => import('layouts/TataLetakUtama.vue'),
    children: [
      {
        path: '',
        name: 'dasbor',
        component: () => import('pages/Dasbor.vue'),
        meta: { perluAuth: true }
      },
      {
        path: '/mahasiswa',
        name: 'mahasiswa',
        component: () => import('pages/mahasiswa/IndeksMahasiswa.vue'),
        meta: { perluAuth: true, peran: ['admin', 'staf'] }
      },
      {
        path: '/mahasiswa/buat',
        name: 'buat-mahasiswa',
        component: () => import('pages/mahasiswa/BuatMahasiswa.vue'),
        meta: { perluAuth: true, peran: ['admin', 'staf'] }
      },
      {
        path: '/mahasiswa/:id',
        name: 'detail-mahasiswa',
        component: () => import('pages/mahasiswa/DetailMahasiswa.vue'),
        meta: { perluAuth: true }
      },
      {
        path: '/akademik/mata-kuliah',
        name: 'mata-kuliah',
        component: () => import('pages/akademik/IndeksMataKuliah.vue'),
        meta: { perluAuth: true }
      },
      {
        path: '/akademik/pengambilan',
        name: 'pengambilan',
        component: () => import('pages/akademik/IndeksPengambilan.vue'),
        meta: { perluAuth: true }
      },
      {
        path: '/keuangan/pembayaran',
        name: 'pembayaran',
        component: () => import('pages/keuangan/IndeksPembayaran.vue'),
        meta: { perluAuth: true }
      },
      {
        path: '/laporan',
        name: 'laporan',
        component: () => import('pages/laporan/IndeksLaporan.vue'),
        meta: { perluAuth: true, peran: ['admin', 'staf'] }
      }
    ]
  },
  {
    path: '/auth',
    component: () => import('layouts/TataLetakAuth.vue'),
    children: [
      {
        path: 'masuk',
        name: 'masuk',
        component: () => import('pages/auth/Masuk.vue')
      },
      {
        path: 'lupa-kata-sandi',
        name: 'lupa-kata-sandi',
        component: () => import('pages/auth/LupaKataSandi.vue')
      }
    ]
  }
]
```

## State Management

### Struktur Pinia Store

```javascript
// stores/mahasiswa.js
export const useMahasiswaStore = defineStore('mahasiswa', () => {
  const mahasiswa = ref([])
  const mahasiswaSaatIni = ref(null)
  const sedangMemuat = ref(false)
  const halaman = ref({
    halaman_saat_ini: 1,
    per_halaman: 20,
    total: 0
  })
  
  const ambilMahasiswa = async (params = {}) => {
    sedangMemuat.value = true
    try {
      const response = await api.get('/mahasiswa', { params })
      mahasiswa.value = response.data.data
      halaman.value = response.data.meta
    } catch (error) {
      console.error('Error mengambil mahasiswa:', error)
    } finally {
      sedangMemuat.value = false
    }
  }
  
  const buatMahasiswa = async (dataMahasiswa) => {
    sedangMemuat.value = true
    try {
      const response = await api.post('/mahasiswa', dataMahasiswa)
      mahasiswa.value.push(response.data.data)
      return response.data.data
    } catch (error) {
      throw error
    } finally {
      sedangMemuat.value = false
    }
  }
  
  return {
    mahasiswa: readonly(mahasiswa),
    mahasiswaSaatIni: readonly(mahasiswaSaatIni),
    sedangMemuat: readonly(sedangMemuat),
    halaman: readonly(halaman),
    ambilMahasiswa,
    buatMahasiswa
  }
})
```

## External System Integration

### Arsitektur Integrasi PDDIKTI

```mermaid
sequenceDiagram
    participant SIA as Sistem SIA
    participant SYNC as Layanan Sinkronisasi
    participant PDDIKTI as PDDIKTI API
    participant DB as Database
    
    Note over SIA,DB: Sinkronisasi Data Mahasiswa
    SIA->>SYNC: Picu Sinkronisasi Mahasiswa
    SYNC->>DB: Ambil Update Mahasiswa
    SYNC->>PDDIKTI: Kirim Data Mahasiswa
    PDDIKTI->>SYNC: Response Konfirmasi
    SYNC->>DB: Update Status Sinkronisasi
    SYNC->>SIA: Notifikasi Sinkronisasi Selesai
    
    Note over SIA,DB: Pelaporan Nilai
    SIA->>SYNC: Submit Laporan Nilai
    SYNC->>DB: Validasi Data Nilai
    SYNC->>PDDIKTI: Submit Nilai
    PDDIKTI->>SYNC: Response Validasi
    SYNC->>DB: Update Status Laporan
    SYNC->>SIA: Update Status Laporan
```

### Integrasi Payment Gateway

```mermaid
flowchart TD
    MAHASISWA[Mahasiswa Inisiasi Pembayaran] --> SIA[Sistem SIA]
    SIA --> VALIDASI[Validasi Permintaan Pembayaran]
    VALIDASI --> GATEWAY[Payment Gateway]
    GATEWAY --> BANK[Bank/E-wallet]
    BANK --> CALLBACK[Callback Pembayaran]
    CALLBACK --> SIA
    SIA --> UPDATE[Update Status Pembayaran]
    UPDATE --> NOTIFIKASI[Kirim Notifikasi]
    NOTIFIKASI --> KWITANSI[Generate Kwitansi]
```

## Strategi Testing

### Framework Unit Testing

```javascript
// tests/components/FormMahasiswa.test.js
describe('Komponen FormMahasiswa', () => {
  let wrapper
  
  beforeEach(() => {
    wrapper = mount(FormMahasiswa, {
      props: {
        mode: 'buat'
      },
      global: {
        plugins: [createTestingPinia()]
      }
    })
  })
  
  test('validasi field wajib', async () => {
    const tombolSubmit = wrapper.find('[data-test="tombol-submit"]')
    await tombolSubmit.trigger('click')
    
    expect(wrapper.find('[data-test="error-nama"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="error-email"]').exists()).toBe(true)
  })
  
  test('submit form dengan data valid', async () => {
    await wrapper.find('[data-test="input-nama"]').setValue('Ahmad Sutanto')
    await wrapper.find('[data-test="input-email"]').setValue('ahmad@example.com')
    
    const tombolSubmit = wrapper.find('[data-test="tombol-submit"]')
    await tombolSubmit.trigger('click')
    
    expect(wrapper.emitted('submit')).toBeTruthy()
  })
})
```

### Framework Testing Backend

```php
class MahasiswaControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_dapat_membuat_mahasiswa()
    {
        $pengguna = Pengguna::factory()->create(['peran' => 'admin']);
        $programStudi = ProgramStudi::factory()->create();
        
        $dataMahasiswa = [
            'data_pribadi' => [
                'nama_lengkap' => 'Ahmad Sutanto',
                'tanggal_lahir' => '2000-01-15',
                'tempat_lahir' => 'Jakarta',
                'jenis_kelamin' => 'laki-laki'
            ],
            'data_kontak' => [
                'email' => 'ahmad@example.com',
                'telepon' => '+6281234567890'
            ],
            'data_akademik' => [
                'id_program_studi' => $programStudi->id,
                'tahun_masuk' => 2024
            ]
        ];
        
        $response = $this->actingAs($pengguna)
            ->postJson('/api/mahasiswa', $dataMahasiswa);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nomor_mahasiswa',
                    'nim',
                    'data_pribadi',
                    'data_kontak',
                    'data_akademik'
                ]
            ]);
        
        $this->assertDatabaseHas('mahasiswa', [
            'data_pribadi->nama_lengkap' => 'Ahmad Sutanto'
        ]);
    }
    
    public function test_validasi_field_wajib()
    {
        $pengguna = Pengguna::factory()->create(['peran' => 'admin']);
        
        $response = $this->actingAs($pengguna)
            ->postJson('/api/mahasiswa', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data_pribadi.nama_lengkap',
                'data_kontak.email',
                'data_akademik.id_program_studi'
            ]);
    }
}
```

### Testing Integrasi

```php
class MahasiswaPengambilanMataKuliahIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_alur_pengambilan_mata_kuliah_lengkap()
    {
        // Setup
        $mahasiswa = Mahasiswa::factory()->create();
        $mataKuliah = MataKuliah::factory()->create(['sks' => 3]);
        $periodeAkademik = PeriodeAkademik::factory()->create(['status' => 'aktif']);
        
        // Test pengambilan mata kuliah
        $response = $this->actingAs($mahasiswa->pengguna)
            ->postJson('/api/pengambilan-mata-kuliah', [
                'id_mata_kuliah' => $mataKuliah->id,
                'id_periode_akademik' => $periodeAkademik->id
            ]);
        
        $response->assertStatus(201);
        
        // Verifikasi pengambilan dibuat
        $this->assertDatabaseHas('pengambilan_mata_kuliah', [
            'id_mahasiswa' => $mahasiswa->id,
            'id_mata_kuliah' => $mataKuliah->id,
            'status' => 'terdaftar'
        ]);
        
        // Test submit nilai
        $dosen = Dosen::factory()->create();
        $response = $this->actingAs($dosen->pengguna)
            ->postJson('/api/nilai', [
                'id_mahasiswa' => $mahasiswa->id,
                'id_mata_kuliah' => $mataKuliah->id,
                'id_periode_akademik' => $periodeAkademik->id,
                'nilai_angka' => 3.5,
                'nilai_huruf' => 'B+'
            ]);
        
        $response->assertStatus(201);
        
        // Verifikasi perhitungan IPK
        $mahasiswa->refresh();
        $this->assertEquals(3.5, $mahasiswa->ipk);
    }
}
```

## Keamanan Sistem

### Autentikasi dan Otorisasi

```php
// Middleware untuk kontrol akses berbasis peran
class PeriksaPeranMiddleware
{
    public function handle($request, Closure $next, ...$peran)
    {
        if (!auth()->check()) {
            return response()->json(['pesan' => 'Tidak terautentikasi'], 401);
        }
        
        $penggunaSaatIni = auth()->user();
        
        if (!in_array($penggunaSaatIni->peran, $peran)) {
            return response()->json(['pesan' => 'Akses ditolak'], 403);
        }
        
        return $next($request);
    }
}
```

### Enkripsi Data Sensitif

```php
// Enkripsi data pribadi mahasiswa
class Mahasiswa extends Model
{
    protected $casts = [
        'data_pribadi' => 'encrypted:array',
        'data_kontak' => 'encrypted:array'
    ];
    
    // Accessor untuk data yang sudah didekripsi
    public function getDataPribadiTerdekripsiAttribute()
    {
        return decrypt($this->data_pribadi);
    }
}
```

### Validasi Input dan Sanitasi

```php
class MahasiswaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'data_pribadi.nama_lengkap' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'data_pribadi.tanggal_lahir' => 'required|date|before:today',
            'data_kontak.email' => 'required|email|unique:mahasiswa,data_kontak->email',
            'data_kontak.telepon' => 'required|regex:/^\+62[0-9]{9,12}$/',
            'nim' => 'required|unique:mahasiswa,nim|regex:/^[0-9]{8}$/'
        ];
    }
    
    public function authorize()
    {
        return auth()->user()->peran === 'admin' || auth()->user()->peran === 'staf';
    }
}
```

## Optimasi Performa

### Strategi Caching

```php
// Service untuk cache data mahasiswa
class MahasiswaService
{
    public function ambilMahasiswaCache($id)
    {
        return Cache::remember("mahasiswa.{$id}", 3600, function () use ($id) {
            return Mahasiswa::with(['programStudi', 'nilai', 'pembayaran'])
                ->find($id);
        });
    }
    
    public function hapusCacheMahasiswa($id)
    {
        Cache::forget("mahasiswa.{$id}");
        Cache::forget("daftar_mahasiswa");
    }
}
```

### Optimasi Database

```sql
-- Index untuk performa query
CREATE INDEX idx_mahasiswa_program_studi ON mahasiswa(id_program_studi);
CREATE INDEX idx_mahasiswa_status ON mahasiswa(status);
CREATE INDEX idx_nilai_mahasiswa_periode ON nilai(id_mahasiswa, id_periode_akademik);
CREATE INDEX idx_pembayaran_status ON pembayaran(status_pembayaran);
CREATE INDEX idx_pengambilan_periode ON pengambilan_mata_kuliah(id_periode_akademik);

-- Partisi tabel untuk data besar
CREATE TABLE nilai_2024 PARTITION OF nilai
FOR VALUES FROM ('2024-01-01') TO ('2025-01-01');
```

### Queue Jobs untuk Proses Berat

```php
// Job untuk sinkronisasi PDDIKTI
class SinkronisasiPDDIKTIJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $mahasiswa;
    
    public function __construct(Mahasiswa $mahasiswa)
    {
        $this->mahasiswa = $mahasiswa;
    }
    
    public function handle()
    {
        try {
            $client = new PDDIKTIClient();
            $response = $client->kirimDataMahasiswa([
                'nim' => $this->mahasiswa->nim,
                'nama' => $this->mahasiswa->data_pribadi['nama_lengkap'],
                'program_studi' => $this->mahasiswa->programStudi->kode_program_studi
            ]);
            
            $this->mahasiswa->update([
                'status_sinkronisasi_pddikti' => 'berhasil',
                'terakhir_sinkronisasi' => now()
            ]);
            
        } catch (Exception $e) {
            Log::error('Gagal sinkronisasi PDDIKTI: ' . $e->getMessage());
            
            $this->mahasiswa->update([
                'status_sinkronisasi_pddikti' => 'gagal'
            ]);
        }
    }
}
```

## Monitoring dan Logging

### Sistem Logging

```php
// Custom log channel untuk audit
class AuditLogger
{
    public static function logAktivitasMahasiswa($mahasiswa, $aktivitas, $detail = [])
    {
        Log::channel('audit')->info('Aktivitas Mahasiswa', [
            'id_mahasiswa' => $mahasiswa->id,
            'nim' => $mahasiswa->nim,
            'aktivitas' => $aktivitas,
            'detail' => $detail,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }
}

// Middleware untuk logging request
class LogRequestMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        Log::channel('requests')->info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'response_status' => $response->status(),
            'duration' => microtime(true) - LARAVEL_START
        ]);
        
        return $response;
    }
}
```

### Health Check Endpoints

```php
// Controller untuk monitoring kesehatan sistem
class HealthCheckController extends Controller
{
    public function index()
    {
        $checks = [
            'database' => $this->cekDatabase(),
            'redis' => $this->cekRedis(),
            'storage' => $this->cekStorage(),
            'pddikti_api' => $this->cekPDDIKTIAPI()
        ];
        
        $status = collect($checks)->every(fn($check) => $check['status'] === 'ok')
            ? 'healthy' 
            : 'unhealthy';
        
        return response()->json([
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'checks' => $checks
        ]);
    }
    
    private function cekDatabase()
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok', 'message' => 'Database terhubung'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Database tidak terhubung'];
        }
    }
}
```

## Deployment dan DevOps

### Docker Configuration

```dockerfile
# Dockerfile untuk aplikasi Laravel
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]
```

### Docker Compose untuk Development

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: sia-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - sia-network
    depends_on:
      - database
      - redis

  nginx:
    image: nginx:alpine
    container_name: sia-nginx
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    networks:
      - sia-network
    depends_on:
      - app

  database:
    image: mysql:8.0
    container_name: sia-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: sia_database
      MYSQL_ROOT_PASSWORD: rahasia123
      MYSQL_USER: sia_user
      MYSQL_PASSWORD: sia_password
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - sia-network

  redis:
    image: redis:alpine
    container_name: sia-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - sia-network

volumes:
  mysql_data:

networks:
  sia-network:
    driver: bridge
```

### CI/CD Pipeline

```yaml
# .github/workflows/deploy.yml
name: Deploy SIA Application

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: sia_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql
    
    - name: Install Dependencies
      run: composer install --no-interaction --prefer-dist --optimize-autoloader
    
    - name: Generate Application Key
      run: php artisan key:generate
    
    - name: Run Database Migrations
      run: php artisan migrate --force
      env:
        DB_CONNECTION: mysql
        DB_HOST: 127.0.0.1
        DB_PORT: 3306
        DB_DATABASE: sia_test
        DB_USERNAME: root
        DB_PASSWORD: ''
    
    - name: Run Tests
      run: php artisan test
      env:
        DB_CONNECTION: mysql
        DB_HOST: 127.0.0.1
        DB_PORT: 3306
        DB_DATABASE: sia_test
        DB_USERNAME: root
        DB_PASSWORD: ''

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - name: Deploy to Production
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.KEY }}
        script: |
          cd /var/www/sia
          git pull origin main
          composer install --no-interaction --optimize-autoloader --no-dev
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          php artisan queue:restart
          sudo systemctl reload nginx
