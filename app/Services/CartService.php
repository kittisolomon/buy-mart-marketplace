<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getOrCreateCart($userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    // {
    //     return DB::transaction(function () use ($cart, $product, $quantity) {
    //         $existingItem = $cart->items()->where('product_id', $product->id)->first();

    //         if ($existingItem) {
    //             $diff = $quantity - $existingItem->quantity;
    //             if ($diff > 0) {
    //                 $stockCheck = $this->checkStock($product, $existingItem, $diff);
    //                 if (!$stockCheck['status']) {
    //                     return $stockCheck;
    //                 }
    //                 $product->decrement('quantity', $diff);
    //             } elseif ($diff < 0) {
    //                 $product->increment('quantity', abs($diff));
    //             }
    //             $existingItem->update(['quantity' => $quantity]);
    //             $cartItem = $existingItem->refresh();
    //         } else {
    //             $stockCheck = $this->checkStock($product, $existingItem, $quantity);
    //             if (!$stockCheck['status']) {
    //                 return $stockCheck;
    //             }
    //             $cartItem = $cart->items()->create([
    //                 'product_id' => $product->id,
    //                 'quantity' => $quantity,
    //             ]);
    //             $product->decrement('quantity', $quantity);
    //         }

    //         return ['status' => true, 'item' => $cartItem->load('product')];
    //     });
    // }

    public function addToCart(Cart $cart, Product $product, int $newQuantity = 1)
    {
        return DB::transaction(function () use ($cart, $product, $newQuantity) {
        
            $existingItem = $cart->items()->firstWhere('product_id', $product->id);

            if ($existingItem) {

                $stockCheck = $this->checkStock($product, $existingItem, $newQuantity);

                if (!$stockCheck['status']) {
                    return $stockCheck;
                }

                $product->decrement('quantity', $newQuantity);

                $existingItem->increment('quantity', $newQuantity);

                $cartItem = $existingItem->refresh();
            } else {
                $stockCheck = $this->checkStock($product, null, $newQuantity);

                if (!$stockCheck['status']) {
                    return $stockCheck;
                }

                $cartItem = $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $newQuantity,
                ]);

                $product->decrement('quantity', $newQuantity);
            }

            return ['status' => true, 'item' => $cartItem->load('product')];
        });
    }


    public function updateItem(CartItem $item, int $quantityToAdd)
    {
        return DB::transaction(function () use ($item, $quantityToAdd) {
            $product = $item->product;

            $newTotalQuantity = $item->quantity + $quantityToAdd;

            if ($newTotalQuantity > $product->quantity) {
                return [
                    'status' => false,
                    'message' => 'Not enough stock available for this product.'
                ];
            }

            $product->decrement('quantity', $quantityToAdd);

            $item->increment('quantity', $quantityToAdd);

            return [
                'status' => true,
                'item' => $item->refresh()->load('product')
            ];
        });
    }


    public function removeItem(CartItem $item, int $quantity = 1): bool
    {
        return DB::transaction(function () use ($item, $quantity) {

            $product = $item->product;

            $product->increment('quantity', $quantity);

            if ($item->quantity > $quantity) {
                $item->decrement('quantity', $quantity);
                return true;
            }

            return $item->delete();
        });
    }

    public function clearCart(Cart $cart): void
    {

        DB::transaction(function () use ($cart) {
            foreach ($cart->items as $item) {
                $item->product->increment('quantity', $item->quantity);
            }

            $cart->items()->delete();
            $cart->delete();
        });
    }

    private function checkStock(Product $product, ?CartItem $existingItem, int $quantity): array
    {
        $totalDesiredQuantity = $existingItem ? $existingItem->quantity + $quantity : $quantity;

        if ($totalDesiredQuantity > $product->quantity) {
            return [
                'status' => false,
                'message' => 'Not enough stock available for this product.'
            ];
        }
        return ['status' => true];
    }
}
