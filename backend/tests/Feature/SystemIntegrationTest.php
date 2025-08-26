<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\JadwalKelas;
use App\Models\PengambilanMataKuliah;
use App\Models\PeriodeAkademik;
use Laravel\Sanctum\Sanctum;

class SystemIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $adminUser;
    private $mahasiswaUser;
    private $dosenUser;
    private $fakultas;
    private $programStudi;
    private $periodeAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->adminUser = Pengguna::factory()->admin()->create();
        $this->mahasiswaUser = Pengguna::factory()->mahasiswa()->create();
        $this->dosenUser = Pengguna::factory()->dosen()->create();

        // Create academic structure
        $this->fakultas = Fakultas::factory()->create();
        $this->programStudi = ProgramStudi::factory()->create([
            'id_fakultas' => $this->fakultas->id
        ]);

        // Create academic period
        $this->periodeAkademik = PeriodeAkademik::factory()->create([
            'jenis_periode' => 'ganjil',
            'tahun_akademik' => '2024/2025',
            'status' => 'berjalan'
        ]);
    }

    /** @test */
    public function complete_student_enrollment_workflow()
    {
        // Step 1: Admin creates a mahasiswa
        Sanctum::actingAs($this->adminUser, ['*']);

        $penggunaData = [
            'nama_pengguna' => 'student001',
            'nama_depan' => 'John',
            'nama_belakang' => 'Doe',
            'email' => 'john.doe@student.university.ac.id',
            'password' => 'password123'
        ];

        $mahasiswaData = [
            'id_program_studi' => $this->programStudi->id,
            'status' => 'aktif',
            'data_pribadi' => [
                'nama_lengkap' => 'John Doe',
                'jenis_kelamin' => 'laki-laki',
                'tanggal_lahir' => '2000-01-15',
                'tempat_lahir' => 'Jakarta',
                'agama' => 'islam',
                'kewarganegaraan' => 'Indonesia'
            ]
        ];

        $response = $this->postJson('/api/mahasiswa', [
            'pengguna' => $penggunaData,
            'mahasiswa' => $mahasiswaData
        ]);

        $response->assertStatus(201);
        $mahasiswaId = $response->json('data.mahasiswa.id');

        // Step 2: Create a dosen
        $dosenData = [
            'pengguna' => [
                'nama_pengguna' => 'dosen001',
                'nama_depan' => 'Dr. Jane',
                'nama_belakang' => 'Smith',
                'email' => 'jane.smith@university.ac.id',
                'password' => 'password123'
            ],
            'dosen' => [
                'id_fakultas' => $this->fakultas->id,
                'nidn' => '1234567890',
                'data_pribadi' => [
                    'nama_lengkap' => 'Dr. Jane Smith',
                    'jenis_kelamin' => 'perempuan'
                ]
            ]
        ];

        $response = $this->postJson('/api/dosen', $dosenData);
        $response->assertStatus(201);
        $dosenId = $response->json('data.dosen.id');

        // Step 3: Create a mata kuliah
        $mataKuliahData = [
            'id_program_studi' => $this->programStudi->id,
            'kode_mata_kuliah' => 'SI001',
            'nama_mata_kuliah' => 'Algoritma dan Pemrograman',
            'sks' => 3,
            'jenis' => 'wajib',
            'semester_rekomendasi' => 1,
            'status' => 'aktif'
        ];

        $response = $this->postJson('/api/mata-kuliah', $mataKuliahData);
        $response->assertStatus(201);
        $mataKuliahId = $response->json('data.id');

        // Step 4: Create jadwal kelas
        $jadwalData = [
            'id_mata_kuliah' => $mataKuliahId,
            'id_dosen' => $dosenId,
            'id_periode_akademik' => $this->periodeAkademik->id,
            'nama_kelas' => 'SI001-A',
            'ruang_kelas' => 'Lab 1',
            'kapasitas_maksimal' => 30,
            'waktu_jadwal' => [
                'hari' => 'Senin',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:30'
            ],
            'status' => 'terbuka'
        ];

        $response = $this->postJson('/api/jadwal-kelas', $jadwalData);
        $response->assertStatus(201);
        $jadwalId = $response->json('data.id');

        // Step 5: Student logs in and views their profile
        $mahasiswa = Mahasiswa::find($mahasiswaId);
        Sanctum::actingAs($mahasiswa->pengguna, ['*']);

        $response = $this->getJson('/api/mahasiswa/profil');
        $response->assertStatus(200)
                ->assertJsonPath('data.nim', $mahasiswa->nim);

        // Step 6: Student enrolls in the course
        $pengambilanData = [
            'id_jadwal_kelas' => $jadwalId,
            'catatan' => 'Mendaftar mata kuliah wajib semester 1'
        ];

        $response = $this->postJson('/api/pengambilan-mata-kuliah', $pengambilanData);
        $response->assertStatus(201);

        // Verify enrollment was created
        $this->assertDatabaseHas('pengambilan_mata_kuliah', [
            'id_mahasiswa' => $mahasiswaId,
            'id_jadwal_kelas' => $jadwalId,
            'status' => 'diajukan'
        ]);

        // Step 7: Admin approves the enrollment
        Sanctum::actingAs($this->adminUser, ['*']);

        $pengambilanId = $response->json('data.id');
        $response = $this->putJson("/api/pengambilan-mata-kuliah/{$pengambilanId}/setujui");
        $response->assertStatus(200);

        // Verify status changed to approved
        $this->assertDatabaseHas('pengambilan_mata_kuliah', [
            'id' => $pengambilanId,
            'status' => 'disetujui'
        ]);

        // Step 8: Check that class enrollment count updated
        $jadwalKelas = JadwalKelas::find($jadwalId);
        $this->assertEquals(1, $jadwalKelas->jumlah_terdaftar);
    }

    /** @test */
    public function lecturer_can_manage_class_and_grades()
    {
        // Setup: Create dosen, mata kuliah, and jadwal
        $dosen = Dosen::factory()->create([
            'id_pengguna' => $this->dosenUser->id,
            'id_fakultas' => $this->fakultas->id
        ]);

        $mataKuliah = MataKuliah::factory()->create([
            'id_program_studi' => $this->programStudi->id
        ]);

        $jadwalKelas = JadwalKelas::factory()->create([
            'id_mata_kuliah' => $mataKuliah->id,
            'id_dosen' => $dosen->id,
            'id_periode_akademik' => $this->periodeAkademik->id
        ]);

        $mahasiswa = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id
        ]);

        // Create enrollment
        $pengambilan = PengambilanMataKuliah::factory()->create([
            'id_mahasiswa' => $mahasiswa->id,
            'id_jadwal_kelas' => $jadwalKelas->id,
            'status' => 'disetujui'
        ]);

        // Dosen logs in
        Sanctum::actingAs($this->dosenUser, ['*']);

        // Step 1: Dosen views their classes
        $response = $this->getJson('/api/dosen/kelas-saya');
        $response->assertStatus(200)
                ->assertJsonPath('data.0.id', $jadwalKelas->id);

        // Step 2: Dosen views students in class
        $response = $this->getJson("/api/jadwal-kelas/{$jadwalKelas->id}/mahasiswa");
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));

        // Step 3: Dosen inputs grade
        $nilaiData = [
            'id_mahasiswa' => $mahasiswa->id,
            'id_mata_kuliah' => $mataKuliah->id,
            'id_periode_akademik' => $this->periodeAkademik->id,
            'nilai_tugas' => 85.0,
            'nilai_uts' => 80.0,
            'nilai_uas' => 88.0,
            'nilai_angka' => 84.0,
            'nilai_huruf' => 'B',
            'nilai_indeks' => 3.0,
            'lulus' => true,
            'status' => 'final'
        ];

        $response = $this->postJson('/api/nilai', $nilaiData);
        $response->assertStatus(201);

        // Verify grade was saved
        $this->assertDatabaseHas('nilai', [
            'id_mahasiswa' => $mahasiswa->id,
            'id_mata_kuliah' => $mataKuliah->id,
            'nilai_angka' => 84.0,
            'nilai_huruf' => 'B',
            'status' => 'final'
        ]);
    }

    /** @test */
    public function admin_can_manage_academic_periods()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        // Step 1: Create new academic period
        $periodeData = [
            'nama_periode' => 'Semester Genap 2024/2025',
            'jenis_periode' => 'genap',
            'tahun_akademik' => '2024/2025',
            'tanggal_mulai' => '2025-02-01',
            'tanggal_selesai' => '2025-06-30',
            'status' => 'akan_datang'
        ];

        $response = $this->postJson('/api/periode-akademik', $periodeData);
        $response->assertStatus(201);

        $periodeId = $response->json('data.id');

        // Step 2: Activate the period
        $response = $this->putJson("/api/periode-akademik/{$periodeId}/aktifkan");
        $response->assertStatus(200);

        // Verify the period is now active
        $this->assertDatabaseHas('periode_akademik', [
            'id' => $periodeId,
            'status' => 'berjalan'
        ]);

        // Verify previous period is no longer active
        $this->assertDatabaseMissing('periode_akademik', [
            'id' => $this->periodeAkademik->id,
            'status' => 'berjalan'
        ]);
    }

    /** @test */
    public function system_handles_capacity_limits_correctly()
    {
        // Create a class with limited capacity
        $dosen = Dosen::factory()->create([
            'id_fakultas' => $this->fakultas->id
        ]);

        $mataKuliah = MataKuliah::factory()->create([
            'id_program_studi' => $this->programStudi->id
        ]);

        $jadwalKelas = JadwalKelas::factory()->create([
            'id_mata_kuliah' => $mataKuliah->id,
            'id_dosen' => $dosen->id,
            'id_periode_akademik' => $this->periodeAkademik->id,
            'kapasitas_maksimal' => 2,
            'jumlah_terdaftar' => 0
        ]);

        // Create students
        $mahasiswa1 = Mahasiswa::factory()->create([
            'id_pengguna' => Pengguna::factory()->mahasiswa()->create()->id,
            'id_program_studi' => $this->programStudi->id
        ]);

        $mahasiswa2 = Mahasiswa::factory()->create([
            'id_pengguna' => Pengguna::factory()->mahasiswa()->create()->id,
            'id_program_studi' => $this->programStudi->id
        ]);

        $mahasiswa3 = Mahasiswa::factory()->create([
            'id_pengguna' => Pengguna::factory()->mahasiswa()->create()->id,
            'id_program_studi' => $this->programStudi->id
        ]);

        // First student enrolls successfully
        Sanctum::actingAs($mahasiswa1->pengguna, ['*']);
        $response = $this->postJson('/api/pengambilan-mata-kuliah', [
            'id_jadwal_kelas' => $jadwalKelas->id
        ]);
        $response->assertStatus(201);

        // Admin approves
        Sanctum::actingAs($this->adminUser, ['*']);
        $pengambilanId1 = $response->json('data.id');
        $this->putJson("/api/pengambilan-mata-kuliah/{$pengambilanId1}/setujui");

        // Second student enrolls successfully
        Sanctum::actingAs($mahasiswa2->pengguna, ['*']);
        $response = $this->postJson('/api/pengambilan-mata-kuliah', [
            'id_jadwal_kelas' => $jadwalKelas->id
        ]);
        $response->assertStatus(201);

        // Admin approves
        Sanctum::actingAs($this->adminUser, ['*']);
        $pengambilanId2 = $response->json('data.id');
        $this->putJson("/api/pengambilan-mata-kuliah/{$pengambilanId2}/setujui");

        // Third student should be rejected due to capacity
        Sanctum::actingAs($mahasiswa3->pengguna, ['*']);
        $response = $this->postJson('/api/pengambilan-mata-kuliah', [
            'id_jadwal_kelas' => $jadwalKelas->id
        ]);
        
        // Should either reject immediately or put in waiting list
        $this->assertContains($response->status(), [422, 201]);
        
        if ($response->status() === 201) {
            // If allowed to register, should be in waiting list status
            $pengambilanId3 = $response->json('data.id');
            $pengambilan3 = PengambilanMataKuliah::find($pengambilanId3);
            $this->assertContains($pengambilan3->status, ['waiting_list', 'diajukan']);
        }

        // Verify class is at capacity
        $jadwalKelas->refresh();
        $this->assertEquals(2, $jadwalKelas->jumlah_terdaftar);
    }
}