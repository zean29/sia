<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Dosen;
use App\Models\Pengguna;
use App\Models\Fakultas;

class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        $nidn = $this->faker->unique()->numerify('##########');
        $nip = $this->faker->unique()->numerify('##################');

        return [
            'id_pengguna' => Pengguna::factory()->dosen(),
            'nomor_dosen' => 'DOS' . date('Y') . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'nidn' => $nidn,
            'nip' => $nip,
            'id_fakultas' => Fakultas::factory(),
            'data_pribadi' => [
                'nama_lengkap' => $this->faker->name(),
                'jenis_kelamin' => $this->faker->randomElement(['laki-laki', 'perempuan']),
                'tanggal_lahir' => $this->faker->dateTimeBetween('-65 years', '-25 years')->format('Y-m-d'),
                'tempat_lahir' => $this->faker->city(),
                'agama' => $this->faker->randomElement(['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu']),
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => $this->faker->randomElement(['belum_kawin', 'kawin', 'cerai']),
            ],
            'data_kontak' => [
                'alamat' => $this->faker->address(),
                'telepon' => '08' . $this->faker->numerify('#########'),
                'email' => $this->faker->unique()->safeEmail(),
            ],
            'kredensial_akademik' => [
                'gelar_depan' => $this->faker->randomElement(['Dr.', 'Prof. Dr.', '']),
                'gelar_belakang' => $this->faker->randomElement(['M.Kom', 'M.T', 'Ph.D', 'S.Kom', 'M.Si']),
                'pendidikan_terakhir' => $this->faker->randomElement(['S2', 'S3']),
                'universitas_terakhir' => $this->faker->company() . ' University',
                'tahun_lulus' => $this->faker->year($max = 'now'),
            ],
            'status_kepegawaian' => $this->faker->randomElement(['tetap', 'kontrak', 'honorer']),
            'jabatan_fungsional' => $this->faker->randomElement(['asisten_ahli', 'lektor', 'lektor_kepala', 'guru_besar', 'tidak_ada']),
            'tanggal_mulai_kerja' => $this->faker->dateTimeBetween('-20 years', 'now')->format('Y-m-d'),
            'tanggal_selesai_kerja' => null,
            'beban_mengajar_min' => 12,
            'beban_mengajar_max' => 16,
            'bidang_keahlian' => [
                $this->faker->randomElement(['Sistem Informasi', 'Teknik Informatika', 'Manajemen', 'Akuntansi']),
                $this->faker->randomElement(['Database', 'Pemrograman', 'Jaringan', 'Keamanan Siber'])
            ],
            'sertifikasi' => [
                'Sertifikat Dosen' => $this->faker->year($max = 'now'),
                'Sertifikat Profesional' => $this->faker->year($max = 'now'),
            ],
            'status' => 'aktif',
        ];
    }

    public function aktif()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'aktif',
            ];
        });
    }

    public function tetap()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_kepegawaian' => 'tetap',
            ];
        });
    }

    public function lektor()
    {
        return $this->state(function (array $attributes) {
            return [
                'jabatan_fungsional' => 'lektor',
            ];
        });
    }
}