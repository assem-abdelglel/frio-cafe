<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //عرض كل االطلبات
    public function index()
    {
        return response()->json(Order::with('items')->latest()->get());
    }

    //انشاء طلب جديد
    
    public function store(Request $request)
    {
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'status' => 'required|string',
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        // إنشاء الطلب الأساسي
        $order = Order::create([
            'user_id' => $request->user_id,
            'status' => $request->status,
            'total' => 0, // مبدئيًا، هنحسبه بعدين
        ]);

        $total = 0;

        // إضافة العناصر المرتبطة بالطلب
        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'type' => $item['type'] ?? null, // لو فيه نوع
            ]);

            // جمع الإجمالي
            $total += $item['quantity'] * $item['price'];
        }

        // تحديث إجمالي الطلب
        $order->update([
            'total' => $total,
        ]);

        //انشاء الفاتوره
        Invoice::create([
            'order_id' => $order->id,
            'amount'   => $total,
            'status'   => 'unpaid',
        ]);

        // إنشاء الفاتورة المرتبطة بالطلب
        $order->invoice()->create([
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items', 'invoice'),
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }

}

    //عرض طلب معين

public function show($id): JsonResponse
{
    $order = Order::with([
        'items.product',   // علشان نجيب بيانات المنتج المرتبطة بكل item
        'payments',        // كل عمليات الدفع
        'invoice'          // الفاتورة
    ])->findOrFail($id);

    return response()->json([
        'order' => $order
    ]);
}
    

    //تحديث طلب
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'nullable|string',
            'total'  => 'nullable|numeric',
        ]);

        $order->update($request->only(['status', 'total']));
        return response()->json($order);
    }

    //حذف طلب
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted']);
    }
}
