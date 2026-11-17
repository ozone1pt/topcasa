<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FluxosWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_registo_web_continua_a_criar_sessao(): void
    {
        $this->post('/register', [
            'nome' => 'Ana Silva',
            'email' => 'ana.silva@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ana.silva@example.com',
            'username' => 'anasilva',
            'status' => 'aprovado',
            'user_type' => 'cliente',
        ]);
    }

    public function test_login_web_aceita_username_ou_email(): void
    {
        $user = User::factory()->create([
            'username' => 'carlos',
            'email' => 'carlos@example.com',
        ]);

        $this->post('/login', [
            'login' => 'carlos',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);

        $this->post('/logout');

        $this->post('/login', [
            'login' => 'carlos@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_perfil_web_continua_a_gravar_e_a_limpar_verificacao(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.com', 'nome' => 'Ana']);

        $this->actingAs($user)->patch('/profile', [
            'nome' => 'Ana Maria',
            'email' => 'nova@example.com',
        ])->assertRedirect(route('profile.edit'));

        $fresh = $user->fresh();
        $this->assertSame('Ana Maria', $fresh->nome);
        $this->assertSame('nova@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);
    }

    public function test_listagem_web_de_anuncios_responde(): void
    {
        $this->get('/anuncios')->assertOk();
    }
}
