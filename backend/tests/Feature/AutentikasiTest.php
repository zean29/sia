<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

class AutentikasiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $pengguna = Pengguna::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'peran' => 'mahasiswa'
        ]);

        $response = $this->postJson('/api/masuk', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'token',
                        'pengguna' => [
                            'id',
                            'nama_pengguna',
                            'email',
                            'peran'
                        ]
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('mahasiswa', $response->json('data.pengguna.peran'));
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $pengguna = Pengguna::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/masuk', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Email atau password tidak valid'
                ]);
    }

    /** @test */
    public function user_cannot_login_with_non_existent_email()
    {
        $response = $this->postJson('/api/masuk', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Email atau password tidak valid'
                ]);
    }

    /** @test */
    public function login_validates_required_fields()
    {
        $response = $this->postJson('/api/masuk', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function login_validates_email_format()
    {
        $response = $this->postJson('/api/masuk', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function authenticated_user_can_get_profile()
    {
        $pengguna = Pengguna::factory()->create([
            'nama_depan' => 'John',
            'nama_belakang' => 'Doe',
            'email' => 'john@example.com',
            'peran' => 'mahasiswa'
        ]);

        $token = $pengguna->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profil');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $pengguna->id,
                        'nama_pengguna' => $pengguna->nama_pengguna,
                        'email' => 'john@example.com',
                        'peran' => 'mahasiswa'
                    ]
                ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/profil');

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $pengguna = Pengguna::factory()->create();
        $token = $pengguna->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/keluar');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Berhasil logout'
                ]);

        // Token should be deleted
        $this->assertEquals(0, $pengguna->tokens()->count());
    }

    /** @test */
    public function user_can_register_mahasiswa()
    {
        $registrationData = [
            'nama_pengguna' => 'johndoe123',
            'nama_depan' => 'John',
            'nama_belakang' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'peran' => 'mahasiswa'
        ];

        $response = $this->postJson('/api/daftar', $registrationData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Pengguna berhasil didaftarkan'
                ]);

        $this->assertDatabaseHas('pengguna', [
            'nama_pengguna' => 'johndoe123',
            'email' => 'john@example.com',
            'peran' => 'mahasiswa'
        ]);
    }

    /** @test */
    public function registration_validates_required_fields()
    {
        $response = $this->postJson('/api/daftar', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'nama_pengguna',
                    'nama_depan',
                    'email',
                    'password'
                ]);
    }

    /** @test */
    public function registration_validates_unique_email()
    {
        $existingUser = Pengguna::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->postJson('/api/daftar', [
            'nama_pengguna' => 'newuser',
            'nama_depan' => 'New',
            'nama_belakang' => 'User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_validates_unique_nama_pengguna()
    {
        $existingUser = Pengguna::factory()->create([
            'nama_pengguna' => 'existinguser'
        ]);

        $response = $this->postJson('/api/daftar', [
            'nama_pengguna' => 'existinguser',
            'nama_depan' => 'New',
            'nama_belakang' => 'User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['nama_pengguna']);
    }

    /** @test */
    public function registration_validates_password_confirmation()
    {
        $response = $this->postJson('/api/daftar', [
            'nama_pengguna' => 'testuser',
            'nama_depan' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }
}