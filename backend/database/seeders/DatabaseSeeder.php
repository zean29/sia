<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use App\Models\Fakultas;
use App\Models\ProgramStudi;
use App\Models\PeriodeAkademik;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Staf;
use App\Models\MataKuliah;
use App\Models\JadwalKelas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat data admin sistem
        $this->seedAdmin();
        
        // 2. Buat data fakultas
        $this->seedFakultas();
        
        // 3. Buat data program studi
        $this->seedProgramStudi();
        
        // 4. Buat data periode akademik
        $this->seedPeriodeAkademik();
        
        // 5. Buat data dosen
        $this->seedDosen();
        
        // 6. Buat data mahasiswa
        $this->seedMahasiswa();
        
        // 7. Buat data staf
        $this->seedStaf();
        
        // 8. Buat data mata kuliah
        $this->seedMataKuliah();
        
        // 9. Buat data jadwal kelas
        $this->seedJadwalKelas();
    }

    private function seedAdmin(): void
    {
        Pengguna::create([
            'nama_pengguna' => 'admin',
            'email' => 'admin@sia.ac.id',
            'kata_sandi' => Hash::make('admin123'),
            'peran' => 'admin',
            'aktif' => true,
            'email_terverifikasi_pada' => now(),
        ]);

        Pengguna::create([
            'nama_pengguna' => 'super_admin',
            'email' => 'superadmin@sia.ac.id',
            'kata_sandi' => Hash::make('superadmin123'),
            'peran' => 'admin',
            'aktif' => true,
            'email_terverifikasi_pada' => now(),
        ]);
    }

    private function seedFakultas(): void
    {
        $fakultasList = [
            [
                'kode_fakultas' => 'FT',
                'nama_fakultas' => 'Fakultas Teknik',
                'deskripsi' => 'Fakultas yang menyelenggarakan program pendidikan tinggi di bidang teknik dan rekayasa',
                'dekan' => 'Prof. Dr. Ir. Ahmad Susanto, M.T.',
                'wakil_dekan' => 'Dr. Ir. Budi Santoso, M.T.',
                'kontak_fakultas' => [
                    'telepon' => '+62-21-1234567',
                    'email' => 'ft@sia.ac.id',
                    'alamat' => 'Jl. Pendidikan No. 1, Jakarta'
                ],
                'status_aktif' => 'aktif',
            ],
            [
                'kode_fakultas' => 'FE',
                'nama_fakultas' => 'Fakultas Ekonomi',
                'deskripsi' => 'Fakultas yang menyelenggarakan program pendidikan tinggi di bidang ekonomi dan bisnis',
                'dekan' => 'Prof. Dr. Siti Nurhaliza, S.E., M.M.',
                'wakil_dekan' => 'Dr. Rudi Hartono, S.E., M.B.A.',
                'kontak_fakultas' => [
                    'telepon' => '+62-21-1234568',
                    'email' => 'fe@sia.ac.id',
                    'alamat' => 'Jl. Pendidikan No. 2, Jakarta'
                ],
                'status_aktif' => 'aktif',
            ],
            [
                'kode_fakultas' => 'FIKOM',
                'nama_fakultas' => 'Fakultas Ilmu Komunikasi',
                'deskripsi' => 'Fakultas yang menyelenggarakan program pendidikan tinggi di bidang komunikasi dan media',
                'dekan' => 'Dr. Maya Sari, S.Sos., M.I.Kom.',
                'wakil_dekan' => 'Dr. Andi Wijaya, S.Sos., M.I.Kom.',
                'kontak_fakultas' => [
                    'telepon' => '+62-21-1234569',
                    'email' => 'fikom@sia.ac.id',
                    'alamat' => 'Jl. Pendidikan No. 3, Jakarta'
                ],
                'status_aktif' => 'aktif',
            ],
        ];

        foreach ($fakultasList as $fakultas) {
            Fakultas::create($fakultas);
        }
    }

    private function seedProgramStudi(): void
    {
        $programStudiList = [
            // Fakultas Teknik
            [
                'kode_program_studi' => 'TI',
                'nama_program_studi' => 'Teknik Informatika',
                'id_fakultas' => 1,
                'jenjang' => 'S1',
                'akreditasi' => 'A',
                'tanggal_akreditasi' => '2022-06-15',
                'kepala_program_studi' => 'Dr. Ir. Bambang Suharto, M.T.',
                'kapasitas_mahasiswa' => 120,
                'visi' => 'Menjadi program studi teknik informatika yang unggul dan berdaya saing global',
                'misi' => 'Menyelenggarakan pendidikan, penelitian, dan pengabdian masyarakat di bidang teknologi informasi',
                'status_aktif' => 'aktif',
            ],
            [
                'kode_program_studi' => 'SI',
                'nama_program_studi' => 'Sistem Informasi',
                'id_fakultas' => 1,
                'jenjang' => 'S1',
                'akreditasi' => 'B+',
                'tanggal_akreditasi' => '2021-12-10',
                'kepala_program_studi' => 'Dr. Ir. Citra Dewi, M.Kom.',
                'kapasitas_mahasiswa' => 100,
                'visi' => 'Menjadi program studi sistem informasi yang berkualitas dan inovatif',
                'misi' => 'Menghasilkan lulusan yang kompeten di bidang sistem informasi',
                'status_aktif' => 'aktif',
            ],
            // Fakultas Ekonomi
            [
                'kode_program_studi' => 'MJ',
                'nama_program_studi' => 'Manajemen',
                'id_fakultas' => 2,
                'jenjang' => 'S1',
                'akreditasi' => 'A',
                'tanggal_akreditasi' => '2023-03-20',
                'kepala_program_studi' => 'Dr. Eko Prabowo, S.E., M.M.',
                'kapasitas_mahasiswa' => 150,
                'visi' => 'Menjadi program studi manajemen yang terdepan dalam menghasilkan manajer profesional',
                'misi' => 'Menyelenggarakan pendidikan manajemen yang berkualitas tinggi',
                'status_aktif' => 'aktif',
            ],
            [
                'kode_program_studi' => 'AK',
                'nama_program_studi' => 'Akuntansi',
                'id_fakultas' => 2,
                'jenjang' => 'S1',
                'akreditasi' => 'B+',
                'tanggal_akreditasi' => '2022-09-15',
                'kepala_program_studi' => 'Dr. Fitri Handayani, S.E., M.Ak.',
                'kapasitas_mahasiswa' => 120,
                'visi' => 'Menjadi program studi akuntansi yang menghasilkan akuntan profesional',
                'misi' => 'Menyelenggarakan pendidikan akuntansi yang berorientasi pada praktik',
                'status_aktif' => 'aktif',
            ],
            // Fakultas Ilmu Komunikasi
            [
                'kode_program_studi' => 'IKOM',
                'nama_program_studi' => 'Ilmu Komunikasi',
                'id_fakultas' => 3,
                'jenjang' => 'S1',
                'akreditasi' => 'B',
                'tanggal_akreditasi' => '2022-11-30',
                'kepala_program_studi' => 'Dr. Desi Anwar, S.Sos., M.I.Kom.',
                'kapasitas_mahasiswa' => 80,
                'visi' => 'Menjadi program studi komunikasi yang unggul dalam era digital',
                'misi' => 'Menghasilkan komunikator yang profesional dan beretika',
                'status_aktif' => 'aktif',
            ],
        ];

        foreach ($programStudiList as $prodi) {
            ProgramStudi::create($prodi);
        }
    }

    private function seedPeriodeAkademik(): void
    {
        $periodeList = [
            [
                'kode_periode' => '20241',
                'nama_periode' => 'Semester Ganjil 2024/2025',
                'tanggal_mulai' => '2024-09-01',
                'tanggal_selesai' => '2025-01-31',
                'jenis_periode' => 'ganjil',
                'status' => 'berjalan',
                'batas_pengambilan_krs' => '2024-09-15',
                'batas_pembayaran' => '2024-09-30',
                'mulai_perkuliahan' => '2024-09-02',
                'selesai_perkuliahan' => '2024-12-20',
                'mulai_uts' => '2024-10-28',
                'selesai_uts' => '2024-11-08',
                'mulai_uas' => '2024-12-09',
                'selesai_uas' => '2024-12-20',
            ],
            [
                'kode_periode' => '20232',
                'nama_periode' => 'Semester Genap 2023/2024',
                'tanggal_mulai' => '2024-02-01',
                'tanggal_selesai' => '2024-07-31',
                'jenis_periode' => 'genap',
                'status' => 'selesai',
                'batas_pengambilan_krs' => '2024-02-15',
                'batas_pembayaran' => '2024-02-28',
                'mulai_perkuliahan' => '2024-02-05',
                'selesai_perkuliahan' => '2024-06-21',
                'mulai_uts' => '2024-04-01',
                'selesai_uts' => '2024-04-12',
                'mulai_uas' => '2024-06-10',
                'selesai_uas' => '2024-06-21',
            ],
        ];

        foreach ($periodeList as $periode) {
            PeriodeAkademik::create($periode);
        }
    }

    private function seedDosen(): void
    {
        $dosenList = [
            [
                'nama_pengguna' => 'bambang.suharto',
                'email' => 'bambang.suharto@sia.ac.id',
                'kata_sandi' => Hash::make('dosen123'),
                'peran' => 'dosen',
                'data_dosen' => [
                    'nomor_dosen' => 'DSN001',
                    'nidn' => '0101086701',
                    'nip' => '197001011995031001',
                    'id_fakultas' => 1,
                    'data_pribadi' => [
                        'nama_lengkap' => 'Dr. Ir. Bambang Suharto, M.T.',
                        'tanggal_lahir' => '1970-01-01',
                        'tempat_lahir' => 'Jakarta',
                        'jenis_kelamin' => 'laki-laki',
                        'agama' => 'islam',
                        'kewarganegaraan' => 'indonesia',
                    ],
                    'kredensial_akademik' => [
                        'gelar_depan' => 'Dr. Ir.',
                        'gelar_belakang' => 'M.T.',
                        'pendidikan_terakhir' => 'S3',
                        'universitas' => 'Institut Teknologi Bandung',
                        'bidang_keahlian' => ['Pemrograman', 'Basis Data', 'Kecerdasan Buatan'],
                    ],
                    'jabatan_fungsional' => 'lektor_kepala',
                    'tanggal_mulai_kerja' => '1995-03-01',
                ],
            ],
            [
                'nama_pengguna' => 'citra.dewi',
                'email' => 'citra.dewi@sia.ac.id',
                'kata_sandi' => Hash::make('dosen123'),
                'peran' => 'dosen',
                'data_dosen' => [
                    'nomor_dosen' => 'DSN002',
                    'nidn' => '0215087501',
                    'nip' => '197502151998032001',
                    'id_fakultas' => 1,
                    'data_pribadi' => [
                        'nama_lengkap' => 'Dr. Ir. Citra Dewi, M.Kom.',
                        'tanggal_lahir' => '1975-02-15',
                        'tempat_lahir' => 'Bandung',
                        'jenis_kelamin' => 'perempuan',
                        'agama' => 'islam',
                        'kewarganegaraan' => 'indonesia',
                    ],
                    'kredensial_akademik' => [
                        'gelar_depan' => 'Dr. Ir.',
                        'gelar_belakang' => 'M.Kom.',
                        'pendidikan_terakhir' => 'S3',
                        'universitas' => 'Universitas Indonesia',
                        'bidang_keahlian' => ['Sistem Informasi', 'E-Business', 'Data Mining'],
                    ],
                    'jabatan_fungsional' => 'lektor',
                    'tanggal_mulai_kerja' => '1998-03-01',
                ],
            ],
        ];

        foreach ($dosenList as $dosenData) {
            $pengguna = Pengguna::create([
                'nama_pengguna' => $dosenData['nama_pengguna'],
                'email' => $dosenData['email'],
                'kata_sandi' => $dosenData['kata_sandi'],
                'peran' => $dosenData['peran'],
                'aktif' => true,
                'email_terverifikasi_pada' => now(),
            ]);

            Dosen::create(array_merge($dosenData['data_dosen'], [
                'id_pengguna' => $pengguna->id,
                'data_kontak' => [
                    'telepon' => '+62-21-' . rand(1000000, 9999999),
                    'email_pribadi' => $dosenData['email'],
                ],
                'status' => 'aktif',
            ]));
        }
    }

    private function seedMahasiswa(): void
    {
        $mahasiswaList = [
            [
                'nama_pengguna' => 'ahmad.sutanto',
                'email' => 'ahmad.sutanto@student.sia.ac.id',
                'kata_sandi' => Hash::make('mahasiswa123'),
                'peran' => 'mahasiswa',
                'data_mahasiswa' => [
                    'nomor_mahasiswa' => '2024010001',
                    'nim' => '24010001',
                    'id_program_studi' => 1, // Teknik Informatika
                    'tanggal_masuk' => '2024-09-01',
                    'data_pribadi' => [
                        'nama_lengkap' => 'Ahmad Sutanto',
                        'tanggal_lahir' => '2005-05-15',
                        'tempat_lahir' => 'Jakarta',
                        'jenis_kelamin' => 'laki-laki',
                        'agama' => 'islam',
                        'kewarganegaraan' => 'indonesia',
                    ],
                    'data_kontak' => [
                        'telepon' => '+62-812-3456-7890',
                        'alamat' => 'Jl. Mawar No. 123, Jakarta Selatan',
                    ],
                    'data_akademik' => [
                        'asal_sekolah' => 'SMA Negeri 1 Jakarta',
                        'tahun_lulus' => 2024,
                        'jalur_masuk' => 'SNMPTN',
                    ],
                    'semester_aktif' => 1,
                ],
            ],
            [
                'nama_pengguna' => 'siti.nurhaliza',
                'email' => 'siti.nurhaliza@student.sia.ac.id',
                'kata_sandi' => Hash::make('mahasiswa123'),
                'peran' => 'mahasiswa',
                'data_mahasiswa' => [
                    'nomor_mahasiswa' => '2024010002',
                    'nim' => '24010002',
                    'id_program_studi' => 2, // Sistem Informasi
                    'tanggal_masuk' => '2024-09-01',
                    'data_pribadi' => [
                        'nama_lengkap' => 'Siti Nurhaliza',
                        'tanggal_lahir' => '2005-08-20',
                        'tempat_lahir' => 'Bogor',
                        'jenis_kelamin' => 'perempuan',
                        'agama' => 'islam',
                        'kewarganegaraan' => 'indonesia',
                    ],
                    'data_kontak' => [
                        'telepon' => '+62-813-4567-8901',
                        'alamat' => 'Jl. Melati No. 456, Bogor',
                    ],
                    'data_akademik' => [
                        'asal_sekolah' => 'SMA Negeri 2 Bogor',
                        'tahun_lulus' => 2024,
                        'jalur_masuk' => 'SBMPTN',
                    ],
                    'semester_aktif' => 1,
                ],
            ],
        ];

        foreach ($mahasiswaList as $mahasiswaData) {
            $pengguna = Pengguna::create([
                'nama_pengguna' => $mahasiswaData['nama_pengguna'],
                'email' => $mahasiswaData['email'],
                'kata_sandi' => $mahasiswaData['kata_sandi'],
                'peran' => $mahasiswaData['peran'],
                'aktif' => true,
                'email_terverifikasi_pada' => now(),
            ]);

            Mahasiswa::create(array_merge($mahasiswaData['data_mahasiswa'], [
                'id_pengguna' => $pengguna->id,
                'status' => 'aktif',
                'total_biaya_kuliah' => 25000000,
                'total_bayar' => 0,
                'ipk' => 0,
                'total_sks' => 0,
            ]));
        }
    }

    private function seedStaf(): void
    {
        $penggunaStaf = Pengguna::create([
            'nama_pengguna' => 'rini.kusuma',
            'email' => 'rini.kusuma@sia.ac.id',
            'kata_sandi' => Hash::make('staf123'),
            'peran' => 'staf',
            'aktif' => true,
            'email_terverifikasi_pada' => now(),
        ]);

        Staf::create([
            'id_pengguna' => $penggunaStaf->id,
            'nomor_staf' => 'STF001',
            'nip' => '198005152005032001',
            'id_fakultas' => 1,
            'data_pribadi' => [
                'nama_lengkap' => 'Rini Kusuma, S.Kom.',
                'tanggal_lahir' => '1980-05-15',
                'tempat_lahir' => 'Jakarta',
                'jenis_kelamin' => 'perempuan',
                'agama' => 'islam',
                'kewarganegaraan' => 'indonesia',
            ],
            'data_kontak' => [
                'telepon' => '+62-21-87654321',
                'email_pribadi' => 'rini.kusuma@sia.ac.id',
            ],
            'jabatan' => 'staf_akademik',
            'status_kepegawaian' => 'tetap',
            'tanggal_mulai_kerja' => '2005-03-01',
            'status' => 'aktif',
        ]);
    }

    private function seedMataKuliah(): void
    {
        $mataKuliahList = [
            // Mata Kuliah Teknik Informatika
            [
                'kode_mata_kuliah' => 'TI101',
                'nama_mata_kuliah' => 'Algoritma dan Pemrograman',
                'id_program_studi' => 1,
                'sks' => 3,
                'jenis' => 'wajib',
                'semester_rekomendasi' => 1,
                'deskripsi' => 'Mata kuliah yang membahas konsep dasar algoritma dan pemrograman',
                'kapasitas_kelas' => 40,
                'status' => 'aktif',
            ],
            [
                'kode_mata_kuliah' => 'TI102',
                'nama_mata_kuliah' => 'Basis Data',
                'id_program_studi' => 1,
                'sks' => 3,
                'jenis' => 'wajib',
                'semester_rekomendasi' => 3,
                'prasyarat' => ['mata_kuliah' => ['TI101']],
                'deskripsi' => 'Mata kuliah yang membahas konsep dan implementasi basis data',
                'kapasitas_kelas' => 40,
                'status' => 'aktif',
            ],
            // Mata Kuliah Sistem Informasi
            [
                'kode_mata_kuliah' => 'SI101',
                'nama_mata_kuliah' => 'Pengantar Sistem Informasi',
                'id_program_studi' => 2,
                'sks' => 3,
                'jenis' => 'wajib',
                'semester_rekomendasi' => 1,
                'deskripsi' => 'Mata kuliah pengenalan konsep dasar sistem informasi',
                'kapasitas_kelas' => 35,
                'status' => 'aktif',
            ],
            [
                'kode_mata_kuliah' => 'SI102',
                'nama_mata_kuliah' => 'Analisis dan Perancangan Sistem',
                'id_program_studi' => 2,
                'sks' => 3,
                'jenis' => 'wajib',
                'semester_rekomendasi' => 4,
                'prasyarat' => ['mata_kuliah' => ['SI101']],
                'deskripsi' => 'Mata kuliah yang membahas metodologi analisis dan perancangan sistem informasi',
                'kapasitas_kelas' => 35,
                'status' => 'aktif',
            ],
        ];

        foreach ($mataKuliahList as $mataKuliah) {
            MataKuliah::create($mataKuliah);
        }
    }

    private function seedJadwalKelas(): void
    {
        $jadwalKelasList = [
            [
                'id_mata_kuliah' => 1, // Algoritma dan Pemrograman
                'id_dosen' => 1, // Dr. Bambang Suharto
                'id_periode_akademik' => 1, // Semester Ganjil 2024/2025
                'nama_kelas' => 'A',
                'ruang_kelas' => 'R.101',
                'waktu_jadwal' => [
                    'hari' => 'Senin',
                    'jam_mulai' => '08:00',
                    'jam_selesai' => '10:30',
                    'gedung' => 'A',
                    'lantai' => '1',
                ],
                'kapasitas_maksimal' => 40,
                'status' => 'terbuka',
                'tanggal_buka_pendaftaran' => '2024-08-15',
                'tanggal_tutup_pendaftaran' => '2024-09-15',
            ],
            [
                'id_mata_kuliah' => 3, // Pengantar Sistem Informasi
                'id_dosen' => 2, // Dr. Citra Dewi
                'id_periode_akademik' => 1,
                'nama_kelas' => 'A',
                'ruang_kelas' => 'R.201',
                'waktu_jadwal' => [
                    'hari' => 'Selasa',
                    'jam_mulai' => '10:30',
                    'jam_selesai' => '13:00',
                    'gedung' => 'A',
                    'lantai' => '2',
                ],
                'kapasitas_maksimal' => 35,
                'status' => 'terbuka',
                'tanggal_buka_pendaftaran' => '2024-08-15',
                'tanggal_tutup_pendaftaran' => '2024-09-15',
            ],
        ];

        foreach ($jadwalKelasList as $jadwal) {
            JadwalKelas::create($jadwal);
        }
    }
}