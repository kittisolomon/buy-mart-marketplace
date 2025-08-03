<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\HasJsonResponse;
use App\Support\HttpConstants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use HasJsonResponse;

    public function __construct(
        private OrderService $orderService
    ) {}
    
    public function storeOrder(Request $request): JsonResponse
    {
        try {
          
            $order = $this->orderService->createOrder($request->user());
            
            return $this->jsonResponse(HttpConstants::HTTP_CREATED, 'Order created successfully', new OrderResource($order->load(['items.product'])));

        } catch (\Exception $e) {

            return $this->jsonResponse(HttpConstants::HTTP_BAD_REQUEST,'Failed to create order', ['error' => $e->getMessage()]);
        
        }
    }

    public function showOrders(Request $request): JsonResponse
    {
        try {
        
            $orders = $this->orderService->getOrders(Auth::id(), 10);
            
            return $this->wrapJsonResponse(OrderResource::collection($orders)->response(),'Orders retrieved successfully');

        } catch (\Exception $e) {
            
            return $this->jsonResponse(HttpConstants::HTTP_BAD_REQUEST, 'Failed to retrieve orders', ['error' => $e->getMessage()]);
        }
    }
}
