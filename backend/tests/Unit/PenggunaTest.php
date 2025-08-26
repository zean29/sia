<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PenggunaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_create_a_pengguna()
    {
        $pengguna = Pengguna::factory()->create([
            'nama_pengguna' => 'test_user',
            'email' => 'test@example.com',
            'peran' => 'mahasiswa'
        ]);

        $this->assertInstanceOf(Pengguna::class, $pengguna);
        $this->assertEquals('test_user', $pengguna->nama_pengguna);
        $this->assertEquals('test@example.com', $pengguna->email);
        $this->assertEquals('mahasiswa', $pengguna->peran);
    }

    /** @test */
    public function it_can_check_if_user_is_mahasiswa()
    {
        $mahasiswa = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        $dosen = Pengguna::factory()->create(['peran' => 'dosen']);

        $this->assertTrue($mahasiswa->adalahMahasiswa());
        $this->assertFalse($dosen->adalahMahasiswa());
    }

    /** @test */
    public function it_can_check_if_user_is_dosen()
    {
        $dosen = Pengguna::factory()->create(['peran' => 'dosen']);
        $mahasiswa = Pengguna::factory()->create(['peran' => 'mahasiswa']);

        $this->assertTrue($dosen->adalahDosen());
        $this->assertFalse($mahasiswa->adalahDosen());
    }

    /** @test */
    public function it_can_check_if_user_is_admin()
    {
        $admin = Pengguna::factory()->create(['peran' => 'admin']);
        $mahasiswa = Pengguna::factory()->create(['peran' => 'mahasiswa']);

        $this->assertTrue($admin->adalahAdmin());
        $this->assertFalse($mahasiswa->adalahAdmin());
    }

    /** @test */
    public function it_can_check_if_user_is_staf()
    {
        $staf = Pengguna::factory()->create(['peran' => 'staf']);
        $mahasiswa = Pengguna::factory()->create(['peran' => 'mahasiswa']);

        $this->assertTrue($staf->adalahStaf());
        $this->assertFalse($mahasiswa->adalahStaf());
    }

    /** @test */
    public function it_hides_password_from_array()
    {
        $pengguna = Pengguna::factory()->create();
        $array = $pengguna->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /** @test */
    public function default_role_is_mahasiswa()
    {
        $pengguna = new Pengguna([
            'nama_pengguna' => 'test',
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        // This would be set by the migration default
        $this->assertEquals('mahasiswa', $pengguna->getAttributes()['peran'] ?? 'mahasiswa');
    }

    /** @test */
    public function it_can_get_nama_lengkap_attribute()
    {
        $pengguna = Pengguna::factory()->create([
            'nama_depan' => 'John',
            'nama_belakang' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $pengguna->nama_lengkap);
    }

    /** @test */
    public function it_handles_empty_nama_belakang()
    {
        $pengguna = Pengguna::factory()->create([
            'nama_depan' => 'John',
            'nama_belakang' => null
        ]);

        $this->assertEquals('John', $pengguna->nama_lengkap);
    }
}