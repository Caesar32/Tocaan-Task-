<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexPaymentRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends BaseApiController
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * List all payments (paginated, user-scoped).
     */
    public function index(IndexPaymentRequest $request): AnonymousResourceCollection
    {
        $payments = $this->paymentService->list($request->validated());

        return PaymentResource::collection($payments)->additional([
            'success' => true,
            'message' => 'Payments retrieved successfully',
        ]);
    }

    /**
     * Process a payment for a confirmed order.
     */
    public function store(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->process($request->validated());

            return $this->successResponse(
                $payment->status->value === 'successful'
                    ? 'Payment processed successfully'
                    : 'Payment failed',
                new PaymentResource($payment),
                201
            );
        } catch (\Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Show a single payment (user-scoped).
     */
    public function show(int $id): JsonResponse
    {
        $payment = $this->paymentService->findById($id);

        return $this->successResponse(
            'Payment retrieved successfully',
            new PaymentResource($payment)
        );
    }

    /**
     * List all payments for a specific order (user-scoped).
     */
    public function getByOrder(int $orderId): JsonResponse
    {
        $payments = $this->paymentService->getByOrderId($orderId);

        return $this->successResponse(
            'Order payments retrieved successfully',
            PaymentResource::collection($payments)
        );
    }
}
