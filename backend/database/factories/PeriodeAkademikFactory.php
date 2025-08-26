<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PeriodeAkademik;

class PeriodeAkademikFactory extends Factory
{
    protected $model = PeriodeAkademik::class;

    public function definition(): array
    {
        $tahunAkademik = $this->faker->numberBetween(2020, 2025) . '/' . ($this->faker->numberBetween(2020, 2025) + 1);
        $jenisPeriode = $this->faker->randomElement(['ganjil', 'genap']);
        
        return [
            'nama_periode' => 'Semester ' . ucfirst($jenisPeriode) . ' ' . $tahunAkademik,
            'jenis_periode' => $jenisPeriode,
            'tahun_akademik' => $tahunAkademik,
            'tanggal_mulai' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'tanggal_selesai' => $this->faker->dateTimeBetween('+4 months', '+6 months')->format('Y-m-d'),
            'tanggal_mulai_krs' => $this->faker->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'tanggal_selesai_krs' => $this->faker->dateTimeBetween('+2 weeks', '+1 month')->format('Y-m-d'),
            'batas_sks_maksimal' => 24,
            'batas_sks_minimal' => 12,
            'status' => $this->faker->randomElement(['akan_datang', 'berjalan', 'selesai']),
        ];
    }

    public function berjalan()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'berjalan',
                'tanggal_mulai' => now()->subMonth()->format('Y-m-d'),
                'tanggal_selesai' => now()->addMonths(3)->format('Y-m-d'),
            ];
        });
    }

    public function akanDatang()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'akan_datang',
                'tanggal_mulai' => now()->addMonth()->format('Y-m-d'),
                'tanggal_selesai' => now()->addMonths(5)->format('Y-m-d'),
            ];
        });
    }
}