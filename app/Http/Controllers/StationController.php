<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationController extends Controller
{
    public function index(): View
    {
        $stations = Station::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('stations.index', compact('stations'));
    }

    public function create(): View
    {
        return view('stations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Station::query()->create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('stations.index')->with('status', 'Station created.');
    }

    public function edit(Station $station): View
    {
        return view('stations.edit', compact('station'));
    }

    public function update(Request $request, Station $station): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $station->update([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('stations.index')->with('status', 'Station updated.');
    }

    public function destroy(Station $station): RedirectResponse
    {
        $station->formFields()->update(['station_id' => null]);
        $station->delete();

        return redirect()->route('stations.index')->with('status', 'Station deleted.');
    }
}
