<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLevel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    private array $handlers = [];

    public function __construct()
    {
        $this->handlers = [
            'inventory-valuation' => fn () => StockLevel::with(['product', 'warehouse'])->get(),
            'stock-movements' => fn () => InventoryTransaction::with(['product', 'warehouse'])->latest()->limit(1000)->get(),
            'purchase-history' => fn () => PurchaseOrder::with(['supplier', 'items'])->latest()->get(),
            'low-stock' => fn () => Product::whereNotNull('reorder_level')->with('stockLevels')->get(),
            'product-history' => fn () => InventoryTransaction::with('product')->latest()->limit(500)->get(),
        ];
    }

    public function generate(string $type, string $format = 'csv'): Response|StreamedResponse
    {
        $data = ($this->handlers[$type] ?? fn () => collect())();

        return match ($format) {
            'pdf' => Pdf::loadView('reports.generic', compact('type', 'data'))->download("{$type}.pdf"),
            'xlsx' => Excel::download(new \App\Exports\GenericReportExport($data), "{$type}.xlsx"),
            default => response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');
                $rows = $data instanceof \Illuminate\Support\Collection ? $data->toArray() : (array) $data;
                if (! empty($rows)) {
                    fputcsv($out, array_keys($rows[0] instanceof \Illuminate\Database\Eloquent\Model ? $rows[0]->toArray() : (array) $rows[0]));
                    foreach ($rows as $row) {
                        fputcsv($out, $row instanceof \Illuminate\Database\Eloquent\Model ? array_values($row->toArray()) : array_values((array) $row));
                    }
                }
                fclose($out);
            }, "{$type}.csv"),
        };
    }
}