<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends BaseApiController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * List all orders (paginated, optionally filtered by status).
     */
    public function index(IndexOrderRequest $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->list($request->validated());

        return OrderResource::collection($orders)->additional([
            'success' => true,
            'message' => 'Orders retrieved successfully',
        ]);
    }

    /**
     * Create a new order with items.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->validated());

        return $this->successResponse(
            'Order created successfully',
            new OrderResource($order),
            201
        );
    }

    /**
     * Show a single order with items and payments count.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->findById($id);

        return $this->successResponse(
            'Order retrieved successfully',
            new OrderResource($order)
        );
    }

    /**
     * Update an order and optionally its items.
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->findById($id);
        $updatedOrder = $this->orderService->update($order, $request->validated());

        return $this->successResponse(
            'Order updated successfully',
            new OrderResource($updatedOrder)
        );
    }

    /**
     * Delete an order (only if no payments exist).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->findById($id);
            $this->orderService->delete($order);

            return $this->successResponse('Order deleted successfully');
        } catch (\Symfony\Component\HttpKernel\Exception\ConflictHttpException $e) {
            return $this->errorResponse($e->getMessage(), 409);
        }
    }
}
