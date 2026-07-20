<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends ApiController
{
    public function __construct(private BarcodeService $barcodeService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        $items = QueryBuilder::for(Product::class)
            ->allowedFilters([AllowedFilter::partial('name'), AllowedFilter::partial('sku')])
            ->allowedIncludes([AllowedInclude::relationship('category'), AllowedInclude::relationship('barcodes')])
            ->paginate(request('per_page', 15));
        return $this->success(ProductResource::collection($items));
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);
        return $this->success(new ProductResource($product->load(['category', 'barcodes'])));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $product = Product::create($request->validated());
        return $this->success(new ProductResource($product), null, 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        $product->update($request->validated());
        return $this->success(new ProductResource($product));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);
        $product->delete();
        return $this->success(null, null, 204);
    }

    public function byBarcode(string $code): JsonResponse
    {
        $product = $this->barcodeService->findByBarcode($code);
        if (! $product) {
            return $this->error(__('products.not_found'), 404, code: 'NOT_FOUND');
        }
        $this->authorize('view', $product);
        return $this->success(new ProductResource($product->load('barcodes')));
    }

    public function generateBarcode(Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        $barcode = $this->barcodeService->generate($product);
        return $this->success($barcode, null, 201);
    }
}