<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends ApiController
{
    public function __construct(private ReportService $reportService) {}

    public function __invoke(Request $request, string $type): Response
    {
        $request->validate(['format' => 'nullable|in:pdf,xlsx,csv']);
        return $this->reportService->generate($type, $request->get('format', 'csv'));
    }
}