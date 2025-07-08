<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use App\Support\HttpConstants;
use App\Traits\HasJsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Log;
 use Illuminate\Support\Arr;

class ProductController extends Controller
{
    use HasJsonResponse;

    public function __construct(public FileUploadService $fileUploadService){}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $products = Product::with(['store', 'seller'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return $this->wrapJsonResponse(ProductResource::collection($products)->response(), 'Products retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $productImage = $validated['image'];
      
        $imageData = $this->fileUploadService->productImageUpload($productImage);

        $product = Product::create([
            'user_id' => Auth::id(),
            'store_id' => $validated['store_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'product_image_url' => $imageData['url'],
            'product_image_id' => $imageData['public_id'],
        ]);

        return $this->jsonResponse(HttpConstants::HTTP_CREATED, 'Product created successfully', $product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['store', 'seller']);

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Product retrieved successfully', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
       
        $validated = $request->validated();

        $newProductImage = $validated['image'] ?? null;

        $updateData = [
            'store_id' => $validated['store_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
        ];

        if ($newProductImage) {
            $productImage = $this->fileUploadService->productImageUpdate($newProductImage, $product->product_image_id);
            $updateData['product_image_url'] = $productImage['url'];
            $updateData['product_image_id'] = $productImage['public_id'];
        }

        $product->update($updateData);

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Product updated successfully', $product);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->fileUploadService->deleteProductImage($product->product_image_id);

        $product->delete();

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Product deleted successfully');
    }
}
