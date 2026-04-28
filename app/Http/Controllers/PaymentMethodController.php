<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return auth()->user()->paymentMethods()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:debito,credito,vales',
            'cut_day' => 'nullable|integer|min:1|max:31',
            'payment_day' => 'nullable|integer|min:1|max:31',
        ]);

        $card = auth()->user()->paymentMethods()->create($validated);
        return response()->json($card, 201);
    }

    public function destroy($id)
    {
        $card = auth()->user()->paymentMethods()->findOrFail($id);
        $card->delete();
        return response()->json(null, 204);
    }
}
