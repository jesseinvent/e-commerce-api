<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Notifications\NewOrderMade;
use App\Notifications\OrderAccepted;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        if ($product->wasCreatedBy(auth()->user())) {
            $orders = $product->orders()->paginate(20);
            return response()->json(OrderResource::collection($orders), Response::HTTP_OK);
        }

        return response()->json(['errors' => 'User not allowed to perform this request'], Response::HTTP_NON_AUTHORITATIVE_INFORMATION);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request, Product $product)
    {
        $order = $product->orders()->create(array_merge($request->all(), [
            'user_id' => auth()->user()->id,
            'paid_status' => false
        ]));

        $product->user->notify(new NewOrderMade($order));

        return response()->json(new OrderResource($order), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        if ($order->wasCreatedBy(auth()->user()) || $order->belongsToProductCreatedBy(auth()->user()) ) {
            return response()->json(new OrderResource($order), Response::HTTP_OK);
        }

        return response()->json(['errors' => 'User not allowed to perform this request'], Response::HTTP_NON_AUTHORITATIVE_INFORMATION);
    }

    public function acceptOrder(Order $order)
    {
        if ($order->belongsToProductCreatedBy(auth()->user())) {
            
            $order->update(['status' => 'accepted']);
            $order->product->user->notify(new OrderAccepted($order));
            return response()->json(new OrderResource($order), Response::HTTP_OK);

        }

        return response()->json(['errors' => 'Orders can only be accepted by who owns the product'], Response::HTTP_NON_AUTHORITATIVE_INFORMATION);

    }

    public function rejectOrder(Order $order)
    {
        if ($order->belongsToProductCreatedBy(auth()->user())) {
            $order->update(['status' => 'rejected']);
            return response()->json(new OrderResource($order), Response::HTTP_OK);
        }

        return response()->json(['errors' => 'Orders can only be rejected by who owns the product'], Response::HTTP_NON_AUTHORITATIVE_INFORMATION);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        if ($order->wasCreatedBy(auth()->user()) || $order->belongsToProductCreatedBy(auth()->user()) ) {
            $order->delete();
            return response([], Response::HTTP_NO_CONTENT);
        }

        return response()->json(['errors' => 'User not allowed to perform this request, user must be creator of order or owner of product'], Response::HTTP_NON_AUTHORITATIVE_INFORMATION);
    }
}
