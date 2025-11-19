<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class BastController extends Controller
{
    public function download(BorrowRequest $borrowRequest)
    {
        Gate::authorize('view', $borrowRequest);

        if (!in_array($borrowRequest->status, ['approved', 'returned'])) {
            abort(403, 'Berita acara hanya tersedia untuk peminjaman yang disetujui.');
        }

        $pdf = Pdf::loadView('pdf.bast', [
            'request' => $borrowRequest,
            'team' => $borrowRequest->team,
            'user' => $borrowRequest->user,
            'item' => $borrowRequest->item,
            'approver' => $borrowRequest->processor,
        ]);

        return $pdf->download('BAST-' . $borrowRequest->id . '.pdf');
    }
}