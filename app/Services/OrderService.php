<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Support\HttpConstants;
use InvalidArgumentException;

class OrderService
{
    public function createOrder($user): Order|false
    {

        $cart = $user->cart()->with('items.product')->first();

        $cartItems = $cart->items;

        if (!$cart || $cart->items->isEmpty()) {
            return false;
        }

        return DB::transaction(function () use ($user, $cartItems): Order {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => HttpConstants::ORDER_PENDING,
                'total' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity)
            ]);

            $itemsData = $cartItems->map(function ($item) use ($order): array {
                return [
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            OrderItem::insert($itemsData);

            return $order;
        });
    }

    public function getOrders($userId, $perPage = 10)
    {
        return Order::with(['items.product', 'payment'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    protected function calculateOrderTotal(Collection $cartItems): float
    {
        return $cartItems->sum(function ($item) {
            throw_if(!$item->product, InvalidArgumentException::class, 'Product missing from cart item');
            throw_if(!is_numeric($item->product->price), InvalidArgumentException::class, 'Invalid product price');

            return round($item->product->price * $item->quantity, 2);
        });
    }
}
