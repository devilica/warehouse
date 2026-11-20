<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StockTransfer\StoreStockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use App\Support\DocumentNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StockTransferController extends ApiController
{
    public function __construct(private StockTransferService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', StockTransfer::class);
        $items = QueryBuilder::for(StockTransfer::class)
            ->allowedFilters([AllowedFilter::exact('status')])
            ->allowedSorts(['status', 'created_at', 'id'])
            ->paginate($this->perPage());

        return $this->success(StockTransferResource::collection($items));
    }

    public function show(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('view', $stockTransfer);
        return $this->success(new StockTransferResource($stockTransfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'items.fromLocation', 'items.toLocation'])));
    }

    public function store(StoreStockTransferRequest $request): JsonResponse
    {
        $this->authorize('create', StockTransfer::class);
        $transfer = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'] ?? [];
            unset($data['items']);
            $t = StockTransfer::create($data + [
                'status' => 'draft',
                'number' => DocumentNumber::make('TR-'),
            ]);
            foreach ($items as $item) {
                $quantity = $item['quantity_requested'] ?? $item['quantity'] ?? null;
                if ($quantity === null) {
                    throw ValidationException::withMessages(['items' => ['Each transfer item needs a quantity.']]);
                }
                $t->items()->create([
                    'product_id' => $item['product_id'],
                    'from_location_id' => $item['from_location_id'] ?? $item['from_warehouse_location_id'] ?? null,
                    'to_location_id' => $item['to_location_id'] ?? $item['to_warehouse_location_id'] ?? null,
                    'quantity_requested' => $quantity,
                ]);
            }
            return $t->load('items.product');
        });
        return $this->success(new StockTransferResource($transfer), null, 201);
    }

    public function approve(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->approve($stockTransfer)));
    }

    public function ship(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->ship($stockTransfer)));
    }

    public function receive(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->receive($stockTransfer)));
    }

    public function complete(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->complete($stockTransfer)));
    }
}