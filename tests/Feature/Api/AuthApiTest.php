<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_email_devolve_token(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.com',
            'username' => 'ana',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'ana@example.com',
            'password' => 'password',
            'device_name' => 'pixel',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'ana@example.com')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonMissingPath('user.password');

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'pixel',
        ]);
    }

    public function test_login_com_username(): void
    {
        User::factory()->create(['username' => 'carlos']);

        $this->postJson('/api/v1/auth/login', [
            'login' => 'carlos',
            'password' => 'password',
            'device_name' => 'android',
        ])->assertOk()->assertJsonPath('user.username', 'carlos');
    }

    public function test_login_aceita_conta_rejeitada_como_no_site(): void
    {
        User::factory()->create([
            'email' => 'recusado@example.com',
            'status' => 'rejeitado',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'login' => 'recusado@example.com',
            'password' => 'password',
            'device_name' => 'android',
        ])->assertOk();
    }

    public function test_login_com_password_errada_devolve_422(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/v1/auth/login', [
            'login' => 'ana@example.com',
            'password' => 'errada',
            'device_name' => 'android',
        ])->assertUnprocessable()->assertJsonValidationErrors('login');
    }

    public function test_login_sem_campos_devolve_422(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login', 'password', 'device_name'])
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_registo_cria_conta_aprovada_e_devolve_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'nome' => 'Ana Silva',
            'email' => 'ana.silva@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'android',
        ]);

        $response->assertCreated();

        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'ana.silva@example.com',
            'username' => 'anasilva',
            'nome' => 'Ana Silva',
            'user_type' => 'cliente',
            'status' => 'aprovado',
        ]);

        $this->assertNotNull(User::where('email', 'ana.silva@example.com')->first()->email_verified_at);
    }

    public function test_registo_acrescenta_sufixo_quando_o_username_existe(): void
    {
        User::factory()->create(['username' => 'anasilva']);

        $this->postJson('/api/v1/auth/register', [
            'nome' => 'Ana',
            'email' => 'ana.silva@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'android',
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'ana.silva@example.com',
            'username' => 'anasilva1',
        ]);
    }

    public function test_registo_rejeita_email_duplicado_e_password_sem_confirmacao(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'nome' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'device_name' => 'android',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_logout_revoga_so_o_token_atual(): void
    {
        $user = User::factory()->create();
        $atual = $user->createToken('este')->plainTextToken;
        $outro = $user->createToken('outro')->plainTextToken;

        $this->withToken($atual)->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Sessão terminada.');

        // The Sanctum guard keeps the user it already resolved for this process.
        // A new HTTP request (new process) builds a new guard; forgetGuards()
        // does the same so the revoked token is actually checked again.
        Auth::forgetGuards();

        $this->withToken($atual)->getJson('/api/v1/me')->assertUnauthorized();

        Auth::forgetGuards();

        $this->withToken($outro)->getJson('/api/v1/me')->assertOk();
    }

    public function test_recuperar_password_envia_o_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'ana@example.com',
        ])->assertOk()->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'ana@example.com']);
    }

    public function test_recuperar_password_com_email_desconhecido_devolve_422(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'ninguem@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        Notification::assertNothingSent();
    }

    public function test_auth_tem_limite_mais_apertado(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'login' => 'ninguem@example.com',
                'password' => 'errada',
                'device_name' => 'android',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', [
            'login' => 'ninguem@example.com',
            'password' => 'errada',
            'device_name' => 'android',
        ])->assertStatus(429);
    }

    public function test_pedido_sem_token_devolve_json_401_sem_redirect(): void
    {
        $this->get('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }
}
