<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show($id)
    {
            $invoice = Invoice::with(['order', 'payment'])->findOrFail($id);

    return response()->json([
        'message' => 'Invoice details retrieved successfully',
        'invoice' => $invoice,
    ]);

    }
}