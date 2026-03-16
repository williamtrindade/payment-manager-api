<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        $clients = Client::has('transactions')
            ->withCount('transactions')
            ->orderBy('name')
            ->paginate(15);

        return response()->json($clients);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load(['transactions' => function ($query) {
            $query->latest()->with('gateway:id,name');
        }]);

        $totalPaid = $client->transactions()
            ->where('status', TransactionStatus::PAID)
            ->sum('amount');

        return response()->json([
            'client' => $client,
            'stats' => [
                'total_spent' => (int) $totalPaid,
                'transactions_count' => $client->transactions->count(),
            ]
        ]);
    }
}
