<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProgramStudi;
use App\Models\Fakultas;

class ProgramStudiFactory extends Factory
{
    protected $model = ProgramStudi::class;

    public function definition(): array
    {
        $programNames = [
            'Sistem Informasi',
            'Teknik Informatika', 
            'Manajemen',
            'Akuntansi',
            'Teknik Sipil',
            'Teknik Mesin',
            'Psikologi',
            'Kedokteran'
        ];

        $programName = $this->faker->randomElement($programNames);
        $kode = strtoupper(substr(str_replace(' ', '', $programName), 0, 3)) . 
                str_pad($this->faker->numberBetween(1, 99), 3, '0', STR_PAD_LEFT);

        return [
            'id_fakultas' => Fakultas::factory(),
            'nama_program_studi' => $programName,
            'kode_program_studi' => $kode,
            'jenjang' => $this->faker->randomElement(['D3', 'S1', 'S2', 'S3']),
            'akreditasi' => $this->faker->randomElement(['A', 'B', 'C', 'Baik Sekali', 'Baik']),
            'status' => 'aktif',
            'tahun_berdiri' => $this->faker->year($max = 'now'),
            'visi' => $this->faker->sentence(10),
            'misi' => $this->faker->paragraph(3),
            'profil_lulusan' => $this->faker->paragraph(2),
            'kompetensi_utama' => [
                'Kompetensi 1: ' . $this->faker->sentence(),
                'Kompetensi 2: ' . $this->faker->sentence(),
                'Kompetensi 3: ' . $this->faker->sentence(),
            ],
            'kompetensi_pendukung' => [
                'Pendukung 1: ' . $this->faker->sentence(),
                'Pendukung 2: ' . $this->faker->sentence(),
            ],
            'prospek_karir' => [
                'Karir 1: ' . $this->faker->jobTitle(),
                'Karir 2: ' . $this->faker->jobTitle(),
                'Karir 3: ' . $this->faker->jobTitle(),
            ],
        ];
    }

    public function s1()
    {
        return $this->state(function (array $attributes) {
            return [
                'jenjang' => 'S1',
            ];
        });
    }

    public function s2()
    {
        return $this->state(function (array $attributes) {
            return [
                'jenjang' => 'S2',
            ];
        });
    }

    public function aktif()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'aktif',
            ];
        });
    }
}