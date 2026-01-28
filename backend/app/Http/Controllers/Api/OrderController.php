<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->orders()->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:armature,transport',
            'details' => 'required|array',
            'comment' => 'nullable|string',
        ]);

        $order = $request->user()->orders()->create([
            'type' => $validated['type'],
            'details' => $validated['details'],
            'comment' => $validated['comment'] ?? null,
            'status' => 'new',
        ]);

        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        return $order;
    }
}