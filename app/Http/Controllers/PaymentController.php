<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::with('order')->get();
    }

    public function store(Request $request)
    {
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'amount' => 'required|numeric|min:0',
        'payment_method' => 'required|un:cash,card', // مثل cash أو card
    ]);

    $order = Order::findOrFail($request->order_id);

    // التأكد إن لسه مفيش دفع مرتبط بالطلب ده (اختياري حسب السيناريو)
    if ($order->payment) {
        return response()->json([
            'message' => 'Payment already exists for this order.',
        ], 400);
    }

    // إنشاء الدفع
    $payment = $order->payment()->create([
        'order_id'       => $request->order_id,
        'amount'         => $request->amount,
        'payment_method' => $request->payment_method,
    ]);

    // إنشاء الفاتورة تلقائيًا بعد الدفع
    $invoice = $payment->invoice()->create([
        'order_id' => $order->id,
        'payment_id' => $payment->id,
        'total' => $order->total,
    ]);

    return response()->json([
        'message' => 'Payment recorded successfully and invoice created',
        'payment' => $payment,
        'invoice' => $invoice,
    ], 201);

    }

    public function show($id)
    {
        return Payment::with('order')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'amount'         => 'nullable|numeric',
            'payment_method' => 'nullable|string',
            'paid_at'        => 'nullable|date',
        ]);

        Payment::update($request->only([
            'amount', 'payment_method', 'paid_at'
        ]));

        return $payment;
    }

    public function destroy($id)
    {
        Payment::findOrFail($id)->delete();
        return response()->json(['message' => 'Payment deleted']);
    }
}
