<?php

namespace Tests\Feature;

use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StaffAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_is_available(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke ruang kerja')
            ->assertSee('Email petugas');
    }

    #[DataProvider('roleRedirects')]
    public function test_staff_members_are_redirected_to_their_role_area(string $role, string $routeName): void
    {
        $petugas = Petugas::factory()->create([
            'role' => $role,
        ]);

        $this->post(route('login.attempt'), [
            'email' => $petugas->email,
            'password' => 'password',
        ])
            ->assertRedirect(route($routeName));

        $this->assertAuthenticatedAs($petugas);
    }

    public static function roleRedirects(): array
    {
        return [
            'admin sales' => ['admin_sales', 'admin.dashboard'],
            'teknisi' => ['teknisi', 'teknisi.work-order'],
            'pimpinan' => ['pimpinan', 'pimpinan.dashboard'],
        ];
    }

    public function test_authenticated_staff_opening_login_is_redirected_to_their_role_area(): void
    {
        $petugas = Petugas::factory()->create([
            'role' => 'teknisi',
        ]);

        $this->actingAs($petugas)
            ->get(route('login'))
            ->assertRedirect(route('teknisi.work-order'));
    }

    public function test_invalid_password_returns_an_indonesian_error(): void
    {
        $petugas = Petugas::factory()->create([
            'email' => 'wrong-password@example.test',
        ]);

        $this->from(route('login'))
            ->post(route('login.attempt'), [
                'email' => $petugas->email,
                'password' => 'not-the-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
        $this->assertSame('Email atau password salah.', session('errors')->first('email'));
    }

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_member_with_the_wrong_role_is_forbidden(): void
    {
        $petugas = Petugas::factory()->create([
            'role' => 'teknisi',
        ]);

        $this->actingAs($petugas)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_logout_invalidates_the_session_and_returns_to_login(): void
    {
        $petugas = Petugas::factory()->create();

        $this->actingAs($petugas)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_attempts_are_limited_per_email_and_ip(): void
    {
        $petugas = Petugas::factory()->create([
            'email' => 'rate-limit@example.test',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))
                ->post(route('login.attempt'), [
                    'email' => $petugas->email,
                    'password' => 'not-the-password',
                ])
                ->assertRedirect(route('login'));
        }

        $this->from(route('login'))
            ->post(route('login.attempt'), [
                'email' => $petugas->email,
                'password' => 'not-the-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertStringContainsString(
            'Terlalu banyak percobaan login.',
            session('errors')->first('email'),
        );
    }
}
