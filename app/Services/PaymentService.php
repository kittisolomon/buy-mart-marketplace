<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\CartService;

class PaymentService
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function initializePayment(Order $order)
    {
        $flutterwaveKey = config('services.flutterwave.secret_key');

        $response = Http::withToken($flutterwaveKey)->post('https://api.flutterwave.com/v3/payments', [
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
            throw new \Exception('Flutterwave init failed: ' . $response->body());
        }

        return $response->json();
    }

    public function verifyAndLogPayment($transactionId)
    {
        $verifyUrl = "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify";

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
                'status' => $data['status'] === 'successful' ? 'completed' : 'failed',
                'amount' => $data['amount'],
                'currency_code' => $data['currency'],
                'reference_id' => $data['id'],
                'is_reconciled' => false,
            ]);

            $order->update(['status' => 'completed']);
            
            $cart = $this->cartService->getOrCreateCart($order->user_id);
            $this->cartService->clearCart($cart);

            return $payment;
        });
    }
}
