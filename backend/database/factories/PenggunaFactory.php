<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Pengguna;

class PenggunaFactory extends Factory
{
    protected $model = Pengguna::class;

    public function definition(): array
    {
        return [
            'nama_pengguna' => $this->faker->unique()->userName(),
            'nama_depan' => $this->faker->firstName(),
            'nama_belakang' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'peran' => $this->faker->randomElement(['mahasiswa', 'dosen', 'staf', 'admin']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'status' => 'aktif',
        ];
    }

    public function mahasiswa()
    {
        return $this->state(function (array $attributes) {
            return [
                'peran' => 'mahasiswa',
            ];
        });
    }

    public function dosen()
    {
        return $this->state(function (array $attributes) {
            return [
                'peran' => 'dosen',
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'peran' => 'admin',
            ];
        });
    }

    public function staf()
    {
        return $this->state(function (array $attributes) {
            return [
                'peran' => 'staf',
            ];
        });
    }
}