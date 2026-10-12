<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    // Menampilkan halaman form register
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Memproses pendaftaran akun baru
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['admin', 'kader'])], // Sesuai check constraint database
        ]);

        // Simpan ke database dengan password ter-hash otomatis oleh Hash::make
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'ACTIVE', // Default status aktif sesuai database
        ]);

        // Langsung login-kan setelah register berhasil
        Auth::login($user);

        return redirect()->route('lanskap.index')->with('success', 'Akun berhasil dibuat dan Anda telah masuk!');
    }
}