<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\CartService;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use App\Support\HttpConstants;

class PaymentService
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    public function initializePayment(Order $order): array
    {
        if (!$order->user || !$order->total) {
            throw new InvalidArgumentException('Invalid order data');
        }

        $flutterwaveKey = config('services.flutterwave.secret_key');

        if (empty($flutterwaveKey)) {

            Log::error('Flutterwave secret key is missing in configuration.');

            throw new InvalidArgumentException('Flutterwave initialization failed.');
        }

        $baseUrl = config('services.flutterwave.base_url');

        $response = Http::withToken($flutterwaveKey)->post($baseUrl . '/payments', [
            'tx_ref' => $order->id,
            'amount' => $order->total,
            'currency' => 'NGN',
            'redirect_url' => route('payment.callback'),
            'customer' => [
                'email' => $order->user->email,
                'name' => $order->user->name,
            ],
        ]);

        if ($response->failed()) {

            Log::error('Flutterwave initialization failed: ' . $response->body());

            throw new \Exception('Flutterwave initialization failed: ' . $response->body());
        }

        return $response->json();
    }

    public function verifyAndLogPayment($transactionId): Payment
    {
        $baseUrl = config('services.flutterwave.base_url');

        $verifyUrl = $baseUrl . "/transactions/{$transactionId}/verify";

        $response = Http::withToken(config('services.flutterwave.secret_key'))->get($verifyUrl);

        if ($response->failed() || $response->json('status') !== 'success') {
            throw new \Exception('Payment verification failed');
        }

        $data = $response->json('data');

        $order = Order::with('user')->findOrFail($data['tx_ref']);

        return DB::transaction(function () use ($order, $data) {
            $payment = Payment::updateOrCreate([
                'order_id' => $order->id
            ], [
                'buyer_id' => $order->user_id,
                'payment_method' => 'flutterwave',
                'status' => $data['status'],
                'transaction_id' => $data['id'],
                'total_amount' => $data['amount'],
                'transfer_fee' => $data['app_fee'] ?? 0,
                'trans_total' => $data['charged_amount'] ?? $data['amount'],
                'settlement_amount' => $data['amount_settled'] ?? $data['amount'],
                'currency_code' => $data['currency'],
            ]);

            Transaction::create([
                'payment_id' => $payment->id,
                'type' => 'payment',
                'status' => $data['status'] === HttpConstants::PAYMENT_SUCCESSFUL ? HttpConstants::ORDER_COMPLETED : HttpConstants::ORDER_FAILED,
                'amount' => $data['amount'],
                'currency_code' => $data['currency'],
                'reference_id' => $data['id'],
                'is_reconciled' => false,
            ]);

            $order->update(['status' => HttpConstants::ORDER_COMPLETED]);

            $cart = $this->cartService->getOrCreateCart($order->user_id);
            $this->cartService->clearCart($cart);

            return $payment;
        });
    }
}
