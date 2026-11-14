<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var Petugas $petugas */
            $petugas = Auth::user();

            return redirect()->to($this->dashboardPathForUser($petugas));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors(['email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."])
                ->onlyInput('email');
        }

        RateLimiter::hit($throttleKey, 60);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var Petugas $petugas */
            $petugas = Auth::user();

            return redirect()->to($this->dashboardPathForUser($petugas));
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }

    public function dashboardPathForUser(Petugas $petugas): string
    {
        return route($this->dashboardRouteNameForRole($petugas->role));
    }

    private function dashboardRouteNameForRole(string $role): string
    {
        return match ($role) {
            'admin_sales' => 'admin.dashboard',
            'teknisi' => 'teknisi.work-order',
            'pimpinan' => 'pimpinan.dashboard',
            default => abort(403),
        };
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower((string) $request->input('email')).'|'.$request->ip();
    }
}
