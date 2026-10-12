<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{   /**
     * Menampilkan form register.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Memproses pendaftaran pengguna baru.
     */
    public function register(Request $request)
    {
        // 1. Validasi Input Form
        $validatedData = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Alamat email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi kata sandi tidak cocok.',
        ]); 
        // 2. Buat pengguna baru
        $user = \App\Models\User::create([
            'name'     => $validatedData['name'],
            'email'    => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);
        // 3. Login otomatis setelah pendaftaran
        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang.');
    }

    /**
     * Menampilkan form login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Memproses otentikasi login pengguna.
     */
    public function login(Request $request)
{
    // 1. Validasi input
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    $remember = $request->has('remember');

    // 2. Cek email & password
    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();

        // 3. LOGIKA REDIRECT SESUAI ROLE (PERBAIKAN UTAMA):
        // Jika akun memiliki role admin -> route('admin.dashboard')
        // Jika akun memiliki role user -> '/dashboard'
        $destination = Auth::user()->role === 'admin'
            ? route('admin.dashboard')
            : '/dashboard';

        return redirect()->intended($destination)->with('success', 'Selamat datang kembali!');
    }

    // 4. Jika login gagal
    return back()->withErrors([
        'email' => 'Email atau kata sandi yang Anda masukkan salah.',
    ])->onlyInput('email');
}

    /**
     * Memproses logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate dan regenerasi token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}