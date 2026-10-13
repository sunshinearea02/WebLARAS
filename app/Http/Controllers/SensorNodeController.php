<?php

namespace App\Http\Controllers;

use App\Models\SensorNode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin & Kader mengelola titik node sensor (terutama koordinat GPS).
 * Dijaga middleware role:admin,kader di routes/web.php.
 */
class SensorNodeController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        SensorNode::create($data + ['node_name' => $data['node_code'], 'status' => 'ACTIVE']);

        return redirect()->route('peta-evakuasi.index')->with('success', "Node {$data['node_code']} berhasil ditambahkan.");
    }

    public function update(Request $request, SensorNode $node)
    {
        $data = $this->validated($request, $node);
        $node->update($data + ['node_name' => $data['node_code']]);

        return redirect()->route('peta-evakuasi.index')->with('success', "Lokasi {$node->node_code} berhasil diperbarui.");
    }

    private function validated(Request $request, ?SensorNode $node = null): array
    {
        return $request->validate([
            'node_code' => ['required', 'string', 'max:50', Rule::unique('sensor_nodes', 'node_code')->ignore($node?->id)],
            'desa'      => ['nullable', 'string', 'max:100'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ], [
            'latitude.between'  => 'Latitude harus di antara -90 dan 90.',
            'longitude.between' => 'Longitude harus di antara -180 dan 180.',
            'node_code.unique'  => 'Kode node sudah dipakai.',
        ]);
    }
}
