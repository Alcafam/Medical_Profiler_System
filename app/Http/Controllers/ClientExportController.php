<?php

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClientExportController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->canExport(), 403);

        $filename = 'clients-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new ClientsExport, $filename);
    }
}
