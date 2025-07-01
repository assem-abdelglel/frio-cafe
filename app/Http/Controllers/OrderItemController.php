<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    //عرض كل العناصر
    public function index()
    {
        return response()->json(OrderItem::with(['order', 'product'])->get());
    }

    //انشاء عنصر طلب
    public function store(Request $request)
    {
        $request->validate([
            'order_id'   => 'required|exists:orders,id',
            'product_id' => 'required|exists:product,id',
            'quantity'   => 'required|integer|min:1',
            'price'      => 'required|numeric|min:0',
            'type'       => 'nullable|string',
        ]);

        $item = OrderItem::create($request->all());

        return response()->json($item, 201);
    }

    //عرض عنصر
    public function show($id)
    {
        $item = OrderItem::with(['order', 'product'])->findOrFail($id);
        return response()->json($item);
    }

    //تحديث عنصر
    public function update(Request $request, $id)
    {
        $item = OrderItem::findOrFail($id);

        $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'price'    => 'nullable|numeric|min:0',
        ]);

        $item->update($request->only(['quantity', 'price']));

        return response()->json($item);
    }

    //حذف عنصر
    public function destroy($id)
    {
        $item = OrderItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Order item deleted']);
    }
}
