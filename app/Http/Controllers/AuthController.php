<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Penduduk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();
        $this->logActivity($request, 'Masuk ke aplikasi');

        return redirect()->intended(route($this->dashboardRoute($request->user()->role)));
    }

    public function register()
    {
        return view('auth.register');
    }

    public function storeRegistration(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'nik' => ['required', 'digits:16', 'unique:penduduks,nik'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'penduduk',
            ]);

            Penduduk::create([
                'user_id' => $user->id,
                'nik' => $data['nik'],
                'nama' => $data['name'],
                'alamat' => $data['alamat'],
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $this->logActivity($request, 'Membuat akun penduduk');

        return redirect()->route($this->dashboardRoute($user->role))->with('success', 'Pendaftaran berhasil. Selamat datang!');
    }

    public function logout(Request $request)
    {
        $this->logActivity($request, 'Keluar dari aplikasi');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function logActivity(Request $request, string $activity): void
    {
        LogAktivitas::create([
            'user_id' => Auth::id(),
            'aktivitas' => $activity,
            'ip_address' => $request->ip(),
        ]);
    }

    private function dashboardRoute(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'kepala_desa' => 'kepala-desa.dashboard',
            default => 'penduduk.dashboard',
        };
    }
}
