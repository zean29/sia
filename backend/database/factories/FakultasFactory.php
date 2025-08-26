<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Fakultas;

class FakultasFactory extends Factory
{
    protected $model = Fakultas::class;

    public function definition(): array
    {
        $fakultasNames = [
            'Fakultas Teknologi Informasi',
            'Fakultas Ekonomi dan Bisnis',
            'Fakultas Teknik',
            'Fakultas Kedokteran',
            'Fakultas Psikologi',
            'Fakultas Hukum',
            'Fakultas Sains dan Teknologi',
            'Fakultas Komunikasi'
        ];

        $fakultasName = $this->faker->unique()->randomElement($fakultasNames);
        $kode = strtoupper(substr(str_replace(['Fakultas ', ' '], ['', ''], $fakultasName), 0, 3));

        return [
            'nama_fakultas' => $fakultasName,
            'kode_fakultas' => $kode,
            'dekan' => $this->faker->name() . ', Ph.D',
            'visi' => $this->faker->sentence(15),
            'misi' => $this->faker->paragraph(4),
            'alamat' => $this->faker->address(),
            'telepon' => '021' . $this->faker->numerify('########'),
            'email' => strtolower($kode) . '@university.ac.id',
            'website' => 'https://' . strtolower($kode) . '.university.ac.id',
            'tahun_berdiri' => $this->faker->year($max = '2020'),
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
}