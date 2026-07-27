<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineInventoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageInventory(), 403);

        $archived = $request->boolean('archived');

        $medicines = Medicine::query()
            ->when($archived, fn ($q) => $q->archived(), fn ($q) => $q->active())
            ->orderBy('generic_name')
            ->orderBy('brand_name')
            ->orderBy('dosage_strength')
            ->paginate(30)
            ->withQueryString();

        return view('medicines.index', compact('medicines', 'archived'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->canManageInventory(), 403);

        return view('medicines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageInventory(), 403);

        $data = $this->validated($request);

        Medicine::query()->create($data);

        return redirect()->route('medicines.index')->with('status', 'Medicine added to inventory.');
    }

    public function edit(Request $request, Medicine $medicine): View
    {
        abort_unless($request->user()->canManageInventory(), 403);

        return view('medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        abort_unless($request->user()->canManageInventory(), 403);

        $medicine->update($this->validated($request));

        return redirect()->route('medicines.index', $medicine->isArchived() ? ['archived' => 1] : [])
            ->with('status', 'Medicine updated.');
    }

    public function destroy(Request $request, Medicine $medicine): RedirectResponse
    {
        abort_unless($request->user()->canManageInventory(), 403);

        $medicine->forceFill(['archived_at' => now()])->save();

        return redirect()->route('medicines.index')->with('status', 'Medicine archived.');
    }

    public function restore(Request $request, Medicine $medicine): RedirectResponse
    {
        abort_unless($request->user()->canManageInventory(), 403);

        $medicine->forceFill(['archived_at' => null])->save();

        return redirect()->route('medicines.index', ['archived' => 1])
            ->with('status', 'Medicine restored to active inventory.');
    }

    /**
     * @return array{
     *     generic_name: string,
     *     brand_name: string,
     *     dosage_strength: ?string,
     *     expiration_date: ?string,
     *     quantity: int,
     *     quantity_dispensed: int,
     *     remarks: ?string
     * }
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'generic_name' => ['required', 'string', 'max:255'],
            'brand_name' => ['required', 'string', 'max:255'],
            'dosage_strength' => ['nullable', 'string', 'max:255'],
            'expiration_month' => ['nullable', 'integer', 'between:1,12'],
            'expiration_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'quantity_dispensed' => ['required', 'integer', 'min:0', 'lte:quantity'],
            'remarks' => ['nullable', 'string'],
        ]);

        $expirationDate = null;
        if (! empty($data['expiration_month']) && ! empty($data['expiration_year'])) {
            $expirationDate = sprintf('%04d-%02d-01', $data['expiration_year'], $data['expiration_month']);
        }

        return [
            'generic_name' => trim($data['generic_name']),
            'brand_name' => trim($data['brand_name']),
            'dosage_strength' => isset($data['dosage_strength']) ? trim($data['dosage_strength']) ?: null : null,
            'expiration_date' => $expirationDate,
            'quantity' => (int) $data['quantity'],
            'quantity_dispensed' => (int) $data['quantity_dispensed'],
            'remarks' => isset($data['remarks']) ? trim($data['remarks']) ?: null : null,
        ];
    }
}
