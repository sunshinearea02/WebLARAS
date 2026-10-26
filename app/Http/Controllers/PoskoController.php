<?php

namespace App\Http\Controllers;

use App\Models\Posko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PoskoController extends Controller
{
    public function index()
    {
        return response()->json(
            Posko::where('aktif', true)
                ->orderBy('nama')
                ->get()
        );
    }

    public function store(Request $request)
    {
        Gate::authorize('kelola-data');
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'alamat' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'jenis' => 'required|in:evakuasi,utama,medis',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['aktif'] = true;

        $posko = Posko::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Posko berhasil ditambahkan.',
                'data' => $posko,
            ], 201);
        }

        return redirect()
            ->route('peta-evakuasi.index')
            ->with('success', 'Posko berhasil ditambahkan.');
    }

    public function destroy(Posko $posko)
    {
        Gate::authorize('kelola-data');
        $posko->update(['aktif' => false]);

        return response()->json([
            'message' => 'Posko berhasil dinonaktifkan.',
        ]);
    }
}
