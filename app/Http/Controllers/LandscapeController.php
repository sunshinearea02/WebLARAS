<?php

namespace App\Http\Controllers;

use App\Models\LandscapeAssessment;
use App\Models\SensorNode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LandscapeController extends Controller
{
    public function index(Request $request)
    {
        // Data lanskap bisa DILIHAT semua orang (termasuk warga/guest)
        $entries = LandscapeAssessment::with(['node', 'user'])->latest()->get();
        $options = LandscapeAssessment::options();
        $nodes = SensorNode::orderBy('node_code')->get();

        // Mode edit hanya untuk admin/kader
        $editing = null;
        if ($request->filled('edit') && $request->user()?->can('kelola-data')) {
            $editing = LandscapeAssessment::find($request->integer('edit'));
        }

        return view('lanskap', compact('entries', 'options', 'editing', 'nodes'));
    }

    public function store(Request $request)
    {
        LandscapeAssessment::create($this->validated($request) + [
            'user_id' => $request->user()->id, // pengisi data tercatat otomatis
        ]);

        return redirect()->route('lanskap.index')->with('success', 'Data lanskap berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $entry = LandscapeAssessment::findOrFail($id);
        $entry->update($this->validated($request) + [
            'user_id' => $request->user()->id, // petugas terakhir yang mengubah
        ]);

        return redirect()->route('lanskap.index')->with('success', 'Data lanskap berhasil diperbarui!');
    }

    public function destroy($id)
    {
        LandscapeAssessment::findOrFail($id)->delete();

        return redirect()->route('lanskap.index')->with('success', 'Data lanskap berhasil dihapus!');
    }

    /**
     * Validasi input form lanskap dan ubah radio "ada / tidak_ada" menjadi boolean.
     */
    private function validated(Request $request): array
    {
        $options = LandscapeAssessment::options();

        $rules = [
            'node_id' => ['required', 'integer', 'exists:sensor_nodes,id'],
            'location_name' => ['required', 'string', 'max:255'],
            'field_notes' => ['nullable', 'string', 'max:5000'],
            'artificial_slope_cutting' => ['nullable', Rule::in(['ada', 'tidak_ada'])],
        ];

        foreach ($options as $field => $choices) {
            $rules[$field] = ['nullable', Rule::in($choices)];
        }

        $data = $request->validate($rules);

        $data['artificial_slope_cutting'] = match ($data['artificial_slope_cutting'] ?? null) {
            'ada' => true,
            'tidak_ada' => false,
            default => null,
        };

        return $data;
    }
}
