<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Landscape;

class LandscapeController extends Controller
{
    public function index()
    {
        // Ambil data lanskap dari database
        $entries = Landscape::latest()->get();

        // WAJIB MENGGUNAKAN view('lanskap') AGAR BLADE ENGINE BERJALAN
        return view('lanskap', compact('entries'));
    }

    public function store(Request $request)
    {
        // Validasi dan simpan data (khusus admin/kader)
        $request->validate([
            'location_name' => 'required|string|max:255',
        ]);

        Landscape::create($request->all());

        return redirect()->route('lanskap.index')->with('success', 'Data lanskap berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        // Hapus data (khusus admin/kader)
        $entry = Landscape::findOrFail($id);
        $entry->delete();

        return redirect()->route('lanskap.index')->with('success', 'Data lanskap berhasil dihapus!');
    }
}