<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Tampilkan Form Login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses Login (khusus Admin & Kader)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // Hanya akun petugas (admin/kader) yang aktif yang boleh masuk.
        if (! $user->isStaff()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => match (true) {
                    $user->isPending() => 'Akun Anda masih menunggu persetujuan Admin.',
                    ! $user->isActive() => 'Akun Anda sedang nonaktif. Hubungi Admin.',
                    default => 'Akun ini tidak memiliki akses petugas (Admin / Kader).',
                },
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Setelah login, petugas diarahkan ke homepage (kecuali tadinya sedang membuka halaman lain)
        return redirect()->intended('/')
            ->with('success', 'Berhasil masuk sebagai ' . ucfirst($user->role) . '.');
    }

    // Tampilkan Form Register + daftar pendaftar yang menunggu persetujuan (hanya Admin)
    public function showRegister()
    {
        $pending = User::where('status', User::STATUS_PENDING)->latest()->get();

        return view('auth.register', compact('pending'));
    }

    // Form pendaftaran mandiri untuk calon kader (publik)
    public function showSignup()
    {
        return view('auth.signup');
    }

    // Proses pendaftaran mandiri: selalu sebagai kader, status PENDING
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'role' => User::ROLE_KADER,
            'status' => User::STATUS_PENDING,
        ]);

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil. Akun Anda akan aktif setelah disetujui Admin.');
    }

    // Admin menyetujui pendaftar
    public function approve(User $user)
    {
        abort_unless($user->isPending(), 404);
        $user->update(['status' => User::STATUS_ACTIVE]);

        return back()->with('success', "Akun {$user->name} disetujui dan sekarang bisa login.");
    }

    // Admin menolak pendaftar (akun dihapus)
    public function reject(User $user)
    {
        abort_unless($user->isPending(), 404);
        $user->delete();

        return back()->with('success', "Pendaftaran {$user->name} ditolak.");
    }

    // Proses Register akun petugas baru (hanya Admin)
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(User::STAFF_ROLES)],
        ]);

        // Password otomatis di-hash oleh cast 'hashed' di model User.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'ACTIVE',
        ]);

        // Admin tetap login sebagai dirinya sendiri (tidak berpindah ke akun baru).
        return redirect()->route('register')
            ->with('success', "Akun {$user->role} \"{$user->name}\" berhasil dibuat.");
    }

    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Berhasil keluar.');
    }
}