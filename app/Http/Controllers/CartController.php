<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use App\Support\HttpConstants;
use App\Traits\HasJsonResponse;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;


class CartController extends Controller
{
    use HasJsonResponse;
    public function __construct(protected CartService $cartService) {}

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user()->id);

        $cart->load(['items.product', 'user']);

        return $this->wrapJsonResponse((new CartResource($cart))->response(), 'Cart items retrieved successfully');
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cart = $this->cartService->getOrCreateCart(Auth::user()->id);

        $result = $this->cartService->addToCart($cart, $product, $request->quantity);

        if (!$result['status']) {
            return $this->jsonResponse(HttpConstants::HTTP_BAD_REQUEST, $result['message']);
        }

        return $this->jsonResponse(HttpConstants::HTTP_CREATED, 'Item added to cart successfully', new CartItemResource($result['item']));
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $result = $this->cartService->updateItem($cartItem, $request->quantity);

        if (!$result['status']) {
            return $this->jsonResponse(HttpConstants::HTTP_BAD_REQUEST, $result['message']);
        }

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Cart item updated successfully', new CartItemResource($result['item']));
    }

    public function destroy(CartItem $cartItem): JsonResponse
    {
        $this->cartService->removeItem($cartItem);

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Item removed from cart successfully');
    }

    public function clear(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart(Auth::user()->id);

        $this->cartService->clearCart($cart);

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Cart cleared successfully');
    }
}
