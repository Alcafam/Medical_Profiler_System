<?php

namespace App\Http\Controllers;

use App\Services\VisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BulkVisitController extends Controller
{
    public function store(Request $request, VisitService $visits): RedirectResponse
    {
        abort_unless($request->user()->canBulkCreateVisits(), 403);

        $request->validate([
            'confirmation' => ['required', 'in:CREATE'],
        ]);

        $created = $visits->bulkCreateForAllClients($request->user());

        return redirect()
            ->route('clients.index')
            ->with('status', "Created {$created} new visit".($created === 1 ? '' : 's').' for all clients. Identity fields were copied; clinical fields are blank.');
    }
}
