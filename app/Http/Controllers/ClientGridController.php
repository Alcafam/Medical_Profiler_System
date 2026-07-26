<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FormField;
use App\Services\ClientFieldValueService;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientGridController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canUseGrid(), 403);

        $fields = FormField::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $clients = Client::query()
            ->with(['latestVisit.fieldValues.editor', 'latestVisit.fieldValues.formField'])
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('clients.grid', compact('clients', 'fields'));
    }

    public function saveCell(
        Request $request,
        Client $client,
        FormField $field,
        ClientFieldValueService $service,
        VisitService $visits,
    ): JsonResponse {
        abort_unless($request->user()->canUseGrid(), 403);

        $visit = $visits->ensureLatestVisit($client, $request->user());

        $data = $request->validate([
            'value' => ['nullable', 'string'],
            'version' => ['nullable', 'integer', 'min:0'],
            'force' => ['nullable', 'boolean'],
        ]);

        $result = $service->save(
            visit: $visit,
            field: $field,
            user: $request->user(),
            value: $data['value'] ?? null,
            expectedVersion: $data['version'] ?? null,
            force: (bool) ($data['force'] ?? false),
        );

        if ($result['status'] === 'conflict') {
            return response()->json([
                'status' => 'conflict',
                'conflict' => $result['conflict'],
            ], 409);
        }

        $value = $result['value'];

        return response()->json([
            'status' => 'saved',
            'value' => $value->value,
            'version' => $value->version,
            'updated_by' => $value->editor?->name,
            'updated_at' => optional($value->updated_at)?->diffForHumans(),
        ]);
    }
}
