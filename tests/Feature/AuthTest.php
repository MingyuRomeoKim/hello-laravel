<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson("/api/v1/register", [
            'name' => 'Mingyu',
            'email' => 'mingyu@kimmingyu.co.kr',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user','token']);

        $this->assertDatabaseHas('user',['email' => 'mingyu@kimmingyu.co.kr']);
    }

    public function test_user_can_login(): void
    {

        $user = User::factory()->create([
            'email' => 'mingyu@kimmingyu.co.kr',
            'password' => Hash::make('password')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'mingyu@kimmingyu.co.kr',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user','token']);
    }
}
