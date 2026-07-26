<?php

namespace App\Http\Controllers;

use App\Enums\FieldType;
use App\Models\FormField;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FormFieldController extends Controller
{
    public function index(): View
    {
        $fields = FormField::query()
            ->with('station')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return view('form-fields.index', compact('fields'));
    }

    public function create(): View
    {
        return view('form-fields.create', [
            'stations' => Station::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'types' => FieldType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        FormField::query()->create([
            'label' => $data['label'],
            'type' => $data['type'],
            'station_id' => $data['station_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'slug' => $this->uniqueSlug($data['label']),
            'is_system' => false,
            'options' => $this->parseOptions($request),
            'is_required' => $request->boolean('is_required'),
            'is_searchable' => $request->boolean('is_searchable'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('form-fields.index')->with('status', 'Field created.');
    }

    public function edit(FormField $formField): View
    {
        return view('form-fields.edit', [
            'field' => $formField,
            'stations' => Station::query()->orderBy('sort_order')->get(),
            'types' => FieldType::cases(),
        ]);
    }

    public function update(Request $request, FormField $formField): RedirectResponse
    {
        $data = $this->validated($request, $formField);

        $formField->update([
            'label' => $data['label'],
            'type' => $data['type'],
            'station_id' => $data['station_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'options' => $this->parseOptions($request),
            'is_required' => $request->boolean('is_required'),
            'is_searchable' => $request->boolean('is_searchable'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('form-fields.index')->with('status', 'Field updated.');
    }

    public function destroy(FormField $formField): RedirectResponse
    {
        if ($formField->is_system) {
            return back()->withErrors(['field' => 'System fields cannot be deleted. Deactivate them instead.']);
        }

        $formField->delete();

        return redirect()->route('form-fields.index')->with('status', 'Field deleted.');
    }

    private function validated(Request $request, ?FormField $field = null): array
    {
        if ($request->input('station_id') === '') {
            $request->merge(['station_id' => null]);
        }

        return $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(FieldType::class)],
            'station_id' => ['nullable', 'exists:stations,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options_text' => ['nullable', 'string'],
        ]);
    }

    private function parseOptions(Request $request): ?array
    {
        if ($request->input('type') !== FieldType::Select->value) {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) $request->input('options_text', '')) ?: [];

        $options = collect($lines)
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return $options ?: null;
    }

    private function uniqueSlug(string $label): string
    {
        $base = Str::slug($label, '_');
        $slug = $base;
        $i = 1;

        while (FormField::query()->where('slug', $slug)->exists()) {
            $slug = $base.'_'.$i;
            $i++;
        }

        return $slug;
    }
}
