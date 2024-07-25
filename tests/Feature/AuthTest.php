<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    const _TEST_MAIL_ = "mingyuTest@kimmingyu.co.kr";

    protected function tearDown(): void
    {
        $user = new User();
        $userRepository = new UserRepository($user);
        $testUser = $userRepository->findByEmail(self::_TEST_MAIL_);

        if ($testUser) {
            $userRepository->delete($testUser->id);
        }

        parent::tearDown();
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson("/api/v1/register", [
            'name' => 'mingyuTest',
            'email' => self::_TEST_MAIL_,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user','token']);

        $this->assertDatabaseHas('users',['email' => self::_TEST_MAIL_]);
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        $this->postJson("/api/v1/register", [
            'name' => 'mingyuTest1',
            'email' => self::_TEST_MAIL_,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response = $this->postJson("/api/v1/register", [
            'name' => 'mingyuTest2',
            'email' => self::_TEST_MAIL_,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_password_mismatch(): void
    {
        $response = $this->postJson("/api/v1/register", [
            'name' => 'mingyuTest',
            'email' => self::_TEST_MAIL_,
            'password' => 'password',
            'password_confirmation' => 'different_password'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

    }

    public function test_user_can_login(): void
    {

        $user = User::factory()->create([
            'email' => self::_TEST_MAIL_,
            'password' => Hash::make('password')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => self::_TEST_MAIL_,
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user','token']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => self::_TEST_MAIL_,
            'password' => Hash::make('password')
        ]);

        Sanctum::actingAs($user,['*']);

        $response = $this->postJson("/api/v1/logout");

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }
}
