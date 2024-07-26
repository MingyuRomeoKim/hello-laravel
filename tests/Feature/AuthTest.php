<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use function Laravel\Prompts\select;

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

    public function test_user_cannot_access_userData_without_login(): void
    {
        $response = $this->getJson("/api/v1/user");

        $response->assertStatus(401); // Unauthorized
    }

    public function test_user_can_access_userData_with_login(): void
    {
        $user = User::factory()->create([
            'email' => self::_TEST_MAIL_,
            'password' => Hash::make('password')
        ]);

        $loginResponse = $this->postJson("/api/v1/login", [
            'email' => self::_TEST_MAIL_,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse['token'];

        $response = $this->withHeaders([
           'Authorization' => 'Bearer '. $token,
        ])->getJson("/api/v1/user");

        $response->assertStatus(200);
    }

    public function test_user_can_access_with_valid_token(): void
    {

        $user = User::factory()->create([
            'email' => self::_TEST_MAIL_,
            'password' => Hash::make('password'),
        ]);

        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;
        $token->expires_at = Carbon::now()->addHours(1); // 1시간 후 만료
        $token->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenResult->plainTextToken,
        ])->getJson("/api/v1/testTokenExpires");

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_with_expired_token(): void
    {
        $user = User::factory()->create([
            'email' => self::_TEST_MAIL_,
            'password' => Hash::make('password'),
        ]);

        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;
        $token->expires_at = Carbon::now()->subHour(); // 1시간 전 만료
        $token->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenResult->plainTextToken,
        ])->getJson("/api/v1/testTokenExpires");

        $response->assertStatus(401)
            ->assertJson(['message' => 'Token has expired.']);
    }
}
