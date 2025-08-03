<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\PaymentService;
use App\Traits\HasJsonResponse;
use App\Support\HttpConstants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    use HasJsonResponse;

    public function __construct(
        private PaymentService $paymentService
    ) {}
    
    public function initialize(Request $request, Order $order): JsonResponse
    {
        try {

            $data = $this->paymentService->initializePayment($order);
            
            return $this->jsonResponse(
                HttpConstants::HTTP_SUCCESS,
                'Payment initialized successfully',
                ['link' => $data['data']['link']]
            );
        } catch (\Exception $e) {
            return $this->jsonResponse(
                HttpConstants::HTTP_BAD_REQUEST,
                'Failed to initialize payment',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function callback(Request $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->verifyAndLogPayment($request->query('transaction_id'));
            
            return $this->jsonResponse(
                HttpConstants::HTTP_SUCCESS,
                'Payment verified successfully',
                new PaymentResource($payment)
            );
        } catch (\Exception $e) {
            return $this->jsonResponse(
                HttpConstants::HTTP_BAD_REQUEST,
                'Payment verification failed',
                ['error' => $e->getMessage()]
            );
        }
    }
}
