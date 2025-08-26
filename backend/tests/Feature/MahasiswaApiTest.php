<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\Fakultas;
use Laravel\Sanctum\Sanctum;

class MahasiswaApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $adminUser;
    private $mahasiswaUser;
    private $fakultas;
    private $programStudi;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->adminUser = Pengguna::factory()->create(['peran' => 'admin']);
        $this->mahasiswaUser = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        
        $this->fakultas = Fakultas::factory()->create([
            'nama_fakultas' => 'Fakultas Teknologi Informasi',
            'kode_fakultas' => 'FTI'
        ]);

        $this->programStudi = ProgramStudi::factory()->create([
            'id_fakultas' => $this->fakultas->id,
            'nama_program_studi' => 'Sistem Informasi',
            'kode_program_studi' => 'SI001',
            'jenjang' => 'S1'
        ]);
    }

    /** @test */
    public function admin_can_get_list_of_mahasiswa()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        // Create test mahasiswa
        $mahasiswa1 = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id,
            'nim' => '2024001001'
        ]);
        $mahasiswa2 = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id,
            'nim' => '2024001002'
        ]);

        $response = $this->getJson('/api/mahasiswa');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'nim',
                                'status',
                                'program_studi' => [
                                    'nama_program_studi',
                                    'kode_program_studi'
                                ]
                            ]
                        ],
                        'current_page',
                        'total'
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(2, $response->json('data.total'));
    }

    /** @test */
    public function admin_can_create_new_mahasiswa()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $penggunaData = [
            'nama_pengguna' => 'mahasiswa001',
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
            ],
            'data_kontak' => [
                'alamat' => 'Jl. Contoh No. 123',
                'telepon' => '081234567890',
                'email' => 'john.doe@student.university.ac.id'
            ]
        ];

        $response = $this->postJson('/api/mahasiswa', [
            'pengguna' => $penggunaData,
            'mahasiswa' => $mahasiswaData
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Mahasiswa berhasil dibuat'
                ]);

        // Check if pengguna was created
        $this->assertDatabaseHas('pengguna', [
            'nama_pengguna' => 'mahasiswa001',
            'email' => 'john.doe@student.university.ac.id',
            'peran' => 'mahasiswa'
        ]);

        // Check if mahasiswa was created
        $this->assertDatabaseHas('mahasiswa', [
            'id_program_studi' => $this->programStudi->id,
            'status' => 'aktif'
        ]);
    }

    /** @test */
    public function admin_can_view_specific_mahasiswa()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $mahasiswa = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id,
            'nim' => '2024001001',
            'data_pribadi' => [
                'nama_lengkap' => 'Jane Doe'
            ]
        ]);

        $response = $this->getJson("/api/mahasiswa/{$mahasiswa->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $mahasiswa->id,
                        'nim' => '2024001001'
                    ]
                ]);
    }

    /** @test */
    public function admin_can_update_mahasiswa()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $mahasiswa = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id,
            'status' => 'aktif'
        ]);

        $updateData = [
            'status' => 'tidak_aktif',
            'data_pribadi' => [
                'nama_lengkap' => 'Updated Name',
                'jenis_kelamin' => 'perempuan'
            ]
        ];

        $response = $this->putJson("/api/mahasiswa/{$mahasiswa->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data mahasiswa berhasil diperbarui'
                ]);

        // Check if data was updated
        $mahasiswa->refresh();
        $this->assertEquals('tidak_aktif', $mahasiswa->status);
        $this->assertEquals('Updated Name', $mahasiswa->data_pribadi['nama_lengkap']);
    }

    /** @test */
    public function admin_can_delete_mahasiswa()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $mahasiswa = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id
        ]);

        $response = $this->deleteJson("/api/mahasiswa/{$mahasiswa->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Mahasiswa berhasil dihapus'
                ]);

        // Check if soft deleted
        $this->assertSoftDeleted('mahasiswa', ['id' => $mahasiswa->id]);
    }

    /** @test */
    public function mahasiswa_can_view_own_profile()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'id_pengguna' => $this->mahasiswaUser->id,
            'id_program_studi' => $this->programStudi->id,
            'nim' => '2024001001'
        ]);

        Sanctum::actingAs($this->mahasiswaUser, ['*']);

        $response = $this->getJson('/api/mahasiswa/profil');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'nim' => '2024001001'
                    ]
                ]);
    }

    /** @test */
    public function mahasiswa_can_update_own_profile()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'id_pengguna' => $this->mahasiswaUser->id,
            'id_program_studi' => $this->programStudi->id
        ]);

        Sanctum::actingAs($this->mahasiswaUser, ['*']);

        $updateData = [
            'data_kontak' => [
                'telepon' => '081999888777',
                'alamat' => 'Alamat Baru'
            ]
        ];

        $response = $this->putJson('/api/mahasiswa/profil', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Profil berhasil diperbarui'
                ]);

        $mahasiswa->refresh();
        $this->assertEquals('081999888777', $mahasiswa->data_kontak['telepon']);
    }

    /** @test */
    public function non_admin_cannot_create_mahasiswa()
    {
        Sanctum::actingAs($this->mahasiswaUser, ['*']);

        $response = $this->postJson('/api/mahasiswa', [
            'pengguna' => [],
            'mahasiswa' => []
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_view_other_mahasiswa()
    {
        $otherMahasiswa = Mahasiswa::factory()->create([
            'id_program_studi' => $this->programStudi->id
        ]);

        Sanctum::actingAs($this->mahasiswaUser, ['*']);

        $response = $this->getJson("/api/mahasiswa/{$otherMahasiswa->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function mahasiswa_creation_validates_required_fields()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->postJson('/api/mahasiswa', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['pengguna', 'mahasiswa']);
    }

    /** @test */
    public function mahasiswa_creation_validates_program_studi_exists()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $penggunaData = [
            'nama_pengguna' => 'test001',
            'nama_depan' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $mahasiswaData = [
            'id_program_studi' => 99999, // Non-existent program studi
            'status' => 'aktif'
        ];

        $response = $this->postJson('/api/mahasiswa', [
            'pengguna' => $penggunaData,
            'mahasiswa' => $mahasiswaData
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['mahasiswa.id_program_studi']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_mahasiswa_endpoints()
    {
        $response = $this->getJson('/api/mahasiswa');
        $response->assertStatus(401);

        $response = $this->postJson('/api/mahasiswa', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/mahasiswa/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/mahasiswa/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/mahasiswa/1');
        $response->assertStatus(401);
    }
}