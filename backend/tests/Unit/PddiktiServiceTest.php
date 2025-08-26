<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PddiktiService;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\IntegrasiPddikti;
use App\Models\ProgramStudi;
use App\Models\Fakultas;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class PddiktiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PddiktiService $pddiktiService;

    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('services.pddikti.base_url', 'https://api-test.pddikti.kemdikbud.go.id/v1');
        Config::set('services.pddikti.api_key', 'test-api-key');
        Config::set('services.pddikti.university_id', 'test-university-id');
        
        $this->pddiktiService = new PddiktiService();
    }

    /** @test */
    public function it_can_format_mahasiswa_data_correctly()
    {
        $pengguna = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        $programStudi = ProgramStudi::factory()->create([
            'kode_program_studi' => 'IF001',
            'jenjang' => 'S1'
        ]);

        $mahasiswa = Mahasiswa::factory()->create([
            'id_pengguna' => $pengguna->id,
            'id_program_studi' => $programStudi->id,
            'nim' => '2024001001',
            'status' => 'aktif',
            'semester_aktif' => 3,
            'ipk' => 3.5,
            'tanggal_masuk' => '2024-01-01',
            'data_pribadi' => [
                'nama_lengkap' => 'John Doe',
                'jenis_kelamin' => 'laki-laki',
                'tanggal_lahir' => '2000-01-01',
                'tempat_lahir' => 'Jakarta',
                'agama' => 'islam',
                'kewarganegaraan' => 'Indonesia'
            ],
            'data_kontak' => [
                'alamat' => 'Jl. Test No. 123',
                'telepon' => '081234567890'
            ]
        ]);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->pddiktiService);
        $method = $reflection->getMethod('formatMahasiswaData');
        $method->setAccessible(true);

        $result = $method->invoke($this->pddiktiService, $mahasiswa);

        $this->assertEquals('2024001001', $result['nim']);
        $this->assertEquals('John Doe', $result['nama_mahasiswa']);
        $this->assertEquals('L', $result['jenis_kelamin']);
        $this->assertEquals('2000-01-01', $result['tanggal_lahir']);
        $this->assertEquals('Jakarta', $result['tempat_lahir']);
        $this->assertEquals('ISLAM', $result['agama']);
        $this->assertEquals('Indonesia', $result['kewarganegaraan']);
        $this->assertEquals('IF001', $result['program_studi']);
        $this->assertEquals('S1', $result['jenjang']);
        $this->assertEquals('AKTIF', $result['status_mahasiswa']);
        $this->assertEquals(3, $result['semester_berjalan']);
        $this->assertEquals(3.5, $result['ipk']);
    }

    /** @test */
    public function it_can_format_dosen_data_correctly()
    {
        $pengguna = Pengguna::factory()->create(['peran' => 'dosen']);
        $fakultas = Fakultas::factory()->create(['kode_fakultas' => 'FTI']);

        $dosen = Dosen::factory()->create([
            'id_pengguna' => $pengguna->id,
            'id_fakultas' => $fakultas->id,
            'nidn' => '1234567890',
            'jabatan_fungsional' => 'lektor',
            'status_kepegawaian' => 'tetap',
            'data_pribadi' => [
                'nama_lengkap' => 'Dr. Jane Smith',
                'jenis_kelamin' => 'perempuan',
                'tanggal_lahir' => '1980-05-15',
                'tempat_lahir' => 'Bandung',
                'agama' => 'kristen'
            ],
            'kredensial_akademik' => [
                'pendidikan_terakhir' => 'S3'
            ],
            'bidang_keahlian' => ['Informatika', 'Sistem Informasi']
        ]);

        $reflection = new \ReflectionClass($this->pddiktiService);
        $method = $reflection->getMethod('formatDosenData');
        $method->setAccessible(true);

        $result = $method->invoke($this->pddiktiService, $dosen);

        $this->assertEquals('1234567890', $result['nidn']);
        $this->assertEquals('Dr. Jane Smith', $result['nama_dosen']);
        $this->assertEquals('P', $result['jenis_kelamin']);
        $this->assertEquals('1980-05-15', $result['tanggal_lahir']);
        $this->assertEquals('Bandung', $result['tempat_lahir']);
        $this->assertEquals('KRISTEN', $result['agama']);
        $this->assertEquals('LEKTOR', $result['jabatan_fungsional']);
        $this->assertEquals('TETAP', $result['status_kepegawaian']);
        $this->assertEquals('FTI', $result['fakultas']);
        $this->assertEquals('S3', $result['pendidikan_terakhir']);
        $this->assertEquals('Informatika,Sistem Informasi', $result['bidang_keahlian']);
    }

    /** @test */
    public function it_maps_status_mahasiswa_correctly()
    {
        $reflection = new \ReflectionClass($this->pddiktiService);
        $method = $reflection->getMethod('mapStatusMahasiswa');
        $method->setAccessible(true);

        $this->assertEquals('AKTIF', $method->invoke($this->pddiktiService, 'aktif'));
        $this->assertEquals('TIDAK_AKTIF', $method->invoke($this->pddiktiService, 'tidak_aktif'));
        $this->assertEquals('LULUS', $method->invoke($this->pddiktiService, 'lulus'));
        $this->assertEquals('DROP_OUT', $method->invoke($this->pddiktiService, 'do'));
        $this->assertEquals('SKORSING', $method->invoke($this->pddiktiService, 'skorsing'));
        $this->assertEquals('CUTI', $method->invoke($this->pddiktiService, 'cuti'));
        $this->assertEquals('AKTIF', $method->invoke($this->pddiktiService, 'unknown_status'));
    }

    /** @test */
    public function it_maps_jabatan_fungsional_correctly()
    {
        $reflection = new \ReflectionClass($this->pddiktiService);
        $method = $reflection->getMethod('mapJabatanFungsional');
        $method->setAccessible(true);

        $this->assertEquals('ASISTEN_AHLI', $method->invoke($this->pddiktiService, 'asisten_ahli'));
        $this->assertEquals('LEKTOR', $method->invoke($this->pddiktiService, 'lektor'));
        $this->assertEquals('LEKTOR_KEPALA', $method->invoke($this->pddiktiService, 'lektor_kepala'));
        $this->assertEquals('GURU_BESAR', $method->invoke($this->pddiktiService, 'guru_besar'));
        $this->assertEquals('TIDAK_ADA', $method->invoke($this->pddiktiService, 'tidak_ada'));
        $this->assertEquals('TIDAK_ADA', $method->invoke($this->pddiktiService, 'unknown_position'));
    }

    /** @test */
    public function sync_mahasiswa_creates_integration_record_on_success()
    {
        Http::fake([
            'api-test.pddikti.kemdikbud.go.id/*' => Http::response([
                'success' => true,
                'id_pddikti' => 'PDDIKTI123',
                'message' => 'Data berhasil disimpan'
            ], 200)
        ]);

        $pengguna = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        $programStudi = ProgramStudi::factory()->create();
        $mahasiswa = Mahasiswa::factory()->create([
            'id_pengguna' => $pengguna->id,
            'id_program_studi' => $programStudi->id
        ]);

        $result = $this->pddiktiService->syncMahasiswa($mahasiswa);

        $this->assertTrue($result['success']);
        $this->assertEquals('Mahasiswa berhasil disinkronisasi ke PDDIKTI', $result['message']);
        
        $this->assertDatabaseHas('integrasi_pddikti', [
            'entitas_type' => Mahasiswa::class,
            'entitas_id' => $mahasiswa->id,
            'jenis_data' => 'mahasiswa',
            'status_sinkronisasi' => 'sync_berhasil',
            'id_pddikti' => 'PDDIKTI123'
        ]);
    }

    /** @test */
    public function sync_mahasiswa_handles_api_failure()
    {
        Http::fake([
            'api-test.pddikti.kemdikbud.go.id/*' => Http::response([
                'error' => 'Invalid data format'
            ], 400)
        ]);

        $pengguna = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        $programStudi = ProgramStudi::factory()->create();
        $mahasiswa = Mahasiswa::factory()->create([
            'id_pengguna' => $pengguna->id,
            'id_program_studi' => $programStudi->id
        ]);

        $result = $this->pddiktiService->syncMahasiswa($mahasiswa);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Gagal sinkronisasi ke PDDIKTI', $result['message']);
        
        $this->assertDatabaseHas('integrasi_pddikti', [
            'entitas_type' => Mahasiswa::class,
            'entitas_id' => $mahasiswa->id,
            'jenis_data' => 'mahasiswa',
            'status_sinkronisasi' => 'sync_gagal'
        ]);
    }

    /** @test */
    public function it_can_get_sync_status_statistics()
    {
        // Create some test data
        $mahasiswa1 = Mahasiswa::factory()->create();
        $mahasiswa2 = Mahasiswa::factory()->create();
        $dosen1 = Dosen::factory()->create();

        // Create integration records
        IntegrasiPddikti::create([
            'entitas_type' => Mahasiswa::class,
            'entitas_id' => $mahasiswa1->id,
            'jenis_data' => 'mahasiswa',
            'status_sinkronisasi' => 'sync_berhasil'
        ]);

        IntegrasiPddikti::create([
            'entitas_type' => Dosen::class,
            'entitas_id' => $dosen1->id,
            'jenis_data' => 'dosen',
            'status_sinkronisasi' => 'sync_gagal'
        ]);

        $stats = $this->pddiktiService->getStatusSync();

        $this->assertEquals(2, $stats['total_mahasiswa']);
        $this->assertEquals(1, $stats['mahasiswa_synced']);
        $this->assertEquals(1, $stats['total_dosen']);
        $this->assertEquals(0, $stats['dosen_synced']);
        $this->assertEquals(1, $stats['total_failed']);
    }
}