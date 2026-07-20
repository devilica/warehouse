<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\GoodsReceipt\StoreGoodsReceiptRequest;
use App\Http\Resources\GoodsReceiptResource;
use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;

class GoodsReceiptController extends ApiController
{
    public function __construct(private GoodsReceiptService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', GoodsReceipt::class);
        return $this->success(GoodsReceiptResource::collection(QueryBuilder::for(GoodsReceipt::class)->paginate()));
    }

    public function show(GoodsReceipt $goodsReceipt): JsonResponse
    {
        $this->authorize('view', $goodsReceipt);
        return $this->success(new GoodsReceiptResource($goodsReceipt->load('items.product')));
    }

    public function store(StoreGoodsReceiptRequest $request): JsonResponse
    {
        $this->authorize('create', GoodsReceipt::class);
        $receipt = GoodsReceipt::create($request->validated() + ['status' => 'draft']);
        return $this->success(new GoodsReceiptResource($receipt), null, 201);
    }

    public function confirm(GoodsReceipt $goodsReceipt): JsonResponse
    {
        $this->authorize('update', $goodsReceipt);
        return $this->success(new GoodsReceiptResource($this->service->confirm($goodsReceipt)));
    }
}