<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\ProgramStudi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class MahasiswaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_create_a_mahasiswa()
    {
        $pengguna = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        $programStudi = ProgramStudi::factory()->create();
        
        $mahasiswa = Mahasiswa::factory()->create([
            'id_pengguna' => $pengguna->id,
            'id_program_studi' => $programStudi->id,
            'nim' => '2024001001'
        ]);

        $this->assertInstanceOf(Mahasiswa::class, $mahasiswa);
        $this->assertEquals('2024001001', $mahasiswa->nim);
        $this->assertEquals($pengguna->id, $mahasiswa->id_pengguna);
    }

    /** @test */
    public function it_can_calculate_ipk()
    {
        $mahasiswa = Mahasiswa::factory()->create();

        // Test with empty grades
        $this->assertEquals(0.0, $mahasiswa->hitungIPK());

        // This would require creating related Nilai records
        // For now, we test the default case
    }

    /** @test */
    public function it_can_get_nama_lengkap_from_data_pribadi()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'data_pribadi' => [
                'nama_lengkap' => 'John Doe Student'
            ]
        ]);

        $this->assertEquals('John Doe Student', $mahasiswa->getNamaLengkapAttribute());
    }

    /** @test */
    public function it_returns_empty_string_when_nama_lengkap_not_set()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'data_pribadi' => []
        ]);

        $this->assertEquals('', $mahasiswa->getNamaLengkapAttribute());
    }

    /** @test */
    public function it_can_get_semester_aktif_from_data_akademik()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'data_akademik' => [
                'semester_aktif' => 5
            ]
        ]);

        $this->assertEquals(5, $mahasiswa->getSemesterAktifAttribute());
    }

    /** @test */
    public function it_defaults_semester_aktif_to_1()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'data_akademik' => []
        ]);

        $this->assertEquals(1, $mahasiswa->getSemesterAktifAttribute());
    }

    /** @test */
    public function it_belongs_to_pengguna()
    {
        $pengguna = Pengguna::factory()->create(['peran' => 'mahasiswa']);
        $mahasiswa = Mahasiswa::factory()->create(['id_pengguna' => $pengguna->id]);

        $this->assertInstanceOf(Pengguna::class, $mahasiswa->pengguna);
        $this->assertEquals($pengguna->id, $mahasiswa->pengguna->id);
    }

    /** @test */
    public function it_belongs_to_program_studi()
    {
        $programStudi = ProgramStudi::factory()->create();
        $mahasiswa = Mahasiswa::factory()->create(['id_program_studi' => $programStudi->id]);

        $this->assertInstanceOf(ProgramStudi::class, $mahasiswa->programStudi);
        $this->assertEquals($programStudi->id, $mahasiswa->programStudi->id);
    }

    /** @test */
    public function it_can_check_if_mahasiswa_is_aktif()
    {
        $mahasiswaAktif = Mahasiswa::factory()->create(['status' => 'aktif']);
        $mahasiswaLulus = Mahasiswa::factory()->create(['status' => 'lulus']);

        $result1 = Mahasiswa::aktif()->where('id', $mahasiswaAktif->id)->first();
        $result2 = Mahasiswa::aktif()->where('id', $mahasiswaLulus->id)->first();

        $this->assertNotNull($result1);
        $this->assertNull($result2);
    }

    /** @test */
    public function data_pribadi_is_cast_to_array()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'data_pribadi' => ['nama_lengkap' => 'Test', 'jenis_kelamin' => 'laki-laki']
        ]);

        $this->assertIsArray($mahasiswa->data_pribadi);
        $this->assertEquals('Test', $mahasiswa->data_pribadi['nama_lengkap']);
    }

    /** @test */
    public function data_kontak_is_cast_to_array()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'data_kontak' => ['email' => 'test@example.com', 'telepon' => '081234567890']
        ]);

        $this->assertIsArray($mahasiswa->data_kontak);
        $this->assertEquals('test@example.com', $mahasiswa->data_kontak['email']);
    }

    /** @test */
    public function data_akademik_is_cast_to_array()
    {
        $mahasiswa = Mahasiswa::factory()->create([
            'data_akademik' => ['semester_aktif' => 3, 'ipk' => 3.5]
        ]);

        $this->assertIsArray($mahasiswa->data_akademik);
        $this->assertEquals(3, $mahasiswa->data_akademik['semester_aktif']);
    }
}