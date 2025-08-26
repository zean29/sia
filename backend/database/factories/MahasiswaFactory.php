<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\ProgramStudi;

class MahasiswaFactory extends Factory
{
    protected $model = Mahasiswa::class;

    public function definition(): array
    {
        $year = now()->year;
        $randomNumber = str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);
        $nim = $year . '001' . $randomNumber;

        return [
            'id_pengguna' => Pengguna::factory()->mahasiswa(),
            'id_program_studi' => ProgramStudi::factory(),
            'nim' => $nim,
            'status' => $this->faker->randomElement(['aktif', 'tidak_aktif', 'lulus', 'cuti', 'do']),
            'tanggal_masuk' => $this->faker->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
            'semester_aktif' => $this->faker->numberBetween(1, 8),
            'ipk' => $this->faker->randomFloat(2, 2.0, 4.0),
            'data_pribadi' => [
                'nama_lengkap' => $this->faker->name(),
                'jenis_kelamin' => $this->faker->randomElement(['laki-laki', 'perempuan']),
                'tanggal_lahir' => $this->faker->dateTimeBetween('-25 years', '-17 years')->format('Y-m-d'),
                'tempat_lahir' => $this->faker->city(),
                'agama' => $this->faker->randomElement(['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu']),
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => $this->faker->randomElement(['belum_kawin', 'kawin', 'cerai']),
            ],
            'data_kontak' => [
                'alamat' => $this->faker->address(),
                'telepon' => '08' . $this->faker->numerify('#########'),
                'email' => $this->faker->unique()->safeEmail(),
                'nama_kontak_darurat' => $this->faker->name(),
                'hubungan_kontak_darurat' => $this->faker->randomElement(['orang_tua', 'saudara', 'kerabat']),
                'telepon_kontak_darurat' => '08' . $this->faker->numerify('#########'),
            ],
            'data_akademik' => [
                'semester_aktif' => $this->faker->numberBetween(1, 8),
                'ipk' => $this->faker->randomFloat(2, 2.0, 4.0),
                'total_sks' => $this->faker->numberBetween(0, 144),
                'status_akademik' => $this->faker->randomElement(['aktif', 'probation', 'normal']),
            ],
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

    public function lulus()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'lulus',
                'semester_aktif' => 8,
                'ipk' => $this->faker->randomFloat(2, 2.75, 4.0),
            ];
        });
    }
}