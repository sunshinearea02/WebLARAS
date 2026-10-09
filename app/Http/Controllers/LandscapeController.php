<?php

namespace App\Http\Controllers;

use App\Models\LandscapeAssessment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LandscapeController extends Controller
{
    public function index()
    {
        $options = LandscapeAssessment::options();
        $entries = LandscapeAssessment::orderByDesc('id')->limit(50)->get();

        return view('lanskap', compact('options', 'entries'));
    }

    public function store(Request $request)
    {
        $options = LandscapeAssessment::options();

        $validated = $request->validate([
            'location_name' => ['required', 'string', 'max:255'],
            'field_notes' => ['nullable', 'string', 'max:5000'],

            'dominant_vegetation' => ['nullable', Rule::in($options['dominant_vegetation'])],
            'slope_classification' => ['nullable', Rule::in($options['slope_classification'])],
            'drainage_condition' => ['nullable', Rule::in($options['drainage_condition'])],
            'soil_cracking' => ['nullable', Rule::in($options['soil_cracking'])],
            'soil_type' => ['nullable', Rule::in($options['soil_type'])],
            'landslide_history' => ['nullable', Rule::in($options['landslide_history'])],
            'artificial_slope_cutting' => ['nullable', 'in:ada,tidak_ada'],
            'geosynthetic_reinforcement' => ['nullable', Rule::in($options['geosynthetic_reinforcement'])],
        ]);

        $validated['artificial_slope_cutting'] = ($request->input('artificial_slope_cutting') === 'ada');

        LandscapeAssessment::create($validated);

        return redirect()->route('lanskap.index')->with('success', 'Data lanskap berhasil disimpan.');
    }

    public function destroy($id)
    {
        LandscapeAssessment::findOrFail($id)->delete();
        return redirect()->route('lanskap.index')->with('success', 'Data lanskap dihapus.');
    }
}
