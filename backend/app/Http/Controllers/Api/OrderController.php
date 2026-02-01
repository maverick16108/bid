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

    public function stats(Request $request)
    {
        $user = $request->user();

        // Stats for cards
        $total = $user->orders()->count();
        $processing = $user->orders()->whereIn('status', ['new', 'processing'])->count();
        $completed = $user->orders()->where('status', 'completed')->count();

        // Chart data (last 30 days)
        $chartData = [];
        $labels = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d.m');
            $chartData[] = $user->orders()
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();
        }

        return response()->json([
            'total' => $total,
            'processing' => $processing,
            'completed' => $completed,
            'chart' => [
                'labels' => $labels,
                'data' => $chartData
            ]
        ]);
    }
}