<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreRequest;
use App\Models\Store;
use App\Support\HttpConstants;
use App\Traits\HasJsonResponse;



class StoreController extends Controller
{
    //
    use HasJsonResponse;
    public function index(): JsonResponse
    {
        $user_id = auth()->id();

        $stores = Store::with(['category', 'owner'])
            ->where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->get();

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Stores retrieved successfully', $stores);
    }

    public function store(StoreRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $store = Store::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'store_category_id' => $validated['store_category_id'],
        ]);

        return $this->jsonResponse(HttpConstants::HTTP_CREATED, 'Store created successfully', $store);
    }

    public function show(Store $store): JsonResponse
    {
        $store->load(['storeCategory', 'owner']);

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Store retrieved successfully', $store);
    }

    public function update(StoreRequest $request, Store $store): JsonResponse
    {
        $validated = $request->validated();

        $store->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'store_category_id' => $validated['store_category_id'],
        ]);

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Store updated successfully', $store);
    }

    public function destroy(Store $store): JsonResponse
    {
        $store->delete();

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Store deleted successfully');
    }

}
