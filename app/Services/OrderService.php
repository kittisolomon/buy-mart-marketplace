<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder($user)
    {
        $cart = $user->cart->load('items.product');

        $cartItems = $cart->items()->with('product')->get();

        return DB::transaction(function () use ($user, $cartItems) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity)
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price
                ]);
            }

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
}
