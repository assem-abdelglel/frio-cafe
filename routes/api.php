<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AuthController;

// تسجيل الدخول (بدون توكن)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// الراوتات المحمية بالتوكن
Route::middleware('auth:sanctum')->group(function () {

    // المستخدم الحالي
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // تسجيل الخروج
    Route::post('/logout', [AuthController::class, 'logout']);

    // الراوتات الخاصة بالأدمن فقط
    Route::middleware('role:admin')->group(function () {

        // الطلبات
        Route::apiResource('orders', OrderController::class);

        // عناصر الطلب
        Route::apiResource('order-items', OrderItemController::class);

        // دفعات
        Route::post('payments', [PaymentController::class, 'store']);

        // فواتير
        Route::get('invoices/{id}', [InvoiceController::class, 'show']);

        // المنتجات
        Route::apiResource('products', ProductController::class);
    });
});
