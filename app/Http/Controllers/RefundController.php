<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\PaymentService;

class RefundController
{
    public function refund(Transaction $transaction, PaymentService $paymentService)
    {
        if ($transaction->status !== TransactionStatus::PAID) {
            return response()->json(['message' => 'Transação não está em estado de estorno.'], 422);
        }

        $success = $paymentService->refund($transaction);

        if ($success) {
            $transaction->update(['status' => TransactionStatus::REFUNDED]);

            return response()->json(['message' => 'Estorno realizado com sucesso!']);
        }

        return response()->json(['message' => 'Falha ao processar estorno no provedor externo.'], 502);
    }
}
