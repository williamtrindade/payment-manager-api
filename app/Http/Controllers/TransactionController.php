<?php
namespace App\Http\Controllers;

use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['client', 'products'])->latest()->get();
        return response()->json($transactions);
    }

    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load(['client', 'products', 'gateway']));
    }
}
