<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\IntegrasiPddikti;
use App\Models\ProgramStudi;
use App\Models\Fakultas;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Http;

class PddiktiIntegrationApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $adminUser;
    private $fakultas;
    private $programStudi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = Pengguna::factory()->create(['peran' => 'admin']);
        
        $this->fakultas = Fakultas::factory()->create();
        $this->programStudi = ProgramStudi::factory()->create([
            'id_fakultas' => $this->fakultas->id
        ]);
    }

    /** @test */
    public function admin_can_sync_mahasiswa_to_pddikti()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        // Mock successful PDDIKTI API response
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'id_pddikti' => 'PDDIKTI123',
                'message' => 'Data berhasil disimpan'
            ], 200)
        ]);

        $mahasiswa = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id,
            'nim' => '2024001001',
            'data_pribadi' => [
                'nama_lengkap' => 'Test Student',
                'jenis_kelamin' => 'laki-laki',
                'tanggal_lahir' => '2000-01-01',
                'tempat_lahir' => 'Jakarta',
                'agama' => 'islam',
                'kewarganegaraan' => 'Indonesia'
            ]
        ]);

        $response = $this->postJson("/api/pddikti/sync/mahasiswa/{$mahasiswa->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Mahasiswa berhasil disinkronisasi ke PDDIKTI'
                ]);

        // Check if integration record was created
        $this->assertDatabaseHas('integrasi_pddikti', [
            'entitas_type' => Mahasiswa::class,
            'entitas_id' => $mahasiswa->id,
            'jenis_data' => 'mahasiswa',
            'status_sinkronisasi' => 'sync_berhasil',
            'id_pddikti' => 'PDDIKTI123'
        ]);
    }

    /** @test */
    public function admin_can_sync_dosen_to_pddikti()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'id_pddikti' => 'PDDIKTI_DOSEN_123',
                'message' => 'Data dosen berhasil disimpan'
            ], 200)
        ]);

        $dosen = Dosen::factory()->create([
            'id_fakultas' => $this->fakultas->id,
            'nidn' => '1234567890',
            'data_pribadi' => [
                'nama_lengkap' => 'Dr. Test Lecturer',
                'jenis_kelamin' => 'perempuan',
                'tanggal_lahir' => '1980-05-15',
                'tempat_lahir' => 'Bandung',
                'agama' => 'kristen'
            ]
        ]);

        $response = $this->postJson("/api/pddikti/sync/dosen/{$dosen->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Dosen berhasil disinkronisasi ke PDDIKTI'
                ]);

        $this->assertDatabaseHas('integrasi_pddikti', [
            'entitas_type' => Dosen::class,
            'entitas_id' => $dosen->id,
            'jenis_data' => 'dosen',
            'status_sinkronisasi' => 'sync_berhasil'
        ]);
    }

    /** @test */
    public function admin_can_get_sync_status()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        // Create test data
        $mahasiswa1 = Mahasiswa::factory()->create(['id_program_studi' => $this->programStudi->id]);
        $mahasiswa2 = Mahasiswa::factory()->create(['id_program_studi' => $this->programStudi->id]);
        $dosen1 = Dosen::factory()->create(['id_fakultas' => $this->fakultas->id]);

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

        $response = $this->getJson('/api/pddikti/status');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_mahasiswa',
                        'mahasiswa_synced',
                        'total_dosen',
                        'dosen_synced',
                        'total_mata_kuliah',
                        'mata_kuliah_synced',
                        'total_nilai',
                        'nilai_synced',
                        'total_failed',
                        'last_sync'
                    ]
                ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_mahasiswa']);
        $this->assertEquals(1, $data['mahasiswa_synced']);
        $this->assertEquals(1, $data['total_dosen']);
        $this->assertEquals(0, $data['dosen_synced']);
        $this->assertEquals(1, $data['total_failed']);
    }

    /** @test */
    public function admin_can_get_integration_records()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $mahasiswa = Mahasiswa::factory()->create(['id_program_studi' => $this->programStudi->id]);
        
        $integration = IntegrasiPddikti::create([
            'entitas_type' => Mahasiswa::class,
            'entitas_id' => $mahasiswa->id,
            'jenis_data' => 'mahasiswa',
            'status_sinkronisasi' => 'sync_berhasil',
            'id_pddikti' => 'PDDIKTI123',
            'terakhir_sync' => now()
        ]);

        $response = $this->getJson('/api/pddikti/records');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'jenis_data',
                                'status_sinkronisasi',
                                'id_pddikti',
                                'terakhir_sync'
                            ]
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
    }

    /** @test */
    public function sync_handles_pddikti_api_failure()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        Http::fake([
            '*' => Http::response([
                'error' => 'Invalid data format',
                'details' => 'Field nama_mahasiswa is required'
            ], 400)
        ]);

        $mahasiswa = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id
        ]);

        $response = $this->postJson("/api/pddikti/sync/mahasiswa/{$mahasiswa->id}");

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false
                ]);

        $this->assertDatabaseHas('integrasi_pddikti', [
            'entitas_type' => Mahasiswa::class,
            'entitas_id' => $mahasiswa->id,
            'status_sinkronisasi' => 'sync_gagal'
        ]);
    }

    /** @test */
    public function admin_can_bulk_sync_mahasiswa()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'id_pddikti' => 'PDDIKTI_BULK_123',
                'message' => 'Data berhasil disimpan'
            ], 200)
        ]);

        $mahasiswa1 = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id,
            'data_pribadi' => ['nama_lengkap' => 'Student 1']
        ]);
        $mahasiswa2 = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id,
            'data_pribadi' => ['nama_lengkap' => 'Student 2']
        ]);

        $response = $this->postJson('/api/pddikti/bulk-sync/mahasiswa', [
            'mahasiswa_ids' => [$mahasiswa1->id, $mahasiswa2->id]
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'total_processed',
                        'successful',
                        'failed',
                        'results'
                    ]
                ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_processed']);
        $this->assertEquals(2, $data['successful']);
        $this->assertEquals(0, $data['failed']);
    }

    /** @test */
    public function non_admin_cannot_access_pddikti_endpoints()
    {
        $mahasiswaUser = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        Sanctum::actingAs($mahasiswaUser, ['*']);

        $mahasiswa = Mahasiswa::factory()->create(['id_program_studi' => $this->programStudi->id]);

        $response = $this->postJson("/api/pddikti/sync/mahasiswa/{$mahasiswa->id}");
        $response->assertStatus(403);

        $response = $this->getJson('/api/pddikti/status');
        $response->assertStatus(403);

        $response = $this->getJson('/api/pddikti/records');
        $response->assertStatus(403);

        $response = $this->postJson('/api/pddikti/bulk-sync/mahasiswa', []);
        $response->assertStatus(403);
    }

    /** @test */
    public function sync_validates_entity_exists()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->postJson('/api/pddikti/sync/mahasiswa/99999');
        $response->assertStatus(404);

        $response = $this->postJson('/api/pddikti/sync/dosen/99999');
        $response->assertStatus(404);
    }

    /** @test */
    public function bulk_sync_validates_request_data()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->postJson('/api/pddikti/bulk-sync/mahasiswa', []);
        $response->assertStatus(422)
               ->assertJsonValidationErrors(['mahasiswa_ids']);

        $response = $this->postJson('/api/pddikti/bulk-sync/mahasiswa', [
            'mahasiswa_ids' => 'not_an_array'
        ]);
        $response->assertStatus(422)
               ->assertJsonValidationErrors(['mahasiswa_ids']);

        $response = $this->postJson('/api/pddikti/bulk-sync/mahasiswa', [
            'mahasiswa_ids' => []
        ]);
        $response->assertStatus(422)
               ->assertJsonValidationErrors(['mahasiswa_ids']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_pddikti_endpoints()
    {
        $mahasiswa = Mahasiswa::factory()->create(['id_program_studi' => $this->programStudi->id]);

        $response = $this->postJson("/api/pddikti/sync/mahasiswa/{$mahasiswa->id}");
        $response->assertStatus(401);

        $response = $this->getJson('/api/pddikti/status');
        $response->assertStatus(401);

        $response = $this->getJson('/api/pddikti/records');
        $response->assertStatus(401);

        $response = $this->postJson('/api/pddikti/bulk-sync/mahasiswa', []);
        $response->assertStatus(401);
    }
}