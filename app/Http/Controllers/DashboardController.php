<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Models\MedicalCase;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // One grouped query instead of five counts. No user input is involved.
        $byStatus = MedicalCase::query()
            ->toBase()
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'Total cases' => (int) $byStatus->sum(),
            'Planning' => (int) ($byStatus[CaseStatus::Planning->value] ?? 0),
            'In Review' => (int) ($byStatus[CaseStatus::InReview->value] ?? 0),
            'Approved' => (int) ($byStatus[CaseStatus::Approved->value] ?? 0),
            'Completed' => (int) ($byStatus[CaseStatus::Completed->value] ?? 0),
        ];

        return view('dashboard', ['stats' => $stats]);
    }
}
