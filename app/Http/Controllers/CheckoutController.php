<?php

namespace App\Http\Controllers;

use App\DTOs\CardDTO;
use App\Models\Client;
use App\Models\Product;
use App\Models\Transaction;
use App\Enums\TransactionStatus;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(Request $request, PaymentService $paymentService)
    {
        $validated = $request->validate([
            'client.name'         => 'required|string',
            'client.email'        => 'required|email',
            'products'            => 'required|array|min:1',
            'products.*.id'       => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'card.number'         => 'required|string|size:16',
            'card.cvv'            => 'required|string|size:3',
        ]);

        $card = CardDTO::fromRequest($request->card);

        return DB::transaction(function () use ($card, $paymentService, $validated, $request) {
            $client = Client::firstOrCreate(
                ['email' => $validated['client']['email']],
                ['name' => $validated['client']['name']]
            );

            $totalAmount = 0;
            $productData = [];

            foreach ($validated['products'] as $item) {
                $product = Product::find($item['id']);
                $totalAmount += $product->amount * $item['quantity'];
                $productData[$product->id] = ['quantity' => $item['quantity']];
            }

            $transaction = Transaction::create([
                'client_id' => $client->id,
                'status' => TransactionStatus::PENDING,
                'amount' => $totalAmount,
                'card_last_numbers' => substr($validated['card']['number'], -4),
            ]);

            $transaction->products()->attach($productData);

            $success = $paymentService->charge($transaction, $card);

            if ($success) {
                return response()->json(['message' => 'Pagamento aprovado!'], 201);
            }
            return response()->json(['message' => 'Pagamento recusado em todos os operadores.'], 402);
        });
    }
}
